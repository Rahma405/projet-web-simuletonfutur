<?php
require_once __DIR__ . '/../config/config.php';

class EvolutionCarriere
{
    // ── Propriétés privées ────────────────────────────────────────────────
    private ?int   $id              = null;
    private int    $metierId        = 0;
    private int    $metierSuivantId = 0;
    private int    $anneesRequises  = 2;
    private string $competencesGap  = '';

    // Champs enrichis (jointure avec la table metiers)
    private string $titreCourant    = '';
    private string $titreSuivant    = '';
    private string $secteurSuivant  = '';
    private int    $salaireSuivantMin = 0;
    private int    $salaireSuivantMax = 0;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // ── Getters ───────────────────────────────────────────────────────────
    public function getId(): ?int              { return $this->id; }
    public function getMetierId(): int         { return $this->metierId; }
    public function getMetierSuivantId(): int  { return $this->metierSuivantId; }
    public function getAnneesRequises(): int   { return $this->anneesRequises; }
    public function getCompetencesGap(): string{ return $this->competencesGap; }
    public function getTitreCourant(): string  { return $this->titreCourant; }
    public function getTitreSuivant(): string  { return $this->titreSuivant; }
    public function getSecteurSuivant(): string{ return $this->secteurSuivant; }
    public function getSalaireSuivantMin(): int{ return $this->salaireSuivantMin; }
    public function getSalaireSuivantMax(): int{ return $this->salaireSuivantMax; }

    // Retourne les compétences gap sous forme de tableau
    public function getCompetencesGapArray(): array
    {
        if (empty(trim($this->competencesGap))) return [];
        return array_map('trim', explode(',', $this->competencesGap));
    }

    // ── Setters ───────────────────────────────────────────────────────────
    public function setId(?int $v): void               { $this->id = $v; }
    public function setMetierId(int $v): void          { $this->metierId = $v; }
    public function setMetierSuivantId(int $v): void   { $this->metierSuivantId = $v; }
    public function setAnneesRequises(int $v): void    { $this->anneesRequises = $v; }
    public function setCompetencesGap(string $v): void { $this->competencesGap = trim($v); }
    public function setTitreCourant(string $v): void   { $this->titreCourant = trim($v); }
    public function setTitreSuivant(string $v): void   { $this->titreSuivant = trim($v); }
    public function setSecteurSuivant(string $v): void { $this->secteurSuivant = trim($v); }
    public function setSalaireSuivantMin(int $v): void { $this->salaireSuivantMin = $v; }
    public function setSalaireSuivantMax(int $v): void { $this->salaireSuivantMax = $v; }

    // ── Hydratation ───────────────────────────────────────────────────────
    public static function fromArray(array $row): self
    {
        $obj = new self();
        $obj->setId((int)($row['id'] ?? 0));
        $obj->setMetierId((int)($row['metier_id'] ?? 0));
        $obj->setMetierSuivantId((int)($row['metier_suivant_id'] ?? 0));
        $obj->setAnneesRequises((int)($row['annees_requises'] ?? 2));
        $obj->setCompetencesGap($row['competences_gap'] ?? '');
        $obj->setTitreCourant($row['titre_courant']     ?? '');
        $obj->setTitreSuivant($row['titre_suivant']     ?? '');
        $obj->setSecteurSuivant($row['secteur_suivant'] ?? '');
        $obj->setSalaireSuivantMin((int)($row['salaire_suivant_min'] ?? 0));
        $obj->setSalaireSuivantMax((int)($row['salaire_suivant_max'] ?? 0));
        return $obj;
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'metier_id'        => $this->metierId,
            'metier_suivant_id'=> $this->metierSuivantId,
            'annees_requises'  => $this->anneesRequises,
            'competences_gap'  => $this->competencesGap,
            'titre_courant'    => $this->titreCourant,
            'titre_suivant'    => $this->titreSuivant,
        ];
    }

    // ── Méthodes principales ──────────────────────────────────────────────

    /**
     * Trouve le métier le plus proche du titre_poste du CV
     * en cherchant dans la table metiers par mots-clés.
     */
    public function findMetierByTitre(string $titrePoste): ?array
    {
        $mots = explode(' ', strtolower(trim($titrePoste)));
        $rows = $this->pdo->query("SELECT * FROM metiers")->fetchAll();

        $best      = null;
        $bestScore = 0;

        foreach ($rows as $row) {
            $titre = strtolower($row['titre']);
            $score = 0;
            foreach ($mots as $mot) {
                if (strlen($mot) > 2 && str_contains($titre, $mot)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $row;
            }
        }
        return ($bestScore > 0) ? $best : null;
    }

    /**
     * Retourne la chaîne d'évolution complète à partir d'un metier_id.
     * Exemple : Junior → Senior → Lead → Architecte
     */
    public function getChainEvolution(int $metierId): array
    {
        $chain   = [];
        $current = $metierId;
        $visited = [];

        while ($current && !in_array($current, $visited)) {
            $visited[] = $current;

            $st = $this->pdo->prepare("
                SELECT ec.*,
                       m1.titre  AS titre_courant,
                       m2.titre  AS titre_suivant,
                       m2.secteur AS secteur_suivant,
                       m2.salaire_min AS salaire_suivant_min,
                       m2.salaire_max AS salaire_suivant_max
                FROM evolution_carriere ec
                JOIN metiers m1 ON ec.metier_id = m1.id
                JOIN metiers m2 ON ec.metier_suivant_id = m2.id
                WHERE ec.metier_id = :id
                LIMIT 1
            ");
            $st->execute([':id' => $current]);
            $row = $st->fetch();

            if (!$row) break;

            $chain[]  = self::fromArray($row);
            $current  = (int)$row['metier_suivant_id'];
        }

        return $chain;
    }

    /**
     * Retourne le métier de départ (premier de la chaîne) à partir de son id.
     */
    public function getMetierById(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM metiers WHERE id = :id");
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }
}
