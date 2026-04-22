<?php
require_once __DIR__ . '/../config/config.php';

class Experience
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function findAll(): array
    {
        return $this->pdo->query("
            SELECT experiences.*, cv.titre_poste
            FROM experiences
            LEFT JOIN cv ON experiences.cv_id = cv.id
            ORDER BY experiences.id DESC
        ")->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM experiences WHERE id = :id");
        $st->execute([':id' => $id]);
        return $st->fetch();
    }

    public function findByCvId(int $cvId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM experiences WHERE cv_id = :cv_id ORDER BY date_debut DESC");
        $st->execute([':cv_id' => $cvId]);
        return $st->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
    }

    public function create(array $d): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO experiences (cv_id, titre, entreprise, lieu, date_debut, date_fin, en_cours, description, type_contrat)
            VALUES (:cv_id, :titre, :entreprise, :lieu, :date_debut, :date_fin, :en_cours, :description, :type_contrat)
        ");
        return $st->execute([
            ':cv_id'        => $d['cv_id'],
            ':titre'        => $d['titre'],
            ':entreprise'   => $d['entreprise']   ?? '',
            ':lieu'         => $d['lieu']          ?? '',
            ':date_debut'   => $d['date_debut'],
            ':date_fin'     => !empty($d['date_fin']) ? $d['date_fin'] : null,
            ':en_cours'     => isset($d['en_cours']) ? 1 : 0,
            ':description'  => $d['description']   ?? '',
            ':type_contrat' => $d['type_contrat']  ?? 'Autre',
        ]);
    }

    public function update(int $id, array $d): bool
    {
        $st = $this->pdo->prepare("
            UPDATE experiences SET
                cv_id=:cv_id, titre=:titre, entreprise=:entreprise,
                lieu=:lieu, date_debut=:date_debut, date_fin=:date_fin,
                en_cours=:en_cours, description=:description, type_contrat=:type_contrat
            WHERE id=:id
        ");
        return $st->execute([
            ':id'           => $id,
            ':cv_id'        => $d['cv_id'],
            ':titre'        => $d['titre'],
            ':entreprise'   => $d['entreprise']   ?? '',
            ':lieu'         => $d['lieu']          ?? '',
            ':date_debut'   => $d['date_debut'],
            ':date_fin'     => !empty($d['date_fin']) ? $d['date_fin'] : null,
            ':en_cours'     => isset($d['en_cours']) ? 1 : 0,
            ':description'  => $d['description']   ?? '',
            ':type_contrat' => $d['type_contrat']  ?? 'Autre',
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM experiences WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
