<?php
require_once __DIR__ . '/../config/config.php';

class Cv
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function findAll(): array
    {
        return $this->pdo->query("SELECT * FROM cv ORDER BY id DESC")->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM cv WHERE id = :id");
        $st->execute([':id' => $id]);
        return $st->fetch();
    }

    public function findWithCompetences(int $id): array
    {
        $st = $this->pdo->prepare("
            SELECT cv.*,
                   competences.id        AS comp_id,
                   competences.nom_competence,
                   competences.niveau,
                   competences.categorie
            FROM cv
            LEFT JOIN competences ON cv.id = competences.cv_id
            WHERE cv.id = :id
        ");
        $st->execute([':id' => $id]);
        return $st->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM cv")->fetchColumn();
    }

    public function create(array $d): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO cv (email,telephone,adresse,titre_poste,description,github,linkedin,site_web,date_naissance,photo)
            VALUES (:email,:telephone,:adresse,:titre_poste,:description,:github,:linkedin,:site_web,:date_naissance,:photo)
        ");
        return $st->execute([
            ':email'          => $d['email'],
            ':telephone'      => $d['telephone']      ?? '',
            ':adresse'        => $d['adresse']        ?? '',
            ':titre_poste'    => $d['titre_poste'],
            ':description'    => $d['description']    ?? '',
            ':github'         => $d['github']         ?? '',
            ':linkedin'       => $d['linkedin']       ?? '',
            ':site_web'       => $d['site_web']       ?? '',
            ':date_naissance' => $d['date_naissance'] ?? '',
            ':photo'          => $d['photo']          ?? '',
        ]);
    }

    public function update(int $id, array $d): bool
    {
        $st = $this->pdo->prepare("
            UPDATE cv SET email=:email,telephone=:telephone,adresse=:adresse,
                titre_poste=:titre_poste,description=:description,
                github=:github,linkedin=:linkedin,site_web=:site_web,
                date_naissance=:date_naissance,photo=:photo
            WHERE id=:id
        ");
        return $st->execute([
            ':id'             => $id,
            ':email'          => $d['email'],
            ':telephone'      => $d['telephone']      ?? '',
            ':adresse'        => $d['adresse']        ?? '',
            ':titre_poste'    => $d['titre_poste'],
            ':description'    => $d['description']    ?? '',
            ':github'         => $d['github']         ?? '',
            ':linkedin'       => $d['linkedin']       ?? '',
            ':site_web'       => $d['site_web']       ?? '',
            ':date_naissance' => $d['date_naissance'] ?? '',
            ':photo'          => $d['photo']          ?? '',
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM cv WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
