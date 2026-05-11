-- ============================================================
-- Unified schema: Simule Ton Futur + QuizForge
-- Database: simule_ton_futur
-- Run this once in phpMyAdmin before starting the app
-- ============================================================

CREATE DATABASE IF NOT EXISTS simule_ton_futur
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE simule_ton_futur;

-- ── Users ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateur (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(50),
    prenom        VARCHAR(50),
    email         VARCHAR(100) UNIQUE,
    motDePasse    VARCHAR(255),
    role          ENUM('admin','user') DEFAULT 'user',
    statut        ENUM('actif','bloque','en_attente') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS profil (
    idProfil      INT AUTO_INCREMENT PRIMARY KEY,
    bio           TEXT,
    photoProfil   VARCHAR(255),
    ville         VARCHAR(50),
    pays          VARCHAR(50),
    langue        VARCHAR(50),
    idUtilisateur INT UNIQUE,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_reset (
    idReset       INT AUTO_INCREMENT PRIMARY KEY,
    idUtilisateur INT NOT NULL,
    tokenHash     VARCHAR(255) NOT NULL,
    expireAt      DATETIME NOT NULL,
    createdAt     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS qr_login (
    idQrLogin     INT AUTO_INCREMENT PRIMARY KEY,
    idUtilisateur INT NOT NULL,
    tokenHash     VARCHAR(255) NOT NULL,
    status        ENUM('pending','approved','used','expired') NOT NULL DEFAULT 'pending',
    expireAt      DATETIME NOT NULL,
    createdAt     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS face_descriptors (
    idFaceDescriptor INT AUTO_INCREMENT PRIMARY KEY,
    idUtilisateur    INT NOT NULL,
    descriptorJson   LONGTEXT NOT NULL,
    createdAt        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_face_user (idUtilisateur),
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Quizzes ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS quiz (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type        VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id       INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    points        INT NOT NULL DEFAULT 1,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS answers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id   INT UNSIGNED NOT NULL,
    answer_text   TEXT NOT NULL,
    is_correct    TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feedback (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quizId   INT UNSIGNED NOT NULL,
    userId   INT UNSIGNED NOT NULL,
    feedback TEXT NOT NULL,
    FOREIGN KEY (quizId) REFERENCES quiz(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Quiz Results (linked to utilisateur) ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS quiz_result (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id    INT UNSIGNED NOT NULL,
    user_id    INT          NOT NULL,
    score      INT          NOT NULL DEFAULT 0,
    total      INT          NOT NULL DEFAULT 0,
    passed_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_result_quiz
        FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE,
    CONSTRAINT fk_result_user
        FOREIGN KEY (user_id) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
