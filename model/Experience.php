<?php
require_once __DIR__ . '/../config/config.php';

class Experience
{
    // ── Propriétés privées ────────────────────────────────────────────────
    private ?int    $id           = null;
    private int     $cv_id        = 0;
    private string  $titre        = '';
    private string  $entreprise   = '';
    private string  $lieu         = '';
    private string  $date_debut   = '';
    private ?string $date_fin     = null;
    private bool    $en_cours     = false;
    private string  $description  = '';
    private string  $type_contrat = 'Autre';

    // Champ jointure (lecture seule, depuis la requête JOIN)
    private string  $titre_poste  = '';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ── Getters ───────────────────────────────────────────────────────────
    public function getId(): ?int           { return $this->id; }
    public function getCvId(): int          { return $this->cv_id; }
    public function getTitre(): string      { return $this->titre; }
    public function getEntreprise(): string { return $this->entreprise; }
    public function getLieu(): string       { return $this->lieu; }
    public function getDateDebut(): string  { return $this->date_debut; }
    public function getDateFin(): ?string   { return $this->date_fin; }
    public function isEnCours(): bool       { return $this->en_cours; }
    public function getDescription(): string{ return $this->description; }
    public function getTypeContrat(): string{ return $this->type_contrat; }
    public function getTitrePoste(): string { return $this->titre_poste; }

    // ── Setters ───────────────────────────────────────────────────────────
    public function setId(?int $v): void           { $this->id = $v; }
    public function setCvId(int $v): void          { $this->cv_id = $v; }
    public function setTitre(string $v): void      { $this->titre = trim($v); }
    public function setEntreprise(string $v): void { $this->entreprise = trim($v); }
    public function setLieu(string $v): void       { $this->lieu = trim($v); }
    public function setDateDebut(string $v): void  { $this->date_debut = trim($v); }
    public function setDateFin(?string $v): void   { $this->date_fin = ($v !== '' && $v !== null) ? trim($v) : null; }
    public function setEnCours(bool $v): void      { $this->en_cours = $v; }
    public function setDescription(string $v): void{ $this->description = trim($v); }
    public function setTypeContrat(string $v): void{ $this->type_contrat = trim($v); }
    public function setTitrePoste(string $v): void { $this->titre_poste = trim($v); }

    // ── Hydratation depuis un tableau PDO ─────────────────────────────────
    public static function fromArray(array $row): self
    {
        $obj = new self();
        $obj->setId((int)($row['id'] ?? 0));
        $obj->setCvId((int)($row['cv_id'] ?? 0));
        $obj->setTitre($row['titre']               ?? '');
        $obj->setEntreprise($row['entreprise']     ?? '');
        $obj->setLieu($row['lieu']                 ?? '');
        $obj->setDateDebut($row['date_debut']      ?? '');
        $obj->setDateFin($row['date_fin']          ?? null);
        $obj->setEnCours((bool)($row['en_cours']   ?? false));
        $obj->setDescription($row['description']   ?? '');
        $obj->setTypeContrat($row['type_contrat']  ?? 'Autre');
        $obj->setTitrePoste($row['titre_poste']    ?? '');
        return $obj;
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'cv_id'        => $this->cv_id,
            'titre'        => $this->titre,
            'entreprise'   => $this->entreprise,
            'lieu'         => $this->lieu,
            'date_debut'   => $this->date_debut,
            'date_fin'     => $this->date_fin,
            'en_cours'     => $this->en_cours,
            'description'  => $this->description,
            'type_contrat' => $this->type_contrat,
            'titre_poste'  => $this->titre_poste,
        ];
    }

    // ── Méthodes CRUD avec PDO ────────────────────────────────────────────
    public function findAll(): array
    {
        $rows = $this->pdo->query("
            SELECT experiences.*, cv.titre_poste
            FROM experiences
            LEFT JOIN cv ON experiences.cv_id = cv.id
            ORDER BY experiences.id DESC
        ")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findByTitre(string $search): array
    {
        $st = $this->pdo->prepare("
            SELECT experiences.*, cv.titre_poste
            FROM experiences
            LEFT JOIN cv ON experiences.cv_id = cv.id
            WHERE experiences.titre LIKE :search
            ORDER BY experiences.date_debut DESC
        ");
        $st->execute([':search' => '%' . $search . '%']);
        return array_map([self::class, 'fromArray'], $st->fetchAll());
    }

    public function findAllSortedByDate(string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $rows = $this->pdo->query("
            SELECT experiences.*, cv.titre_poste
            FROM experiences
            LEFT JOIN cv ON experiences.cv_id = cv.id
            ORDER BY experiences.date_debut $order
        ")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findById(int $id): ?self
    {
        $st = $this->pdo->prepare("SELECT * FROM experiences WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ? self::fromArray($row) : null;
    }

    public function findByCvId(int $cvId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM experiences WHERE cv_id = :cv_id ORDER BY date_debut DESC");
        $st->execute([':cv_id' => $cvId]);
        return array_map([self::class, 'fromArray'], $st->fetchAll());
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
    }

    public function create(): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO experiences
                (cv_id, titre, entreprise, lieu, date_debut, date_fin, en_cours, description, type_contrat)
            VALUES
                (:cv_id, :titre, :entreprise, :lieu, :date_debut, :date_fin, :en_cours, :description, :type_contrat)
        ");
        $ok = $st->execute([
            ':cv_id'        => $this->cv_id,
            ':titre'        => $this->titre,
            ':entreprise'   => $this->entreprise,
            ':lieu'         => $this->lieu,
            ':date_debut'   => $this->date_debut,
            ':date_fin'     => $this->date_fin,
            ':en_cours'     => (int)$this->en_cours,
            ':description'  => $this->description,
            ':type_contrat' => $this->type_contrat,
        ]);
        if ($ok) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function update(): bool
    {
        $st = $this->pdo->prepare("
            UPDATE experiences SET
                cv_id=:cv_id, titre=:titre, entreprise=:entreprise,
                lieu=:lieu, date_debut=:date_debut, date_fin=:date_fin,
                en_cours=:en_cours, description=:description, type_contrat=:type_contrat
            WHERE id=:id
        ");
        return $st->execute([
            ':id'           => $this->id,
            ':cv_id'        => $this->cv_id,
            ':titre'        => $this->titre,
            ':entreprise'   => $this->entreprise,
            ':lieu'         => $this->lieu,
            ':date_debut'   => $this->date_debut,
            ':date_fin'     => $this->date_fin,
            ':en_cours'     => (int)$this->en_cours,
            ':description'  => $this->description,
            ':type_contrat' => $this->type_contrat,
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM experiences WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
