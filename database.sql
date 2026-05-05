-- ============================================================
--  Simule Ton Futur  |  Base de données complète
--  ESPRIT - UP Web - AU 2025/2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS simule_ton_futur
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE simule_ton_futur;

-- ── Table utilisateur ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateur (
    idUtilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(50),
    prenom        VARCHAR(50),
    email         VARCHAR(100) UNIQUE,
    motDePasse    VARCHAR(255),
    role          ENUM('admin','user') DEFAULT 'user'
);

-- ── Table profil ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS profil (
    idProfil      INT AUTO_INCREMENT PRIMARY KEY,
    bio           TEXT,
    photoProfil   VARCHAR(255),
    ville         VARCHAR(50),
    pays          VARCHAR(50),
    langue        VARCHAR(50),
    idUtilisateur INT UNIQUE,
    FOREIGN KEY (idUtilisateur) REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE
);

-- ── Table cv ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cv (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    email          VARCHAR(100),
    telephone      VARCHAR(20),
    adresse        VARCHAR(150),
    titre_poste    VARCHAR(100),
    description    TEXT,
    github         VARCHAR(255),
    linkedin       VARCHAR(255),
    site_web       VARCHAR(255),
    date_naissance DATE,
    photo          VARCHAR(255)
);

-- ── Table competences ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS competences (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    cv_id          INT,
    nom_competence VARCHAR(100),
    niveau         ENUM('Débutant','Intermédiaire','Avancé','Expert') DEFAULT 'Intermédiaire',
    categorie      VARCHAR(50),
    FOREIGN KEY (cv_id) REFERENCES cv(id) ON DELETE CASCADE
);

-- ── Table experiences ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS experiences (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    cv_id        INT,
    titre        VARCHAR(150) NOT NULL,
    entreprise   VARCHAR(100),
    lieu         VARCHAR(100),
    date_debut   DATE         NOT NULL,
    date_fin     DATE,
    en_cours     TINYINT(1)   DEFAULT 0,
    description  TEXT,
    type_contrat ENUM('CDI','CDD','Stage','Freelance','Alternance','Bénévolat','Autre') DEFAULT 'Autre',
    FOREIGN KEY (cv_id) REFERENCES cv(id) ON DELETE CASCADE
);

-- ── Table metiers (Métier Avancé #1 - Recommandation) ─────────
CREATE TABLE IF NOT EXISTS metiers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(100) NOT NULL,
    description TEXT,
    secteur     VARCHAR(80),
    salaire_min INT DEFAULT 0,
    salaire_max INT DEFAULT 0
);

-- ── Table metier_competences (compétences requises par métier) ─
CREATE TABLE IF NOT EXISTS metier_competences (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    metier_id      INT NOT NULL,
    nom_competence VARCHAR(100) NOT NULL,
    niveau_minimum ENUM('Débutant','Intermédiaire','Avancé','Expert') DEFAULT 'Intermédiaire',
    FOREIGN KEY (metier_id) REFERENCES metiers(id) ON DELETE CASCADE
);

-- ── Table evolution_carriere (Métier Avancé #2 - Évolution) ───
CREATE TABLE IF NOT EXISTS evolution_carriere (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    metier_id        INT NOT NULL,
    metier_suivant_id INT NOT NULL,
    annees_requises  INT DEFAULT 2,
    competences_gap  TEXT COMMENT 'compétences manquantes séparées par virgule',
    FOREIGN KEY (metier_id)         REFERENCES metiers(id) ON DELETE CASCADE,
    FOREIGN KEY (metier_suivant_id) REFERENCES metiers(id) ON DELETE CASCADE
);

-- ============================================================
--  DONNÉES DE TEST
-- ============================================================

INSERT INTO utilisateur (nom, prenom, email, motDePasse, role) VALUES
('Ben Salah', 'Yassine', 'yassine@esprit.tn', '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'admin'),
('Trabelsi',  'Fatima',  'fatima@gmail.com',  '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
('Hamdi',     'Karim',   'karim@yahoo.fr',    '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user'),
('Gharbi',    'Sonia',   'sonia@outlook.com', '$2y$10$abcdefghij1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZ012345', 'user');

INSERT INTO profil (bio, photoProfil, ville, pays, langue, idUtilisateur) VALUES
('Administrateur passionné de tech.', 'default.png', 'Tunis',   'Tunisie', 'Français', 1),
('Étudiante en développement web.',   'default.png', 'Sfax',    'Tunisie', 'Français', 2),
('Freelance développeur PHP.',        'default.png', 'Sousse',  'Tunisie', 'Arabe',    3),
('Créatrice de contenu digital.',     'default.png', 'Bizerte', 'Tunisie', 'Anglais',  4);

INSERT INTO cv (email, telephone, adresse, titre_poste, description, github, linkedin) VALUES
('yassine@esprit.tn', '54678900', 'Tunis',   'Développeur Full-Stack', 'Passionné par le développement web.',  'https://github.com/yassine', 'https://linkedin.com/in/yassine'),
('fatima@gmail.com',  '54678901', 'Sfax',    'Développeuse Front-End', 'Spécialisée en React.',                'https://github.com/fatima',  'https://linkedin.com/in/fatima'),
('karim@yahoo.fr',    '54678902', 'Sousse',  'Développeur PHP Senior', 'Expert Laravel.',                     'https://github.com/karim',   NULL),
('sonia@outlook.com', '54678903', 'Bizerte', 'UX/UI Designer',         'Créatrice d''expériences digitales.', NULL,                         'https://linkedin.com/in/sonia');

INSERT INTO competences (cv_id, nom_competence, niveau, categorie) VALUES
(1, 'PHP',         'Expert',        'Back-End'),
(1, 'JavaScript',  'Avancé',        'Front-End'),
(1, 'React',       'Intermédiaire', 'Front-End'),
(1, 'MySQL',       'Avancé',        'Base de données'),
(2, 'React',       'Expert',        'Front-End'),
(2, 'CSS/Tailwind','Avancé',        'Front-End'),
(2, 'JavaScript',  'Avancé',        'Front-End'),
(3, 'Laravel',     'Expert',        'Back-End'),
(3, 'PHP',         'Expert',        'Back-End'),
(3, 'MySQL',       'Avancé',        'Base de données'),
(4, 'Figma',       'Expert',        'Design'),
(4, 'Adobe XD',    'Intermédiaire', 'Design');

INSERT INTO experiences (cv_id, titre, entreprise, lieu, date_debut, date_fin, en_cours, description, type_contrat) VALUES
(1, 'Développeur Full-Stack',    'Vermeg',           'Tunis',  '2022-09-01', NULL,         1, 'Développement de solutions bancaires en PHP/Symfony et React.', 'CDI'),
(1, 'Stagiaire Développeur Web', 'Sofrecom Tunisia', 'Tunis',  '2021-06-01', '2021-08-31', 0, 'Développement d''une application de gestion interne.',          'Stage'),
(2, 'Développeuse Front-End',    'Telnet',           'Sfax',   '2023-01-15', NULL,         1, 'Intégration d''interfaces React pour des clients.',              'CDI'),
(2, 'Freelance Front-End',       'Auto-entrepreneur','Remote', '2020-03-01', '2022-12-31', 0, 'Projets freelance pour des startups tunisiennes.',              'Freelance'),
(3, 'Lead Développeur PHP',      'Proxym Group',     'Sousse', '2019-04-01', NULL,         1, 'Architecture de plateformes SaaS en Laravel.',                  'CDI'),
(3, 'Stagiaire PHP',             'SopraHR',          'Sousse', '2018-07-01', '2018-09-30', 0, 'Maintenance d''un ERP interne.',                                'Stage'),
(4, 'UX/UI Designer Senior',     'Talan Tunisie',    'Bizerte','2021-10-01', NULL,         1, 'Design d''applications mobiles et web B2B.',                    'CDI'),
(4, 'Alternante Designer',       'Orange Tunisie',   'Tunis',  '2019-09-01', '2021-06-30', 0, 'Création de supports visuels et prototypage Figma.',            'Alternance');

-- ── Données métiers ───────────────────────────────────────────
INSERT INTO metiers (titre, description, secteur, salaire_min, salaire_max) VALUES
('Développeur Backend Junior',   'Développe des API et logiques serveur.',              'Informatique',  1200, 2000),
('Développeur Backend Senior',   'Conçoit des architectures backend complexes.',        'Informatique',  2500, 4000),
('Lead Développeur Backend',     'Pilote une équipe backend et prend des décisions.',   'Informatique',  4000, 6000),
('Architecte Logiciel',          'Définit l''architecture globale des systèmes.',       'Informatique',  5000, 8000),
('Développeur Frontend Junior',  'Intègre des interfaces web avec HTML/CSS/JS.',        'Informatique',  1200, 1900),
('Développeur Frontend Senior',  'Développe des SPA complexes en React/Vue.',           'Informatique',  2500, 3800),
('Lead Frontend',                'Encadre une équipe frontend et fixe les standards.',  'Informatique',  3800, 5500),
('UX/UI Designer Junior',        'Crée des maquettes et wireframes.',                   'Design',         900, 1600),
('UX/UI Designer Senior',        'Dirige la conception d''expériences utilisateur.',    'Design',        2000, 3500),
('Lead Designer / Design Lead',  'Définit la vision design produit.',                   'Design',        3500, 5000),
('Développeur Full-Stack',       'Travaille aussi bien backend que frontend.',           'Informatique',  2000, 4000),
('DevOps Engineer',              'Gère la CI/CD, les infras et le déploiement.',        'Informatique',  3000, 5500);

-- ── Compétences requises par métier ──────────────────────────
INSERT INTO metier_competences (metier_id, nom_competence, niveau_minimum) VALUES
-- Développeur Backend Junior (id=1)
(1,'PHP','Intermédiaire'),(1,'MySQL','Débutant'),(1,'JavaScript','Débutant'),
-- Développeur Backend Senior (id=2)
(2,'PHP','Avancé'),(2,'MySQL','Avancé'),(2,'Laravel','Avancé'),(2,'JavaScript','Intermédiaire'),
-- Lead Développeur Backend (id=3)
(3,'PHP','Expert'),(3,'MySQL','Avancé'),(3,'Laravel','Expert'),(3,'Docker','Intermédiaire'),
-- Architecte Logiciel (id=4)
(4,'PHP','Expert'),(4,'MySQL','Expert'),(4,'Docker','Avancé'),(4,'AWS','Intermédiaire'),
-- Développeur Frontend Junior (id=5)
(5,'JavaScript','Intermédiaire'),(5,'CSS/Tailwind','Débutant'),(5,'React','Débutant'),
-- Développeur Frontend Senior (id=6)
(6,'JavaScript','Avancé'),(6,'React','Avancé'),(6,'CSS/Tailwind','Avancé'),
-- Lead Frontend (id=7)
(7,'JavaScript','Expert'),(7,'React','Expert'),(7,'CSS/Tailwind','Expert'),
-- UX/UI Designer Junior (id=8)
(8,'Figma','Intermédiaire'),(8,'Adobe XD','Débutant'),
-- UX/UI Designer Senior (id=9)
(9,'Figma','Avancé'),(9,'Adobe XD','Intermédiaire'),
-- Lead Designer (id=10)
(10,'Figma','Expert'),(10,'Adobe XD','Avancé'),
-- Développeur Full-Stack (id=11)
(11,'PHP','Avancé'),(11,'JavaScript','Avancé'),(11,'React','Intermédiaire'),(11,'MySQL','Avancé'),
-- DevOps (id=12)
(12,'Docker','Avancé'),(12,'AWS','Intermédiaire'),(12,'MySQL','Intermédiaire');

-- ── Évolution de carrière ─────────────────────────────────────
INSERT INTO evolution_carriere (metier_id, metier_suivant_id, annees_requises, competences_gap) VALUES
-- Backend : Junior → Senior → Lead → Architecte
(1, 2,  2, 'Laravel,Docker'),
(2, 3,  3, 'Docker,CI/CD'),
(3, 4,  3, 'AWS,Kubernetes'),
-- Frontend : Junior → Senior → Lead
(5, 6,  2, 'TypeScript,Testing'),
(6, 7,  3, 'Architecture Frontend,Mentoring'),
-- Design : Junior → Senior → Lead
(8, 9,  2, 'Recherche UX,Prototypage avancé'),
(9, 10, 3, 'Stratégie produit,Leadership');
