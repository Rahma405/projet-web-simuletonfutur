<?php
require_once __DIR__ . '/../config/config.php';

class EvolutionCarriere
{
    private ?int $id = null;
    private int $metierId = 0;
    private int $metierSuivantId = 0;
    private int $anneesRequises = 0;
    private string $competencesGap = '';
    private string $titreCourant = '';
    private string $titreSuivant = '';
    private string $secteurSuivant = '';
    private int $salaireSuivantMin = 0;
    private int $salaireSuivantMax = 0;

    private PDO $pdo;

    public function __construct(array $data = [])
    {
        $this->pdo = Config::getConnexion();
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getMetierId(): int { return $this->metierId; }
    public function getMetierSuivantId(): int { return $this->metierSuivantId; }
    public function getAnneesRequises(): int { return $this->anneesRequises; }
    public function getCompetencesGap(): string { return $this->competencesGap; }
    public function getTitreCourant(): string { return $this->titreCourant; }
    public function getTitreSuivant(): string { return $this->titreSuivant; }
    public function getSecteurSuivant(): string { return $this->secteurSuivant; }
    public function getSalaireSuivantMin(): int { return $this->salaireSuivantMin; }
    public function getSalaireSuivantMax(): int { return $this->salaireSuivantMax; }

    public function getCompetencesGapArray(): array
    {
        return array_filter(array_map('trim', explode(',', $this->competencesGap)));
    }

    public function setId(?int $id): void { $this->id = $id; }
    public function setMetierId(int $metierId): void { $this->metierId = $metierId; }
    public function setMetierSuivantId(int $metierSuivantId): void { $this->metierSuivantId = $metierSuivantId; }
    public function setAnneesRequises(int $anneesRequises): void { $this->anneesRequises = max(0, $anneesRequises); }
    public function setCompetencesGap(string $competencesGap): void { $this->competencesGap = trim($competencesGap); }
    public function setTitreCourant(string $titreCourant): void { $this->titreCourant = trim($titreCourant); }
    public function setTitreSuivant(string $titreSuivant): void { $this->titreSuivant = trim($titreSuivant); }
    public function setSecteurSuivant(string $secteurSuivant): void { $this->secteurSuivant = trim($secteurSuivant); }
    public function setSalaireSuivantMin(int $salaireSuivantMin): void { $this->salaireSuivantMin = max(0, $salaireSuivantMin); }
    public function setSalaireSuivantMax(int $salaireSuivantMax): void { $this->salaireSuivantMax = max(0, $salaireSuivantMax); }

    public function hydrate(array $data): void
    {
        $this->setId(isset($data['id']) ? (int)$data['id'] : null);
        $this->setMetierId((int)($data['metier_id'] ?? 0));
        $this->setMetierSuivantId((int)($data['metier_suivant_id'] ?? 0));
        $this->setAnneesRequises((int)($data['annees_requises'] ?? 0));
        $this->setCompetencesGap((string)($data['competences_gap'] ?? ''));
        $this->setTitreCourant((string)($data['titre_courant'] ?? ''));
        $this->setTitreSuivant((string)($data['titre_suivant'] ?? ''));
        $this->setSecteurSuivant((string)($data['secteur_suivant'] ?? ''));
        $this->setSalaireSuivantMin((int)($data['salaire_suivant_min'] ?? 0));
        $this->setSalaireSuivantMax((int)($data['salaire_suivant_max'] ?? 0));
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /** @return EvolutionCarriere[] */
    public function getSmartChainEvolution(int $metierId, int $cvId): array
    {
        $chain = $this->getChainEvolution($metierId);
        if (!empty($chain)) {
            foreach ($chain as $step) {
                $step->setCompetencesGap(implode(',', $this->filterMissingCompetences($cvId, $step->getCompetencesGapArray())));
            }
            return $chain;
        }

        return $this->buildGeneratedChain($metierId, $cvId);
    }

    /** @return EvolutionCarriere[] */
    public function getChainEvolution(int $metierId): array
    {
        $chain = [];
        $current = $metierId;
        $visited = [];

        while ($current && !in_array($current, $visited, true)) {
            $visited[] = $current;
            $st = $this->pdo->prepare("
                SELECT ec.*,
                       m1.titre AS titre_courant,
                       m2.titre AS titre_suivant,
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
            if (!$row) {
                break;
            }

            $chain[] = self::fromArray($row);
            $current = (int)$row['metier_suivant_id'];
        }

        return $chain;
    }

    /** @return EvolutionCarriere[] */
    private function buildGeneratedChain(int $metierId, int $cvId): array
    {
        $metier = $this->getMetierRow($metierId);
        if (!$metier) {
            return [];
        }

        $profiles = $this->fallbackProfiles((string)$metier['titre'], (string)$metier['secteur']);
        $steps = [];
        foreach ($profiles as $idx => $profile) {
            $missing = $this->filterMissingCompetences($cvId, $profile['competences']);
            $steps[] = self::fromArray([
                'id' => 0,
                'metier_id' => $idx === 0 ? $metierId : 0,
                'metier_suivant_id' => 0,
                'annees_requises' => $profile['annees'],
                'competences_gap' => implode(',', $missing),
                'titre_courant' => $idx === 0 ? (string)$metier['titre'] : $profiles[$idx - 1]['titre'],
                'titre_suivant' => $profile['titre'],
                'secteur_suivant' => (string)$metier['secteur'],
                'salaire_suivant_min' => (int)$profile['salaire_min'],
                'salaire_suivant_max' => (int)$profile['salaire_max'],
            ]);
        }

        return $steps;
    }

    private function getMetierRow(int $metierId): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM metiers WHERE id = :id");
        $st->execute([':id' => $metierId]);
        return $st->fetch();
    }

    private function fallbackProfiles(string $titre, string $secteur): array
    {
        $titreKey = $this->normalize($titre);
        $secteurKey = $this->normalize($secteur);

        if (str_contains($titreKey, 'graphiste') || str_contains($titreKey, 'designer') || str_contains($secteurKey, 'design')) {
            return [
                [
                    'titre' => str_contains($titreKey, 'graphiste') ? 'Graphiste Senior / Directeur Artistique' : 'UX/UI Designer Senior',
                    'annees' => 2,
                    'competences' => ['Figma', 'Adobe XD', 'UI/UX', 'Direction artistique', 'Portfolio professionnel'],
                    'salaire_min' => 2200,
                    'salaire_max' => 3800,
                ],
                [
                    'titre' => 'Lead Designer',
                    'annees' => 3,
                    'competences' => ['Leadership', 'Design system', 'Strategie produit', 'Mentoring'],
                    'salaire_min' => 3500,
                    'salaire_max' => 5500,
                ],
            ];
        }

        if (str_contains($titreKey, 'front') || str_contains($titreKey, 'react')) {
            return [
                [
                    'titre' => 'Developpeur Frontend Senior',
                    'annees' => 2,
                    'competences' => ['React', 'TypeScript', 'Testing', 'Architecture Frontend'],
                    'salaire_min' => 2500,
                    'salaire_max' => 3800,
                ],
                [
                    'titre' => 'Lead Frontend',
                    'annees' => 3,
                    'competences' => ['Mentoring', 'Design system', 'Performance Web', 'Leadership'],
                    'salaire_min' => 3800,
                    'salaire_max' => 5500,
                ],
            ];
        }

        if (str_contains($titreKey, 'php') || str_contains($titreKey, 'backend') || str_contains($titreKey, 'develop')) {
            return [
                [
                    'titre' => 'Developpeur Backend Senior',
                    'annees' => 2,
                    'competences' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
                    'salaire_min' => 2500,
                    'salaire_max' => 4000,
                ],
                [
                    'titre' => 'Lead Developpeur Backend',
                    'annees' => 3,
                    'competences' => ['Architecture logicielle', 'CI/CD', 'Leadership', 'API Security'],
                    'salaire_min' => 4000,
                    'salaire_max' => 6000,
                ],
            ];
        }

        if (str_contains($secteurKey, 'langue')) {
            return [
                [
                    'titre' => 'Traducteur Technique',
                    'annees' => 2,
                    'competences' => ['Anglais', 'Francais', 'Redaction', 'Terminologie technique'],
                    'salaire_min' => 1200,
                    'salaire_max' => 2600,
                ],
                [
                    'titre' => 'Content Manager International',
                    'annees' => 2,
                    'competences' => ['SEO international', 'Strategie contenu', 'Communication', 'Anglais avance'],
                    'salaire_min' => 1800,
                    'salaire_max' => 3500,
                ],
            ];
        }

        return [
            [
                'titre' => $titre . ' Senior',
                'annees' => 2,
                'competences' => ['Communication', 'Gestion de projet', 'Expertise metier'],
                'salaire_min' => 2200,
                'salaire_max' => 3800,
            ],
            [
                'titre' => 'Lead ' . $titre,
                'annees' => 3,
                'competences' => ['Leadership', 'Mentoring', 'Strategie', 'Presentation professionnelle'],
                'salaire_min' => 3500,
                'salaire_max' => 5500,
            ],
        ];
    }

    private function filterMissingCompetences(int $cvId, array $required): array
    {
        $owned = $this->getCvCompetenceKeys($cvId);
        $missing = [];

        foreach ($required as $competence) {
            $key = $this->normalize($competence);
            if ($key === '') {
                continue;
            }
            $hasCompetence = false;
            foreach ($owned as $ownedKey) {
                if ($ownedKey === $key || str_contains($ownedKey, $key) || str_contains($key, $ownedKey)) {
                    $hasCompetence = true;
                    break;
                }
            }
            if (!$hasCompetence) {
                $missing[] = $competence;
            }
        }

        return array_values(array_unique($missing));
    }

    private function getCvCompetenceKeys(int $cvId): array
    {
        $st = $this->pdo->prepare("SELECT nom_competence FROM competences WHERE cv_id = :id");
        $st->execute([':id' => $cvId]);
        return array_map(fn($name) => $this->normalize((string)$name), $st->fetchAll(PDO::FETCH_COLUMN));
    }

    private function normalize(string $text): string
    {
        $text = strtolower(trim($text));
        $text = strtr($text, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ã©' => 'e', 'Ã¨' => 'e', 'Ãª' => 'e', 'Ã«' => 'e',
            'à' => 'a', 'â' => 'a', 'Ã ' => 'a', 'Ã¢' => 'a',
            'î' => 'i', 'ï' => 'i', 'Ã®' => 'i', 'Ã¯' => 'i',
            'ô' => 'o', 'Ã´' => 'o',
            'ù' => 'u', 'û' => 'u', 'Ã¹' => 'u', 'Ã»' => 'u',
            'ç' => 'c', 'Ã§' => 'c',
        ]);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
