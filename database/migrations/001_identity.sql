-- 001_identity.sql — users, subscriptions, billing, OTP, admins

CREATE TABLE IF NOT EXISTS users (
  id                 BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  msisdn_hash        CHAR(64)         NOT NULL COMMENT 'hash_hmac sha256 blind index — lookup key',
  msisdn_enc         VARBINARY(255)   NOT NULL COMMENT 'base64(iv|tag|ciphertext), AES-256-GCM',
  msisdn_last4       CHAR(4)          NOT NULL COMMENT 'UI display only',
  operator           ENUM('robi','airtel') NOT NULL,
  display_name       VARCHAR(80)      DEFAULT NULL,
  target_band        DECIMAL(2,1)     DEFAULT NULL COMMENT 'e.g. 7.0',
  daily_goal_type    ENUM('words','quiz_questions') NOT NULL DEFAULT 'words',
  daily_goal_count   SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  status             ENUM('pending','active','blocked') NOT NULL DEFAULT 'pending',
  locale             CHAR(2)          NOT NULL DEFAULT 'bn',
  created_at         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at      DATETIME         DEFAULT NULL,
  anonymized_at      DATETIME         DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_msisdn (msisdn_hash),
  KEY idx_users_status (status),
  KEY idx_users_operator (operator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id            BIGINT UNSIGNED NOT NULL,
  bdapps_sub_ref     VARCHAR(128)    DEFAULT NULL COMMENT 'subscriber reference from SDP',
  status             ENUM('pending','active','grace','expired','unsubscribed') NOT NULL DEFAULT 'pending',
  daily_amount       DECIMAL(6,2)    NOT NULL DEFAULT 2.78,
  started_at         DATETIME        DEFAULT NULL,
  current_period_end DATETIME        DEFAULT NULL COMMENT 'access allowed while NOW() < this',
  grace_until        DATETIME        DEFAULT NULL,
  cancelled_at       DATETIME        DEFAULT NULL,
  cancel_source      ENUM('user','sms_stop','operator','admin','system') DEFAULT NULL,
  cancel_reason      VARCHAR(160)    DEFAULT NULL,
  created_at         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sub_ref (bdapps_sub_ref),
  KEY idx_sub_user (user_id),
  KEY idx_sub_cycle (status, current_period_end),
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS charge_transactions (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  subscription_id BIGINT UNSIGNED NOT NULL,
  idempotency_key VARCHAR(80)     NOT NULL COMMENT 'sha1(sub_id:YYYY-MM-DD)',
  external_txn_id VARCHAR(128)    DEFAULT NULL,
  amount          DECIMAL(6,2)    NOT NULL,
  status          ENUM('pending','success','failed','reversed') NOT NULL DEFAULT 'pending',
  status_code     VARCHAR(48)     DEFAULT NULL,
  failure_reason  VARCHAR(160)    DEFAULT NULL,
  raw_response    JSON            DEFAULT NULL,
  attempted_at    DATETIME        NOT NULL,
  settled_at      DATETIME        DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_charge_idem (idempotency_key),
  KEY idx_charge_sub (subscription_id, attempted_at),
  KEY idx_charge_status (status, attempted_at),
  CONSTRAINT fk_charge_sub FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS otp_requests (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  msisdn_hash  CHAR(64)        NOT NULL,
  otp_hash     VARCHAR(255)    NOT NULL COMMENT 'password_hash() — never plaintext',
  reference_id VARCHAR(128)    DEFAULT NULL COMMENT 'gateway reference',
  purpose      ENUM('subscribe','login') NOT NULL DEFAULT 'subscribe',
  attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 3,
  ip_hash      CHAR(64)        NOT NULL,
  ua_hash      CHAR(64)        DEFAULT NULL,
  consumed_at  DATETIME        DEFAULT NULL,
  expires_at   DATETIME        NOT NULL,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_otp_lookup (msisdn_hash, consumed_at, expires_at),
  KEY idx_otp_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  email         VARCHAR(160)  NOT NULL,
  password_hash VARCHAR(255)  NOT NULL COMMENT 'PASSWORD_ARGON2ID',
  name          VARCHAR(80)   NOT NULL,
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  totp_secret   VARBINARY(255) DEFAULT NULL,
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  last_login_at DATETIME      DEFAULT NULL,
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
