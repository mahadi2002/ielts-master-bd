-- 005_support.sql — contact/support inbox

CREATE TABLE IF NOT EXISTS contact_messages (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(80)     NOT NULL,
  contact       VARCHAR(120)    NOT NULL COMMENT 'phone or email, as typed — not the account msisdn',
  message       TEXT            NOT NULL,
  status        ENUM('new','read','resolved') NOT NULL DEFAULT 'new',
  ip_hash       CHAR(64)        DEFAULT NULL,
  resolved_by   INT UNSIGNED    DEFAULT NULL COMMENT 'admins.id',
  resolved_at   DATETIME        DEFAULT NULL,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
