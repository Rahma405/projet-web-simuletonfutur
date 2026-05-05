<?php
require_once __DIR__ . '/../config/config.php';

class Metier
{
    // ── Propriétés privées ────────────────────────────────────────────────
    private ?int   $id          = null;
    private string $titre       = '';
    private string $description = '';
    private string $secteur     = '';
    private int    $salaireMin  = 0;
    private int    $salaireMax  = 0;

    // Champ calculé (score de compatibilité, non stocké en BD)
    private int    $score       = 0;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ── Getters ───────────────────────────────────────────────────────────
    public function getId(): ?int         { return $this->id; }
    public function getTitre(): string    { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getSecteur(): string  { return $this->secteur; }
    public function getSalaireMin(): int  { return $this->salaireMin; }
    public function getSalaireMax(): int  { return $this->salaireMax; }
    public function getScore(): int       { return $this->score; }

    // ── Setters ───────────────────────────────────────────────────────────
    public function setId(?int $v): void          { $this->id = $v; }
    public function setTitre(string $v): void     { $this->titre = trim($v); }
    public function setDescription(string $v): void { $this->description = trim($v); }
    public function setSecteur(string $v): void   { $this->secteur = trim($v); }
    public function setSalaireMin(int $v): void   { $this->salaireMin = $v; }
    public function setSalaireMax(int $v): void   { $this->salaireMax = $v; }
    public function setScore(int $v): void        { $this->score = $v; }

    // ── Hydratation depuis tableau PDO ────────────────────────────────────
    public static function fromArray(array $row): self
    {
        $obj = new self();
        $obj->setId((int)($row['id'] ?? 0));
        $obj->setTitre($row['titre']             ?? '');
        $obj->setDescription($row['description'] ?? '');
        $obj->setSecteur($row['secteur']         ?? '');
        $obj->setSalaireMin((int)($row['salaire_min'] ?? 0));
        $obj->setSalaireMax((int)($row['salaire_max'] ?? 0));
        $obj->setScore((int)($row['score']       ?? 0));
        return $obj;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'titre'       => $this->titre,
            'description' => $this->description,
            'secteur'     => $this->secteur,
            'salaire_min' => $this->salaireMin,
            'salaire_max' => $this->salaireMax,
            'score'       => $this->score,
        ];
    }

    // ── Méthodes CRUD ─────────────────────────────────────────────────────
    public function findAll(): array
    {
        $rows = $this->pdo->query("SELECT * FROM metiers ORDER BY titre ASC")->fetchAll();
        return array_map([self::class, 'fromArray'], $rows);
    }

    public function findById(int $id): ?self
    {
        $st = $this->pdo->prepare("SELECT * FROM metiers WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ? self::fromArray($row) : null;
    }

    public function findBySecteur(string $secteur): array
    {
        $st = $this->pdo->prepare("SELECT * FROM metiers WHERE secteur = :s ORDER BY titre ASC");
        $st->execute([':s' => $secteur]);
        return array_map([self::class, 'fromArray'], $st->fetchAll());
    }

    public function getAllSecteurs(): array
    {
        return $this->pdo->query("SELECT DISTINCT secteur FROM metiers ORDER BY secteur ASC")
                         ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM metiers")->fetchColumn();
    }

    public function create(): bool
    {
        $st = $this->pdo->prepare("
            INSERT INTO metiers (titre, description, secteur, salaire_min, salaire_max)
            VALUES (:titre, :description, :secteur, :salaire_min, :salaire_max)
        ");
        $ok = $st->execute([
            ':titre'       => $this->titre,
            ':description' => $this->description,
            ':secteur'     => $this->secteur,
            ':salaire_min' => $this->salaireMin,
            ':salaire_max' => $this->salaireMax,
        ]);
        if ($ok) $this->id = (int)$this->pdo->lastInsertId();
        return $ok;
    }

    public function update(): bool
    {
        $st = $this->pdo->prepare("
            UPDATE metiers SET
                titre=:titre, description=:description, secteur=:secteur,
                salaire_min=:salaire_min, salaire_max=:salaire_max
            WHERE id=:id
        ");
        return $st->execute([
            ':id'          => $this->id,
            ':titre'       => $this->titre,
            ':description' => $this->description,
            ':secteur'     => $this->secteur,
            ':salaire_min' => $this->salaireMin,
            ':salaire_max' => $this->salaireMax,
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM metiers WHERE id = :id");
        return $st->execute([':id' => $id]);
    }

    // ── Récupère les compétences requises pour un métier ─────────────────
    public function getCompetencesRequises(int $metierId): array
    {
        $st = $this->pdo->prepare("
            SELECT * FROM metier_competences WHERE metier_id = :id
        ");
        $st->execute([':id' => $metierId]);
        return $st->fetchAll();
    }

    // ── ALGORITHME DE RECOMMANDATION PAR SCORE ───────────────────────────
    /**
     * Compare les compétences d'un CV avec toutes les fiches métier
     * et retourne un tableau de Metier triés par score DESC.
     *
     * Règle de points par niveau :
     *   Débutant=1  Intermédiaire=2  Avancé=3  Expert=4
     * Un candidat avec un niveau >= requis obtient le max.
     * Un niveau inférieur donne un score partiel.
     */
    public function findMetiersCompatibles(int $cvId): array
    {
        // 1. Compétences du CV (nom => niveau numérique)
        $st = $this->pdo->prepare("SELECT nom_competence, niveau FROM competences WHERE cv_id = :id");
        $st->execute([':id' => $cvId]);
        $cvComps = [];
        foreach ($st->fetchAll() as $row) {
            $cvComps[strtolower(trim($row['nom_competence']))] = $this->niveauToInt($row['niveau']);
        }

        // 2. Tous les métiers avec leurs compétences requises
        $metiers = $this->pdo->query("SELECT * FROM metiers ORDER BY titre ASC")->fetchAll();
        $result  = [];

        foreach ($metiers as $mRow) {
            $reqRows = $this->getCompetencesRequises((int)$mRow['id']);
            if (empty($reqRows)) continue;

            $totalPoints = 0;
            $maxPoints   = 0;

            foreach ($reqRows as $req) {
                $reqNom    = strtolower(trim($req['nom_competence']));
                $reqNiveau = $this->niveauToInt($req['niveau_minimum']);
                $maxPoints += $reqNiveau;

                if (isset($cvComps[$reqNom])) {
                    $candidatNiveau = $cvComps[$reqNom];
                    // Si niveau >= requis : points complets
                    if ($candidatNiveau >= $reqNiveau) {
                        $totalPoints += $reqNiveau;
                    } else {
                        // Partiel : niveau du candidat / niveau requis * points requis
                        $totalPoints += (int)(($candidatNiveau / $reqNiveau) * $reqNiveau);
                    }
                }
                // Si compétence absente : 0 point
            }

            $score = ($maxPoints > 0) ? (int)round(($totalPoints / $maxPoints) * 100) : 0;

            $metier = self::fromArray($mRow);
            $metier->setScore($score);
            $result[] = $metier;
        }

        // 3. Tri par score décroissant
        usort($result, fn($a, $b) => $b->getScore() - $a->getScore());

        return $result;
    }

    // ── Convertit niveau texte → entier ──────────────────────────────────
    public function niveauToInt(string $niveau): int
    {
        return match(strtolower(trim($niveau))) {
            'débutant'       => 1,
            'intermédiaire'  => 2,
            'avancé'         => 3,
            'expert'         => 4,
            default          => 1,
        };
    }
}
