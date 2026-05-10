-- QuizForge Database Schema
-- Run this once to create the required tables.

CREATE DATABASE IF NOT EXISTS quizzes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quizzes;

CREATE TABLE IF NOT EXISTS quiz (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type        VARCHAR(100) NOT NULL,
    description TEXT         NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS questions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id       INT UNSIGNED NOT NULL,
    question_text TEXT         NOT NULL,
    points        INT          NOT NULL DEFAULT 1,
    CONSTRAINT fk_questions_quiz
        FOREIGN KEY (quiz_id) REFERENCES quiz(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS answers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id   INT UNSIGNED NOT NULL,
    answer_text   TEXT         NOT NULL,
    is_correct    TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT fk_answers_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quizId   INT UNSIGNED NOT NULL,
    userId   INT UNSIGNED NOT NULL,
    feedback TEXT         NOT NULL,
    CONSTRAINT fk_feedback_quiz
        FOREIGN KEY (quizId) REFERENCES quiz(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
