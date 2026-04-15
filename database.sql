-- ============================================================
--  Simule Ton Futur  |  Base de données
--  ESPRIT - UP Web - AU 2025/2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS simule_ton_futur
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE simule_ton_futur;

-- ── Table utilisateur ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateur (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(50),
    prenom        VARCHAR(50),
    email         VARCHAR(100) UNIQUE,
    motDePasse    VARCHAR(255),
    role          ENUM('admin','user') DEFAULT 'user'
);

-- ── Table profil ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS profil (
    idProfil      INT AUTO_INCREMENT PRIMARY KEY,
    bio           TEXT,
    photoProfil   VARCHAR(255),
    ville         VARCHAR(50),
    pays          VARCHAR(50),
    langue        VARCHAR(50),
    idUtilisateur INT UNIQUE,
    FOREIGN KEY (idUtilisateur)
        REFERENCES utilisateur(idUtilisateur)
        ON DELETE CASCADE
);

-- ── Données de test ──────────────────────────────────────────
INSERT INTO utilisateur (nom, prenom, email, motDePasse, role) VALUES
('Ben Salah',  'Yassine', 'yassine@esprit.tn', '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'admin'),
('Trabelsi',   'Fatima',  'fatima@gmail.com',  '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
('Hamdi',      'Karim',   'karim@yahoo.fr',    '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
('Gharbi',     'Sonia',   'sonia@outlook.com', '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user');

INSERT INTO profil (bio, photoProfil, ville, pays, langue, idUtilisateur) VALUES
('Administrateur passionné de tech.', 'default.png', 'Tunis',   'Tunisie', 'Français', 1),
('Étudiante en développement web.',   'default.png', 'Sfax',    'Tunisie', 'Français', 2),
('Freelance développeur PHP.',        'default.png', 'Sousse',  'Tunisie', 'Arabe',    3),
('Créatrice de contenu digital.',     'default.png', 'Bizerte', 'Tunisie', 'Anglais',  4);
