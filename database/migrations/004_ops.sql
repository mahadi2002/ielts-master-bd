-- 004_ops.sql — sessions, security, queue, webhooks, admin alerts

-- One session shape for everyone — admin is a users.role value, not a
-- separate identity, so there is no admin_id column here.
CREATE TABLE IF NOT EXISTS sessions (
  id            CHAR(64)        NOT NULL,
  user_id       BIGINT UNSIGNED DEFAULT NULL,
  ip_hash       CHAR(64)        DEFAULT NULL,
  ua_hash       CHAR(64)        DEFAULT NULL,
  payload       MEDIUMTEXT      NOT NULL,
  last_activity INT UNSIGNED    NOT NULL,
  created_at    INT UNSIGNED    NOT NULL,
  PRIMARY KEY (id),
  KEY idx_sess_user (user_id),
  KEY idx_sess_gc (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  bucket        VARCHAR(150) NOT NULL COMMENT 'e.g. otp_request:0:ip:<hash>',
  hits          INT UNSIGNED NOT NULL DEFAULT 0,
  window_start  DATETIME     NOT NULL,
  blocked_until DATETIME     DEFAULT NULL,
  PRIMARY KEY (bucket),
  KEY idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_type ENUM('user','admin','system','gateway') NOT NULL DEFAULT 'system',
  actor_id   BIGINT UNSIGNED DEFAULT NULL,
  action     VARCHAR(60)     NOT NULL COMMENT 'otp.sent, sub.activated, charge.failed, admin.word.updated',
  entity     VARCHAR(40)     DEFAULT NULL,
  entity_id  BIGINT UNSIGNED DEFAULT NULL,
  ip_hash    CHAR(64)        DEFAULT NULL,
  ua_hash    CHAR(64)        DEFAULT NULL,
  meta       JSON            DEFAULT NULL COMMENT 'NEVER store full msisdn or otp here',
  created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_actor (actor_type, actor_id, created_at),
  KEY idx_audit_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_events (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id     VARCHAR(160)    NOT NULL COMMENT 'idempotency key from provider',
  type         VARCHAR(60)     DEFAULT NULL,
  payload      JSON            NOT NULL,
  signature_ok TINYINT(1)      NOT NULL DEFAULT 0,
  processed_at DATETIME        DEFAULT NULL,
  error        VARCHAR(255)    DEFAULT NULL,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wh_event (event_id),
  KEY idx_wh_unprocessed (processed_at, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  queue        VARCHAR(40)     NOT NULL DEFAULT 'default',
  job          VARCHAR(80)     NOT NULL,
  payload      JSON            NOT NULL,
  attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 3,
  available_at DATETIME        NOT NULL,
  reserved_at  DATETIME        DEFAULT NULL,
  failed_at    DATETIME        DEFAULT NULL,
  last_error   VARCHAR(500)    DEFAULT NULL,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_jobs_poll (queue, reserved_at, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guards ExclusiveWordService's reward pool from silently running dry per band.
CREATE TABLE IF NOT EXISTS admin_alerts (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  alert_type VARCHAR(50)     NOT NULL COMMENT 'exclusive_pool_low, exclusive_pool_empty',
  context    JSON            DEFAULT NULL,
  resolved   TINYINT(1)      NOT NULL DEFAULT 0,
  created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_alerts_unresolved (resolved, alert_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
