-- 002_content.sql — words, quizzes, guides (the catalog + reference library)

CREATE TABLE IF NOT EXISTS words (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug              VARCHAR(140)    NOT NULL,
  headword          VARCHAR(100)    NOT NULL,
  ipa               VARCHAR(100)    DEFAULT NULL,
  part_of_speech    VARCHAR(30)     DEFAULT NULL,
  definition_en     TEXT            NOT NULL,
  definition_bn     TEXT            DEFAULT NULL,
  example_sentence  TEXT            DEFAULT NULL,
  synonyms          JSON            DEFAULT NULL COMMENT 'array of strings',
  ielts_band_level  TINYINT UNSIGNED NOT NULL COMMENT '6,7,8,9',
  task_tag          ENUM('task1','task2','speaking','general') DEFAULT NULL,
  audio_url         VARCHAR(255)    DEFAULT NULL,
  is_exclusive      BOOLEAN         NOT NULL DEFAULT FALSE COMMENT 'reward-only pool',
  frequency_rank    INT UNSIGNED    DEFAULT NULL,
  view_count        INT UNSIGNED    NOT NULL DEFAULT 0,
  created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_words_slug (slug),
  FULLTEXT INDEX ft_headword_def (headword, definition_en),
  KEY idx_band_exclusive (ielts_band_level, is_exclusive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quizzes (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  word_id        BIGINT UNSIGNED NOT NULL,
  quiz_type      ENUM('mcq','fill_blank','synonym_match') NOT NULL,
  question       TEXT            NOT NULL,
  options        JSON            DEFAULT NULL COMMENT 'for mcq/synonym_match',
  correct_answer VARCHAR(255)    NOT NULL,
  PRIMARY KEY (id),
  KEY idx_quiz_word (word_id),
  CONSTRAINT fk_quiz_word FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guides (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug            VARCHAR(140)    NOT NULL,
  title           VARCHAR(200)    NOT NULL,
  category        ENUM('writing_task1','writing_task2','speaking','listening','reading','vocabulary') NOT NULL,
  excerpt         VARCHAR(300)    NOT NULL COMMENT 'free-tier teaser, shown to everyone',
  body_md         MEDIUMTEXT      NOT NULL COMMENT 'gated — full body, markdown',
  band_relevance  VARCHAR(20)     DEFAULT NULL COMMENT 'e.g. "6-9", "7+"',
  is_published    TINYINT(1)      NOT NULL DEFAULT 1,
  view_count      INT UNSIGNED    NOT NULL DEFAULT 0,
  published_at    DATETIME        DEFAULT NULL,
  created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_guides_slug (slug),
  KEY idx_guides_category (category, is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
