-- A executer dans phpMyAdmin sur la base: gestion portfilio
USE `gestion portfilio`;

CREATE TABLE IF NOT EXISTS metiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    secteur VARCHAR(80),
    salaire_min INT DEFAULT 0,
    salaire_max INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS metier_competences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metier_id INT NOT NULL,
    nom_competence VARCHAR(100) NOT NULL,
    niveau_minimum VARCHAR(30) DEFAULT 'Intermediaire',
    FOREIGN KEY (metier_id) REFERENCES metiers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS evolution_carriere (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metier_id INT NOT NULL,
    metier_suivant_id INT NOT NULL,
    annees_requises INT DEFAULT 2,
    competences_gap TEXT,
    FOREIGN KEY (metier_id) REFERENCES metiers(id) ON DELETE CASCADE,
    FOREIGN KEY (metier_suivant_id) REFERENCES metiers(id) ON DELETE CASCADE
);

INSERT INTO metiers (titre, description, secteur, salaire_min, salaire_max) VALUES
('Developpeur Backend Junior', 'Developpe des API et la logique serveur.', 'Informatique', 1200, 2000),
('Developpeur Backend Senior', 'Concoit des architectures backend robustes.', 'Informatique', 2500, 4000),
('Lead Developpeur Backend', 'Encadre une equipe backend et pilote les choix techniques.', 'Informatique', 4000, 6000),
('Architecte Logiciel', 'Definit l architecture globale des systemes.', 'Informatique', 5000, 8000),
('Developpeur Frontend Junior', 'Integre des interfaces web modernes.', 'Informatique', 1200, 1900),
('Developpeur Frontend Senior', 'Developpe des applications frontend complexes.', 'Informatique', 2500, 3800),
('Lead Frontend', 'Fixe les standards frontend et accompagne l equipe.', 'Informatique', 3800, 5500),
('UX UI Designer Junior', 'Cree des maquettes et prototypes.', 'Design', 900, 1600),
('UX UI Designer Senior', 'Concoit des experiences utilisateur avancees.', 'Design', 2000, 3500),
('Lead Designer', 'Definit la vision design produit.', 'Design', 3500, 5000),
('Developpeur Full Stack', 'Travaille cote backend et frontend.', 'Informatique', 2000, 4000),
('DevOps Engineer', 'Gere CI CD, infrastructure et deploiement.', 'Informatique', 3000, 5500),
('Support Client Multilingue', 'Accompagne les clients en plusieurs langues.', 'Langues', 1000, 2200),
('Traducteur Technique', 'Traduit des contenus techniques et produits.', 'Langues', 1200, 2600),
('Content Manager International', 'Cree et adapte des contenus pour plusieurs marches.', 'Langues', 1800, 3500);

INSERT INTO metier_competences (metier_id, nom_competence, niveau_minimum) VALUES
(1,'PHP','Intermediaire'),(1,'MySQL','Debutant'),(1,'JavaScript','Debutant'),
(2,'PHP','Avance'),(2,'MySQL','Avance'),(2,'Laravel','Avance'),(2,'JavaScript','Intermediaire'),
(3,'PHP','Expert'),(3,'MySQL','Avance'),(3,'Laravel','Expert'),(3,'Docker','Intermediaire'),
(4,'PHP','Expert'),(4,'MySQL','Expert'),(4,'Docker','Avance'),(4,'AWS','Intermediaire'),
(5,'JavaScript','Intermediaire'),(5,'CSS/Tailwind','Debutant'),(5,'React','Debutant'),
(6,'JavaScript','Avance'),(6,'React','Avance'),(6,'CSS/Tailwind','Avance'),
(7,'JavaScript','Expert'),(7,'React','Expert'),(7,'CSS/Tailwind','Expert'),
(8,'Figma','Intermediaire'),(8,'Adobe XD','Debutant'),
(9,'Figma','Avance'),(9,'Adobe XD','Intermediaire'),
(10,'Figma','Expert'),(10,'Adobe XD','Avance'),
(11,'PHP','Avance'),(11,'JavaScript','Avance'),(11,'React','Intermediaire'),(11,'MySQL','Avance'),
(12,'Docker','Avance'),(12,'AWS','Intermediaire'),(12,'MySQL','Intermediaire'),
(13,'Francais','Avance'),(13,'Anglais','Intermediaire'),(13,'Communication','Avance'),
(14,'Francais','Expert'),(14,'Anglais','Avance'),(14,'Redaction','Avance'),
(15,'Anglais','Avance'),(15,'Francais','Avance'),(15,'Redaction','Avance'),(15,'Communication','Intermediaire');

INSERT INTO evolution_carriere (metier_id, metier_suivant_id, annees_requises, competences_gap) VALUES
(1, 2, 2, 'Laravel,Docker'),
(2, 3, 3, 'Docker,CI/CD'),
(3, 4, 3, 'AWS,Kubernetes'),
(5, 6, 2, 'TypeScript,Testing'),
(6, 7, 3, 'Architecture Frontend,Mentoring'),
(8, 9, 2, 'Recherche UX,Prototypage avance'),
(9, 10, 3, 'Strategie produit,Leadership'),
(13, 14, 2, 'Redaction,Terminologie technique'),
(14, 15, 2, 'SEO international,Strategie contenu');
