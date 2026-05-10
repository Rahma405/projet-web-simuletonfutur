# SimuleTonFutur 

> Plateforme interactive qui aide toute personne à explorer et simuler le monde du travail moderne — à travers de vrais choix avec de vraies conséquences.

Développé dans le cadre du **PIDEV – 2ème Année Ingénierie** à **Esprit School of Engineering** (Année Universitaire 2025–2026).

---

## Table des Matières

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contributions](#contributions)
- [Contexte Académique](#contexte-académique)
- [Licence](#licence)

---

## Aperçu

**SimuleTonFutur** est une application web interactive qui permet à l'utilisateur de simuler des parcours professionnels réels et de prendre des décisions de carrière — sans aucun risque.

Au lieu de simplement lire des informations sur les métiers, l'utilisateur **vit l'expérience** : il fait des choix, observe les conséquences, et découvre quel chemin professionnel lui correspond le mieux.

### À qui s'adresse la plateforme ?

| 🎓 Étudiants | Choisir entre salariat et entrepreneuriat |
| 🧑‍💼 Jeunes diplômés | Devenir freelance ou intégrer une entreprise |
| 🔄 Personnes en reconversion | Changer de secteur d'activité |
| 💻 Chercheurs de télétravail | Gérer un emploi à distance |
| 🎥 Créateurs de contenu | Lancer une carrière digitale |
| 🚀 Futurs entrepreneurs | Créer et piloter une startup |
| 🛒 Lanceurs d'activité en ligne | Démarrer un business ou e-commerce |

### Que peut-on simuler ?

- 💼 Devenir **freelance**
- 🚀 Créer une **startup**
- 🏠 Travailler en **télétravail**
- 🎥 Devenir **créateur de contenu**
- 📚 Apprendre une **compétence digitale**
- 🛒 Lancer une **activité en ligne**

---

## Fonctionnalités

- 🔐 Inscription et authentification sécurisée des utilisateurs
- 🤳 Connexion par **Face ID** (reconnaissance faciale via webcam)
- 🔑 Réinitialisation de mot de passe par email
- 🧩 **hCaptcha** anti-bot intégré
- 👤 Gestion complète du profil utilisateur (photo, bio, ville, pays, langue)
- 🌍 Interface **multilingue** : Français, Anglais, Espagnol, Arabe, Allemand
- 🛠️ Panneau d'administration (back-office) : gestion des utilisateurs et des profils
- 📊 Dashboard avec statistiques (utilisateurs inscrits, profils créés, taux de complétion)
- 📄 Export des données en **PDF**
- 🗺️ Moteur de simulation de parcours professionnels interactif

---

## Tech Stack

### Frontend
-   JavaScript
- Bootstrap 5
- face-api.js (reconnaissance faciale)

### Backend
- PHP 8.x (Architecture MVC)
- PDO (accès base de données)
- Sessions PHP (gestion authentification)

### Base de données
- MySQL 5.7+
- phpMyAdmin

### Outils & Workflow
- Git & GitHub (contrôle de version)
- VS Code (environnement de développement)
-  XAMPP (serveur local)
- hCaptcha (protection anti-bot)

---

## Architecture

Le projet suit le pattern **MVC (Modèle – Vue – Contrôleur)** :

```
Esprit-PIDEV-2A34-2026-SimuleTonFutur/
│
├── config.php                        # Connexion PDO (Singleton) + fonctions utilitaires
├── index.php                         # Point d'entrée de l'application
├── database.sql                      # Script SQL de la base de données
│
├── model/
│   ├── Utilisateur.php               # Modèle utilisateur
│   └── Profil.php                    # Modèle profil
│
├── controller/
│   ├── UtilisateurC.php              # Logique métier utilisateurs
│   └── ProfilC.php                   # Logique métier profils
│
└── view/
    ├── frontoffice/                  # Interface utilisateur (côté client)
    │   ├── login.php                 # Connexion classique
    │   ├── register.php              # Inscription
    │   ├── face_login.php            # Connexion Face ID
    │   ├── face_register.php         # Activation Face ID
    │   ├── qr_login_confirm.php      # Connexion QR Code
    │   ├── reset_password.php        # Réinitialisation mot de passe
    │   ├── list_utilisateurs.php     # Liste des utilisateurs
    │   ├── list_profils.php          # Liste des profils
    │   ├── edit_profil.php           # Modification du profil
    │   └── layouts/                  # Header & Footer communs
    │
    ├── backoffice/                   # Interface administrateur
    │   ├── list_utilisateurs.php     # Gestion des utilisateurs
    │   ├── list_profils.php          # Gestion des profils
    │   ├── add_utilisateur.php       # Ajout utilisateur
    │   ├── edit_utilisateur.php      # Modification utilisateur
    │   └── layouts/                  # Header & Footer admin
    │
    └── assets/
        ├── css/style.css             # Styles personnalisés
        ├── js/script.js              # Scripts JavaScript
        └── img/                      # Images & photos de profil
```

---

## Installation

### Prérequis

- [XAMPP](https://www.apachefriends.org/) installé
- PHP >= 8.0
- MySQL >= 5.7
- Git

### Étapes

**1. Cloner le repository :**

```bash
git clone https://github.com/Rahma405/Esprit-PIDEV-2A34-2026-SimuleTonFutur.git
cd Esprit-PIDEV-2A34-2026-SimuleTonFutur
```

**2. Configurer le serveur local :**

- Placer le dossier dans `htdocs/` (XAMPP)
- Démarrer **Apache** et **MySQL** depuis le panneau de contrôle WAMP/XAMPP

**3. Importer la base de données :**

- Ouvrir [phpMyAdmin](http://localhost/phpmyadmin)
- Créer une nouvelle base de données nommée `simule_ton_futur`
- Importer le fichier `database.sql` depuis la racine du projet

**4. Configurer la connexion :**

Ouvrir `config.php` et mettre à jour si nécessaire :

```php
$host   = 'localhost';
$dbname = 'simule_ton_futur';
$user   = 'root';
$pass   = '';
```

Mettre à jour aussi l'URL de base :

```php
private const APP_PUBLIC_URL = 'http://localhost/Esprit-PIDEV-2A34-2026-SimuleTonFutur/simule_ton_futur';
```

**5. Accéder à l'application :**

Ouvrir le navigateur et aller sur :

```
http://localhost/Esprit-PIDEV-2A34-2026-SimuleTonFutur/simule_ton_futur
```

---

## Utilisation

### Côté Utilisateur (Front-office)

1. Créer un compte via la page **Inscription**
2. Se connecter avec email/mot de passe, **Face ID**, ou **QR Code**
3. Compléter son profil (photo, bio, ville, pays, langue préférée)
4. Explorer et simuler des parcours professionnels
5. Consulter les résultats et recommandations

### Côté Administrateur (Back-office)

1. Se connecter avec un compte ayant le rôle `admin`
2. Gérer les utilisateurs (ajouter, modifier, bloquer, supprimer)
3. Gérer les profils de la plateforme
4. Consulter les statistiques du tableau de bord
5. Exporter les données en PDF

---

## Contributions

Nous remercions tous ceux qui contribuent à ce projet !

### Équipe du projet

| Rahma | Gestion Utilisateurs & Chef de projet | [@Rahma405](https://github.com/Rahma405) |
| Dorra | Gestion Portfolio | [@dorra12345](https://github.com/dorra12345) |
| Iyed | Gestion Offre Candidature | [@iyed222](https://github.com/iyed222) |
| Firas | Gestion Messagerie | [@firas_karoui](https://github.com/firas_karoui) |
| Aziz | Gestion Quiz | [@azizgan12](https://github.com/azizgan12) |




### Comment contribuer ?

1. **Forker** le repository
2. **Cloner** votre fork :

```bash
git clone https://github.com/votre-username/Esprit-PIDEV-2A34-2026-SimuleTonFutur.git
cd Esprit-PIDEV-2A34-2026-SimuleTonFutur
```

3. Créer une nouvelle **branche** pour votre fonctionnalité :

```bash
git checkout -b feature/nom-de-la-fonctionnalite
```

4. **Valider** vos modifications :

```bash
git add .
git commit -m "Ajout : description de la fonctionnalité"
```

5. **Pousser** et ouvrir une **Pull Request** :

```bash
git push origin feature/nom-de-la-fonctionnalite
```

---

## Contexte Académique


| Établissement | **Esprit School of Engineering** – Tunisie |
| Programme | Ingénierie – 2ème Année (PIDEV) |
| Année universitaire | 2025–2026 |
| Classe | 2A34 |
| Matière | Projet Intégré de Développement |

---

## Licence

Ce projet est développé à des **fins académiques** dans le cadre du programme **PIDEV** à **Esprit School of Engineering**.
Il n'est pas destiné à un usage commercial.

---

<p align="center">
  Réalisé avec ❤️ par l'équipe 2A34 – <strong>Esprit School of Engineering</strong> | PIDEV 2025–2026
</p>
