-- 003_user_data.sql — learning progress, SRS state, streaks, UGC (Q&A)

CREATE TABLE IF NOT EXISTS user_word_progress (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id            BIGINT UNSIGNED NOT NULL,
  word_id            BIGINT UNSIGNED NOT NULL,
  status             ENUM('new','learning','review','mastered') NOT NULL DEFAULT 'new',
  ease_factor        DECIMAL(4,2)    NOT NULL DEFAULT 2.50,
  interval_days      INT UNSIGNED    NOT NULL DEFAULT 1,
  repetitions        INT UNSIGNED    NOT NULL DEFAULT 0,
  next_review_date   DATE            NOT NULL,
  last_reviewed_at   DATETIME        DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_progress_user_word (user_id, word_id),
  KEY idx_review_queue (user_id, next_review_date),
  CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_progress_word FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_progress (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id            BIGINT UNSIGNED NOT NULL,
  progress_date      DATE            NOT NULL,
  goal_target        SMALLINT UNSIGNED NOT NULL,
  goal_achieved      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  goal_completed     TINYINT(1)      NOT NULL DEFAULT 0,
  exclusive_word_id  BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_progress_user_date (user_id, progress_date),
  CONSTRAINT fk_daily_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_daily_word FOREIGN KEY (exclusive_word_id) REFERENCES words(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS streaks (
  user_id             BIGINT UNSIGNED NOT NULL,
  current_streak      INT UNSIGNED    NOT NULL DEFAULT 0,
  longest_streak      INT UNSIGNED    NOT NULL DEFAULT 0,
  freezes_available   TINYINT UNSIGNED NOT NULL DEFAULT 1,
  freezes_reset_at    DATE            DEFAULT NULL COMMENT 'weekly refill',
  last_completed_date DATE            DEFAULT NULL,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_streak_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_collection (
  user_id     BIGINT UNSIGNED NOT NULL,
  word_id     BIGINT UNSIGNED NOT NULL,
  unlocked_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, word_id),
  CONSTRAINT fk_collection_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_collection_word FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempts (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      BIGINT UNSIGNED NOT NULL,
  quiz_id      BIGINT UNSIGNED NOT NULL,
  is_correct   TINYINT(1)      NOT NULL,
  attempted_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_attempts_user_date (user_id, attempted_at),
  CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_attempt_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ask-a-question UGC loop. Answers come from a users.role = 'admin' account,
-- not other users — this is moderated expert Q&A, not an open forum. Answers
-- are attributed to "the team" in the UI, not a specific phone number.
CREATE TABLE IF NOT EXISTS qa_questions (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     BIGINT UNSIGNED NOT NULL,
  word_id     BIGINT UNSIGNED DEFAULT NULL COMMENT 'optional — the word this question is about',
  title       VARCHAR(200)    NOT NULL,
  body        TEXT            NOT NULL,
  status      ENUM('open','answered') NOT NULL DEFAULT 'open',
  answer      TEXT            DEFAULT NULL,
  answered_by BIGINT UNSIGNED DEFAULT NULL COMMENT 'users.id of the admin who answered',
  answered_at DATETIME        DEFAULT NULL,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_qa_status (status, created_at),
  KEY idx_qa_user (user_id),
  CONSTRAINT fk_qa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_qa_word FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE SET NULL,
  CONSTRAINT fk_qa_admin FOREIGN KEY (answered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
