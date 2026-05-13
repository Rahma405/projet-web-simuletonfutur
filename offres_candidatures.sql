-- ============================================================
--  Simule Ton Futur  |  Module Offres & Candidatures
--  ESPRIT - UP Web - AU 2025/2026
-- ============================================================

USE simule_ton_futur;

-- ── Table offre ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS offre (
    idOffre       INT AUTO_INCREMENT PRIMARY KEY,
    titre         VARCHAR(100)  NOT NULL,
    competences   TEXT          NOT NULL COMMENT 'Compétences requises, séparées par des virgules',
    localisation  VARCHAR(100)  NOT NULL
);

-- ── Table candidature ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS candidature (
    idCandidature  INT AUTO_INCREMENT PRIMARY KEY,
    idUtilisateur  INT NOT NULL,
    skills         TEXT         NOT NULL COMMENT 'Compétences du candidat, séparées par des virgules',
    cv             VARCHAR(255) DEFAULT NULL,
    localisation   VARCHAR(100) NOT NULL,
    FOREIGN KEY (idUtilisateur)
        REFERENCES utilisateur(idUtilisateur)
        ON DELETE CASCADE
);

-- ── Données de test : offres ────────────────────────────────
INSERT INTO offre (titre, competences, localisation) VALUES
('Développeur PHP Full Stack',   'PHP,MySQL,HTML,CSS,JavaScript',        'Tunis'),
('Designer UI/UX',               'Figma,Photoshop,CSS,HTML',             'Sfax'),
('Data Analyst',                 'Python,SQL,Excel,Power BI',            'Tunis'),
('Développeur Mobile',           'Flutter,Dart,Firebase,REST API',       'Sousse'),
('Chef de Projet Digital',       'Agile,Scrum,Jira,Communication',       'Tunis'),
('Développeur Frontend React',   'React,JavaScript,CSS,HTML,Git',        'Bizerte'),
('Administrateur Système',       'Linux,Docker,Bash,Réseau',             'Tunis'),
('Community Manager',            'Réseaux sociaux,Canva,Rédaction,SEO',  'Sfax');

-- ── Données de test : candidatures ──────────────────────────
INSERT INTO candidature (idUtilisateur, skills, cv, localisation) VALUES
(2, 'PHP,HTML,CSS,JavaScript',      'cv_fatima.pdf',  'Sfax'),
(3, 'PHP,MySQL,Laravel,Git',        'cv_karim.pdf',   'Sousse'),
(4, 'Figma,Canva,Réseaux sociaux',  'cv_sonia.pdf',   'Bizerte');
