<?php
require_once __DIR__ . '/../config/config.php';

class Cv
{
    // ── Propriétés privées ────────────────────────────────────────────────
    private ?int    $id             = null;
    private string  $email          = '';
    private string  $telephone      = '';
    private string  $adresse        = '';
    private string  $titre_poste    = '';
    private string  $description    = '';
    private string  $github         = '';
    private string  $linkedin       = '';
    private string  $site_web       = '';
    private string  $date_naissance = '';
    private string  $photo          = '';

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ── Getters ───────────────────────────────────────────────────────────
    public function getId(): ?int            { return $this->id; }
    public function getEmail(): string       { return $this->email; }
    public function getTelephone(): string   { return $this->telephone; }
    public function getAdresse(): string     { return $this->adresse; }
    public function getTitrePoste(): string  { return $this->titre_poste; }
    public function getDescription(): string { return $this->description; }
    public function getGithub(): string      { return $this->github; }
    public function getLinkedin(): string    { return $this->linkedin; }
    public function getSiteWeb(): string     { return $this->site_web; }
    public function getDateNaissance(): string { return $this->date_naissance; }
    public function getPhoto(): string       { return $this->photo; }

    // ── Setters ───────────────────────────────────────────────────────────
    public function setId(?int $id): void              { $this->id = $id; }
    public function setEmail(string $v): void          { $this->email = trim($v); }
    public function setTelephone(string $v): void      { $this->telephone = trim($v); }
    public function setAdresse(string $v): void        { $this->adresse = trim($v); }
    public function setTitrePoste(string $v): void     { $this->titre_poste = trim($v); }
    public function setDescription(string $v): void    { $this->description = trim($v); }
    public function setGithub(string $v): void         { $this->github = trim($v); }
    public function setLinkedin(string $v): void       { $this->linkedin = trim($v); }
    public function setSiteWeb(string $v): void        { $this->site_web = trim($v); }
    public function setDateNaissance(string $v): void  { $this->date_naissance = trim($v); }
    public function setPhoto(string $v): void          { $this->photo = trim($v); }

    // ── Hydratation depuis un tableau PDO ─────────────────────────────────
    public static function fromArray(array $row): self
    {
        $obj = new self();
        $obj->setId((int)($row['id'] ?? 0));
        $obj->setEmail($row['email']               ?? '');
        $obj->setTelephone($row['telephone']       ?? '');
        $obj->setAdresse($row['adresse']           ?? '');
        $obj->setTitrePoste($row['titre_poste']    ?? '');
        $obj->setDescription($row['description']   ?? '');
        $obj->setGithub($row['github']             ?? '');
        $obj->setLinkedin($row['linkedin']         ?? '');
        $obj->setSiteWeb($row['site_web']          ?? '');
        $obj->setDateNaissance($row['date_naissance'] ?? '');
        $obj->setPhoto($row['photo']               ?? '');
        return $obj;
    }

    // Convertit l'objet en tableau (pratique pour les vues)
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'email'          => $this->email,
            'telephone'      => $this->telephone,
            'adresse'        => $this->adresse,
            'titre_poste'    => $this->titre_poste,
            'description'    => $this->description,
            'github'         => $this->github,
            'linkedin'       => $this->linkedin,
            'site_web'       => $this->site_web,
            'date_naissance' => $this->date_naissance,
            'photo'          => $this->photo,
        ];
    }

    // ── Méthodes CRUD avec PDO ────────────────────────────────────────────
    public function findAll(): array
    {
        $rows = $this->pdo->query("SELECT * FROM cv ORDER BY id DESC")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findByTitre(string $search): array
    {
        $st = $this->pdo->prepare("SELECT * FROM cv WHERE titre_poste LIKE :search ORDER BY id DESC");
        $st->execute([':search' => '%' . $search . '%']);
        return array_map([self::class, 'fromArray'], $st->fetchAll());
    }

    public function findAllSortedByDate(string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $rows = $this->pdo->query("SELECT * FROM cv ORDER BY date_naissance $order")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findById(int $id): ?self
    {
        $st = $this->pdo->prepare("SELECT * FROM cv WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ? self::fromArray($row) : null;
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

    public function create(): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO cv
                (email, telephone, adresse, titre_poste, description, github, linkedin, site_web, date_naissance, photo)
            VALUES
                (:email, :telephone, :adresse, :titre_poste, :description, :github, :linkedin, :site_web, :date_naissance, :photo)
        ");
        $ok = $st->execute([
            ':email'          => $this->email,
            ':telephone'      => $this->telephone,
            ':adresse'        => $this->adresse,
            ':titre_poste'    => $this->titre_poste,
            ':description'    => $this->description,
            ':github'         => $this->github,
            ':linkedin'       => $this->linkedin,
            ':site_web'       => $this->site_web,
            ':date_naissance' => $this->date_naissance,
            ':photo'          => $this->photo,
        ]);
        if ($ok) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function update(): bool
    {
        $st = $this->pdo->prepare("
            UPDATE cv SET
                email=:email, telephone=:telephone, adresse=:adresse,
                titre_poste=:titre_poste, description=:description,
                github=:github, linkedin=:linkedin, site_web=:site_web,
                date_naissance=:date_naissance, photo=:photo
            WHERE id=:id
        ");
        return $st->execute([
            ':id'             => $this->id,
            ':email'          => $this->email,
            ':telephone'      => $this->telephone,
            ':adresse'        => $this->adresse,
            ':titre_poste'    => $this->titre_poste,
            ':description'    => $this->description,
            ':github'         => $this->github,
            ':linkedin'       => $this->linkedin,
            ':site_web'       => $this->site_web,
            ':date_naissance' => $this->date_naissance,
            ':photo'          => $this->photo,
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM cv WHERE id = :id");
        return $st->execute([':id' => $id]);
    }
}
