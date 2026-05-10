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

    /** Tri par date_naissance ou id, ordre ASC/DESC */
    public function findAllSorted(string $sort = 'id', string $order = 'DESC'): array
    {
        $allowed_sort  = ['id', 'date_naissance', 'titre_poste'];
        $allowed_order = ['ASC', 'DESC'];
        $sort  = in_array($sort, $allowed_sort)   ? $sort  : 'id';
        $order = in_array(strtoupper($order), $allowed_order) ? strtoupper($order) : 'DESC';
        return $this->pdo->query("SELECT * FROM cv ORDER BY $sort $order")->fetchAll();
    }

    /** Recherche par titre_poste */
    public function search(string $q, string $sort = 'id', string $order = 'DESC'): array
    {
        $allowed_sort  = ['id', 'date_naissance', 'titre_poste'];
        $allowed_order = ['ASC', 'DESC'];
        $sort  = in_array($sort, $allowed_sort)   ? $sort  : 'id';
        $order = in_array(strtoupper($order), $allowed_order) ? strtoupper($order) : 'DESC';
        $st = $this->pdo->prepare("SELECT * FROM cv WHERE titre_poste LIKE :q ORDER BY $sort $order");
        $st->execute([':q' => '%' . $q . '%']);
        return $st->fetchAll();
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

    public function findWithAll(int $id): array
    {
        // competences
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
        $rows = $st->fetchAll();
        if (!$rows) return [];
        $cv = $rows[0];
        $competences = [];
        foreach ($rows as $row) {
            if ($row['comp_id']) {
                $competences[] = [
                    'id'             => $row['comp_id'],
                    'nom_competence' => $row['nom_competence'],
                    'niveau'         => $row['niveau'],
                    'categorie'      => $row['categorie'],
                ];
            }
        }
        // experiences
        $st2 = $this->pdo->prepare("SELECT * FROM experiences WHERE cv_id = :id ORDER BY date_debut DESC");
        $st2->execute([':id' => $id]);
        $experiences = $st2->fetchAll();
        return ['cv' => $cv, 'competences' => $competences, 'experiences' => $experiences];
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
