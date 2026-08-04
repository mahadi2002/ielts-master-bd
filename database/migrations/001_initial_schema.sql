-- IELTS Master BD — Schema
-- MySQL 8 / MariaDB 10.4+, utf8mb4_unicode_ci throughout

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS & AUTH
-- ============================================================

CREATE TABLE users (
  id                 CHAR(36) PRIMARY KEY,
  mobile_number      VARCHAR(11) NOT NULL UNIQUE,   -- 01XXXXXXXXX
  operator           ENUM('robi','airtel') NOT NULL,
  name               VARCHAR(100) NULL,
  target_band        DECIMAL(2,1) NULL,             -- e.g. 7.0
  daily_goal_type    ENUM('words','quiz_questions') NOT NULL DEFAULT 'words',
  daily_goal_count   SMALLINT UNSIGNED NOT NULL DEFAULT 5,
  role               ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_active_at     DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE otp_requests (
  id                 CHAR(36) PRIMARY KEY,
  mobile_number      VARCHAR(11) NOT NULL,
  otp_hash           VARCHAR(255) NOT NULL,
  attempts           TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at         DATETIME NOT NULL,
  verified_at        DATETIME NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_mobile_expiry (mobile_number, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id                 CHAR(64) PRIMARY KEY,           -- session token
  user_id            CHAR(36) NOT NULL,
  ip_address         VARCHAR(45) NULL,
  user_agent         VARCHAR(255) NULL,
  payload            MEDIUMTEXT NULL,
  last_activity      DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. SUBSCRIPTION (BDApps)
-- ============================================================

CREATE TABLE subscriptions (
  id                 CHAR(36) PRIMARY KEY,
  user_id            CHAR(36) NOT NULL,
  gateway            ENUM('mock','bdapps') NOT NULL DEFAULT 'mock',
  external_ref       VARCHAR(100) NULL,              -- BDApps subscription ID
  status             ENUM('active','expired','cancelled','failed') NOT NULL DEFAULT 'active',
  daily_amount       DECIMAL(6,2) NOT NULL DEFAULT 2.78,
  started_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_charged_at    DATETIME NULL,
  next_charge_at     DATETIME NULL,
  cancelled_at       DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscription_events (
  id                 CHAR(36) PRIMARY KEY,
  subscription_id    CHAR(36) NOT NULL,
  event_type         ENUM('charge_success','charge_failed','cancelled','renewed') NOT NULL,
  raw_payload        JSON NULL,                       -- gateway callback, for audit
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. WORDS & CONTENT
-- ============================================================

CREATE TABLE words (
  id                 CHAR(36) PRIMARY KEY,
  headword            VARCHAR(100) NOT NULL,
  ipa                 VARCHAR(100) NULL,
  part_of_speech      VARCHAR(30) NULL,
  definition_en        TEXT NOT NULL,
  definition_bn        TEXT NULL,
  example_sentence     TEXT NULL,
  synonyms             JSON NULL,                     -- array of strings
  ielts_band_level     TINYINT UNSIGNED NOT NULL,      -- 6,7,8,9
  task_tag             ENUM('task1','task2','speaking','general') NULL,
  audio_url            VARCHAR(255) NULL,
  is_exclusive         BOOLEAN NOT NULL DEFAULT FALSE, -- reward-only pool
  frequency_rank       INT UNSIGNED NULL,
  created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FULLTEXT INDEX ft_headword_def (headword, definition_en),
  INDEX idx_band_exclusive (ielts_band_level, is_exclusive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. LEARNING PROGRESS (SM-2 spaced repetition)
-- ============================================================

CREATE TABLE user_word_progress (
  id                 CHAR(36) PRIMARY KEY,
  user_id            CHAR(36) NOT NULL,
  word_id            CHAR(36) NOT NULL,
  status             ENUM('new','learning','review','mastered') NOT NULL DEFAULT 'new',
  ease_factor        DECIMAL(4,2) NOT NULL DEFAULT 2.50,
  interval_days      INT UNSIGNED NOT NULL DEFAULT 1,
  repetitions        INT UNSIGNED NOT NULL DEFAULT 0,
  next_review_date   DATE NOT NULL,
  last_reviewed_at   DATETIME NULL,
  UNIQUE KEY uq_user_word (user_id, word_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE,
  INDEX idx_review_queue (user_id, next_review_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE daily_progress (
  id                 CHAR(36) PRIMARY KEY,
  user_id            CHAR(36) NOT NULL,
  progress_date      DATE NOT NULL,
  goal_target        SMALLINT UNSIGNED NOT NULL,
  goal_achieved      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  goal_completed     BOOLEAN NOT NULL DEFAULT FALSE,
  exclusive_word_id  CHAR(36) NULL,
  UNIQUE KEY uq_user_date (user_id, progress_date),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (exclusive_word_id) REFERENCES words(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE streaks (
  user_id             CHAR(36) PRIMARY KEY,
  current_streak      INT UNSIGNED NOT NULL DEFAULT 0,
  longest_streak      INT UNSIGNED NOT NULL DEFAULT 0,
  freezes_available   TINYINT UNSIGNED NOT NULL DEFAULT 1,
  freezes_reset_at    DATE NULL,                       -- weekly refill
  last_completed_date DATE NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_collection (
  user_id            CHAR(36) NOT NULL,
  word_id            CHAR(36) NOT NULL,
  unlocked_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, word_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. QUIZZES
-- ============================================================

CREATE TABLE quizzes (
  id                 CHAR(36) PRIMARY KEY,
  word_id            CHAR(36) NOT NULL,
  quiz_type          ENUM('mcq','fill_blank','synonym_match') NOT NULL,
  question           TEXT NOT NULL,
  options             JSON NULL,                       -- for mcq/synonym_match
  correct_answer      VARCHAR(255) NOT NULL,
  FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quiz_attempts (
  id                 CHAR(36) PRIMARY KEY,
  user_id            CHAR(36) NOT NULL,
  quiz_id            CHAR(36) NOT NULL,
  is_correct         BOOLEAN NOT NULL,
  attempted_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  INDEX idx_user_date (user_id, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. INFRASTRUCTURE (no Redis assumption)
-- ============================================================

CREATE TABLE rate_limits (
  bucket_key         VARCHAR(150) PRIMARY KEY,          -- e.g. 'otp:01712345678' or 'dict:ip:1.2.3.4'
  hits                INT UNSIGNED NOT NULL DEFAULT 0,
  window_started_at  DATETIME NOT NULL,
  INDEX idx_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
  id                 CHAR(36) PRIMARY KEY,
  job_type           VARCHAR(50) NOT NULL,              -- 'daily_reset', 'subscription_charge_check'
  payload            JSON NULL,
  status             ENUM('pending','running','done','failed') NOT NULL DEFAULT 'pending',
  run_at             DATETIME NOT NULL,
  attempts           TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status_run (status, run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_alerts (
  id                 CHAR(36) PRIMARY KEY,
  alert_type         VARCHAR(50) NOT NULL,               -- 'exclusive_pool_low'
  context             JSON NULL,
  resolved            BOOLEAN NOT NULL DEFAULT FALSE,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
