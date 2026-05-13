
CREATE DATABASE IF NOT EXISTS simule_ton_futur
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE simule_ton_futur;


CREATE TABLE IF NOT EXISTS utilisateur (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(50),
    prenom        VARCHAR(50),
    email         VARCHAR(100) UNIQUE,
    motDePasse    VARCHAR(255),
    role          ENUM('admin','user') DEFAULT 'user',
    statut        ENUM('actif','bloque','en_attente') DEFAULT 'actif'
);


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

