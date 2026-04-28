-- phpMyAdmin SQL Dump
-- Simule Ton Futur | ESPRIT UP Web 2025/2026
-- Base de données complète — 4 tables (utilisateur, profil, offre, candidature)
-- Importez CE fichier uniquement dans phpMyAdmin (remplace database.sql + offres_candidatures.sql)
--
-- Host: 127.0.0.1
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
--  Base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS `simule_ton_futur`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `simule_ton_futur`;

-- ============================================================
--  Suppression des tables existantes (ordre FK-safe)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `candidature`;
DROP TABLE IF EXISTS `profil`;
DROP TABLE IF EXISTS `offre`;
DROP TABLE IF EXISTS `utilisateur`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Table `utilisateur`
-- ============================================================

CREATE TABLE `utilisateur` (
  `idUtilisateur` int(11) NOT NULL,
  `nom`           varchar(50)  DEFAULT NULL,
  `prenom`        varchar(50)  DEFAULT NULL,
  `email`         varchar(100) DEFAULT NULL,
  `motDePasse`    varchar(255) DEFAULT NULL,
  `role`          enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utilisateur` (`idUtilisateur`, `nom`, `prenom`, `email`, `motDePasse`, `role`) VALUES
(1, 'Ben Salah', 'Yassine', 'yassine@esprit.tn',    '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'admin'),
(2, 'Trabelsi',  'Fatima',  'fatima@gmail.com',      '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
(3, 'Hamdi',     'Karim',   'karim@yahoo.fr',        '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
(4, 'Gharbi',    'Sonia',   'sonia@outlook.com',     '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
(5, 'Chokri',    'Iyed',    'iyedchokri40@gmail.com','$2y$10$on5Tq1idCBguzXuoAx3tV.cneD7mwI4joCS..IymkYNVAgkIXXaAS', 'user');

-- ============================================================
--  Table `profil`
-- ============================================================

CREATE TABLE `profil` (
  `idProfil`      int(11)      NOT NULL,
  `bio`           text         DEFAULT NULL,
  `photoProfil`   varchar(255) DEFAULT NULL,
  `ville`         varchar(50)  DEFAULT NULL,
  `pays`          varchar(50)  DEFAULT NULL,
  `langue`        varchar(50)  DEFAULT NULL,
  `idUtilisateur` int(11)      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `profil` (`idProfil`, `bio`, `photoProfil`, `ville`, `pays`, `langue`, `idUtilisateur`) VALUES
(1, 'Administrateur passionné de tech.', 'default.png', 'Tunis',   'Tunisie', 'Français', 1),
(2, 'Étudiante en développement web.',   'default.png', 'Sfax',    'Tunisie', 'Français', 2),
(3, 'Freelance développeur PHP.',        'default.png', 'Sousse',  'Tunisie', 'Arabe',    3),
(4, 'Créatrice de contenu digital.',     'default.png', 'Bizerte', 'Tunisie', 'Anglais',  4),
(5, '',                                  'default.png', 'Gafsa',   'Tunisie', 'Autre',    5);

-- ============================================================
--  Table `offre`
-- ============================================================

CREATE TABLE `offre` (
  `idOffre`      int(11)      NOT NULL,
  `titre`        varchar(100) NOT NULL,
  `competences`  text         NOT NULL,
  `localisation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `offre` (`idOffre`, `titre`, `competences`, `localisation`) VALUES
(1, 'Développeur PHP Full Stack',  'PHP,MySQL,HTML,CSS,JavaScript',       'Tunis'),
(2, 'Designer UI/UX',              'Figma,Photoshop,CSS,HTML',            'Sfax'),
(3, 'Data Analyst',                'Python,SQL,Excel,Power BI',           'Tunis'),
(4, 'Développeur Mobile',          'Flutter,Dart,Firebase,REST API',      'Sousse'),
(5, 'Chef de Projet Digital',      'Agile,Scrum,Jira,Communication',      'Tunis'),
(6, 'Développeur Frontend React',  'React,JavaScript,CSS,HTML,Git',       'Bizerte'),
(7, 'Administrateur Système',      'Linux,Docker,Bash,Réseau',            'Tunis'),
(8, 'Community Manager',           'Réseaux sociaux,Canva,Rédaction,SEO', 'Sfax');

-- ============================================================
--  Table `candidature`
-- ============================================================

CREATE TABLE `candidature` (
  `idCandidature` int(11)      NOT NULL,
  `idUtilisateur` int(11)      NOT NULL,
  `skills`        text         NOT NULL,
  `cv`            varchar(255) DEFAULT NULL,
  `localisation`  varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `candidature` (`idCandidature`, `idUtilisateur`, `skills`, `cv`, `localisation`) VALUES
(1, 2, 'PHP,HTML,CSS,JavaScript',     'cv_fatima.pdf', 'Sfax'),
(2, 3, 'PHP,MySQL,Laravel,Git',       'cv_karim.pdf',  'Sousse'),
(3, 4, 'Figma,Canva,Réseaux sociaux', 'cv_sonia.pdf',  'Bizerte');

-- ============================================================
--  Index & clés primaires
-- ============================================================

ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`idUtilisateur`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `profil`
  ADD PRIMARY KEY (`idProfil`),
  ADD UNIQUE KEY `idUtilisateur` (`idUtilisateur`);

ALTER TABLE `offre`
  ADD PRIMARY KEY (`idOffre`);

ALTER TABLE `candidature`
  ADD PRIMARY KEY (`idCandidature`),
  ADD KEY `idx_candidature_utilisateur` (`idUtilisateur`);

-- ============================================================
--  AUTO_INCREMENT
-- ============================================================

ALTER TABLE `utilisateur`
  MODIFY `idUtilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `profil`
  MODIFY `idProfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `offre`
  MODIFY `idOffre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `candidature`
  MODIFY `idCandidature` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- ============================================================
--  Contraintes de clés étrangères
-- ============================================================

ALTER TABLE `profil`
  ADD CONSTRAINT `profil_ibfk_1`
    FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateur` (`idUtilisateur`)
    ON DELETE CASCADE;

ALTER TABLE `candidature`
  ADD CONSTRAINT `candidature_ibfk_1`
    FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateur` (`idUtilisateur`)
    ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
