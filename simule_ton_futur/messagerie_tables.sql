-- =============================================
-- Tables messagerie pour la base simule_ton_futur
-- A exécuter APRES la création de la table utilisateur
-- =============================================

USE simule_ton_futur;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------
-- Table : conversations
-- -------------------------------------------
DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur1_id` int(11) NOT NULL,
  `utilisateur2_id` int(11) NOT NULL,
  `sujet` varchar(100) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_conv_u1` (`utilisateur1_id`),
  KEY `fk_conv_u2` (`utilisateur2_id`),
  CONSTRAINT `fk_conv_u1` FOREIGN KEY (`utilisateur1_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_conv_u2` FOREIGN KEY (`utilisateur2_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : messages
-- -------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_envoi` datetime NOT NULL DEFAULT current_timestamp(),
  `est_lu` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_msg_exp` (`expediteur_id`),
  KEY `fk_msg_dest` (`destinataire_id`),
  CONSTRAINT `fk_msg_dest` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_msg_exp` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : blocages
-- -------------------------------------------
DROP TABLE IF EXISTS `blocages`;
CREATE TABLE `blocages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bloqueur_id` int(11) NOT NULL,
  `bloque_id` int(11) NOT NULL,
  `date_blocage` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_blocage` (`bloqueur_id`, `bloque_id`),
  CONSTRAINT `fk_bloc_bloqueur` FOREIGN KEY (`bloqueur_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE,
  CONSTRAINT `fk_bloc_bloque` FOREIGN KEY (`bloque_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : reactions
-- -------------------------------------------
DROP TABLE IF EXISTS `reactions`;
CREATE TABLE `reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `emoji` varchar(10) NOT NULL,
  `date_reaction` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reaction` (`message_id`, `utilisateur_id`),
  CONSTRAINT `fk_react_msg` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_react_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`idUtilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Ajouter colonne telephone à utilisateur (si pas déjà présente)
-- -------------------------------------------
-- ALTER TABLE utilisateur ADD COLUMN telephone VARCHAR(20) DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;
