<?php
require_once __DIR__ . '/../config/config.php';

class Competence
{
    // ── Propriétés privées ────────────────────────────────────────────────
    private ?int   $id             = null;
    private int    $cv_id          = 0;
    private string $nom_competence = '';
    private string $niveau         = '';
    private string $categorie      = '';

    // Champ jointure (lecture seule)
    private string $titre_poste    = '';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ── Getters ───────────────────────────────────────────────────────────
    public function getId(): ?int             { return $this->id; }
    public function getCvId(): int            { return $this->cv_id; }
    public function getNomCompetence(): string{ return $this->nom_competence; }
    public function getNiveau(): string       { return $this->niveau; }
    public function getCategorie(): string    { return $this->categorie; }
    public function getTitrePoste(): string   { return $this->titre_poste; }

    // ── Setters ───────────────────────────────────────────────────────────
    public function setId(?int $v): void              { $this->id = $v; }
    public function setCvId(int $v): void             { $this->cv_id = $v; }
    public function setNomCompetence(string $v): void { $this->nom_competence = trim($v); }
    public function setNiveau(string $v): void        { $this->niveau = trim($v); }
    public function setCategorie(string $v): void     { $this->categorie = trim($v); }
    public function setTitrePoste(string $v): void    { $this->titre_poste = trim($v); }

    // ── Hydratation depuis un tableau PDO ─────────────────────────────────
    public static function fromArray(array $row): self
    {
        $obj = new self();
        $obj->setId((int)($row['id'] ?? 0));
        $obj->setCvId((int)($row['cv_id'] ?? 0));
        $obj->setNomCompetence($row['nom_competence'] ?? '');
        $obj->setNiveau($row['niveau']               ?? '');
        $obj->setCategorie($row['categorie']         ?? '');
        $obj->setTitrePoste($row['titre_poste']      ?? '');
        return $obj;
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'cv_id'          => $this->cv_id,
            'nom_competence' => $this->nom_competence,
            'niveau'         => $this->niveau,
            'categorie'      => $this->categorie,
            'titre_poste'    => $this->titre_poste,
        ];
    }

    // ── Méthodes CRUD avec PDO ────────────────────────────────────────────
    public function findAll(): array
    {
        $rows = $this->pdo->query("
            SELECT competences.*, cv.titre_poste
            FROM competences
            LEFT JOIN cv ON competences.cv_id = cv.id
            ORDER BY competences.id DESC
        ")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findById(int $id): ?self
    {
        $st = $this->pdo->prepare("SELECT * FROM competences WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ? self::fromArray($row) : null;
    }

    public function findByCvId(int $cvId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM competences WHERE cv_id = :cv_id ORDER BY categorie");
        $st->execute([':cv_id' => $cvId]);
        return array_map([self::class, 'fromArray'], $st->fetchAll());
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM competences")->fetchColumn();
    }

    public function create(): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO competences (cv_id, nom_competence, niveau, categorie)
            VALUES (:cv_id, :nom_competence, :niveau, :categorie)
        ");
        $ok = $st->execute([
            ':cv_id'          => $this->cv_id,
            ':nom_competence' => $this->nom_competence,
            ':niveau'         => $this->niveau,
            ':categorie'      => $this->categorie,
        ]);
        if ($ok) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function update(): bool
    {
        $st = $this->pdo->prepare("
            UPDATE competences SET
                cv_id=:cv_id, nom_competence=:nom_competence,
                niveau=:niveau, categorie=:categorie
            WHERE id=:id
        ");
        return $st->execute([
            ':id'             => $this->id,
            ':cv_id'          => $this->cv_id,
            ':nom_competence' => $this->nom_competence,
            ':niveau'         => $this->niveau,
            ':categorie'      => $this->categorie,
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM competences WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
