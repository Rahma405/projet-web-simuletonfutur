<?php
require_once __DIR__ . '/../config/config.php';

class Competence
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function findAll(): array
    {
        return $this->pdo->query("
            SELECT competences.*, cv.titre_poste
            FROM competences
            LEFT JOIN cv ON competences.cv_id = cv.id
            ORDER BY competences.id DESC
        ")->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM competences WHERE id = :id");
        $st->execute([':id' => $id]);
        return $st->fetch();
    }

    public function findByCvId(int $cvId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM competences WHERE cv_id = :cv_id ORDER BY categorie");
        $st->execute([':cv_id' => $cvId]);
        return $st->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM competences")->fetchColumn();
    }

    public function create(array $d): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO competences (cv_id,nom_competence,niveau,categorie)
            VALUES (:cv_id,:nom_competence,:niveau,:categorie)
        ");
        return $st->execute([
            ':cv_id'          => $d['cv_id'],
            ':nom_competence' => $d['nom_competence'],
            ':niveau'         => $d['niveau'],
            ':categorie'      => $d['categorie'],
        ]);
    }

    public function update(int $id, array $d): bool
    {
        $st = $this->pdo->prepare("
            UPDATE competences SET cv_id=:cv_id,nom_competence=:nom_competence,
                niveau=:niveau,categorie=:categorie WHERE id=:id
        ");
        return $st->execute([
            ':id'             => $id,
            ':cv_id'          => $d['cv_id'],
            ':nom_competence' => $d['nom_competence'],
            ':niveau'         => $d['niveau'],
            ':categorie'      => $d['categorie'],
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM competences WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
