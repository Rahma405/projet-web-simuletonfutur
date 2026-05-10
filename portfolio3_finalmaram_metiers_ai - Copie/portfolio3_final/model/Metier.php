<?php
require_once __DIR__ . '/../config/config.php';

class Metier
{
    private ?int $id = null;
    private string $titre = '';
    private string $description = '';
    private string $secteur = '';
    private int $salaireMin = 0;
    private int $salaireMax = 0;
    private int $score = 0;
    private int $titleScore = 0;
    private int $nbCompatible = 0;
    private array $matchedCompetences = [];
    private array $missingCompetences = [];

    private PDO $pdo;

    public function __construct(array $data = [])
    {
        $this->pdo = Config::getConnexion();
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getSecteur(): string { return $this->secteur; }
    public function getSalaireMin(): int { return $this->salaireMin; }
    public function getSalaireMax(): int { return $this->salaireMax; }
    public function getScore(): int { return $this->score; }
    public function getTitleScore(): int { return $this->titleScore; }
    public function getNbCompatible(): int { return $this->nbCompatible; }
    public function getMatchedCompetences(): array { return $this->matchedCompetences; }
    public function getMissingCompetences(): array { return $this->missingCompetences; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setTitre(string $titre): void { $this->titre = trim($titre); }
    public function setDescription(string $description): void { $this->description = trim($description); }
    public function setSecteur(string $secteur): void { $this->secteur = trim($secteur); }
    public function setSalaireMin(int $salaireMin): void { $this->salaireMin = max(0, $salaireMin); }
    public function setSalaireMax(int $salaireMax): void { $this->salaireMax = max(0, $salaireMax); }
    public function setScore(int $score): void { $this->score = max(0, min(100, $score)); }
    public function setTitleScore(int $titleScore): void { $this->titleScore = max(0, min(100, $titleScore)); }
    public function setNbCompatible(int $nbCompatible): void { $this->nbCompatible = max(0, $nbCompatible); }
    public function setMatchedCompetences(array $matchedCompetences): void { $this->matchedCompetences = $matchedCompetences; }
    public function setMissingCompetences(array $missingCompetences): void { $this->missingCompetences = $missingCompetences; }

    public function hydrate(array $data): void
    {
        $this->setId(isset($data['id']) ? (int)$data['id'] : null);
        $this->setTitre((string)($data['titre'] ?? ''));
        $this->setDescription((string)($data['description'] ?? ''));
        $this->setSecteur((string)($data['secteur'] ?? ''));
        $this->setSalaireMin((int)($data['salaire_min'] ?? 0));
        $this->setSalaireMax((int)($data['salaire_max'] ?? 0));
        $this->setScore((int)($data['score'] ?? 0));
        $this->setTitleScore((int)($data['title_score'] ?? 0));
        $this->setNbCompatible((int)($data['nb_compatible'] ?? 0));
        $this->setMatchedCompetences($data['matched_competences'] ?? []);
        $this->setMissingCompetences($data['missing_competences'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'secteur' => $this->secteur,
            'salaire_min' => $this->salaireMin,
            'salaire_max' => $this->salaireMax,
            'score' => $this->score,
            'title_score' => $this->titleScore,
            'nb_compatible' => $this->nbCompatible,
            'matched_competences' => $this->matchedCompetences,
            'missing_competences' => $this->missingCompetences,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /** @return Metier[] */
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

    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM metiers")->fetchColumn();
    }

    public function getAllSecteurs(): array
    {
        return $this->pdo->query("SELECT DISTINCT secteur FROM metiers ORDER BY secteur ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCompetencesRequises(int $metierId): array
    {
        $st = $this->pdo->prepare("SELECT * FROM metier_competences WHERE metier_id = :id");
        $st->execute([':id' => $metierId]);
        return $st->fetchAll();
    }

    /** @return Metier[] */
    public function findMetiersCompatibles(int $cvId): array
    {
        $cvSt = $this->pdo->prepare("SELECT titre_poste FROM cv WHERE id = :id");
        $cvSt->execute([':id' => $cvId]);
        $cv = $cvSt->fetch() ?: ['titre_poste' => ''];
        $titreCv = (string)($cv['titre_poste'] ?? '');

        $st = $this->pdo->prepare("SELECT nom_competence, niveau FROM competences WHERE cv_id = :id");
        $st->execute([':id' => $cvId]);

        $cvComps = [];
        foreach ($st->fetchAll() as $row) {
            $cvComps[$this->normalizeCompetenceKey($row['nom_competence'])] = $this->niveauToInt($row['niveau']);
        }

        $result = [];
        foreach ($this->findAll() as $metier) {
            $reqRows = $this->getCompetencesRequises((int)$metier->getId());
            if (empty($reqRows)) {
                continue;
            }

            $totalPoints = 0;
            $maxPoints = 0;
            $matched = [];
            $missing = [];

            foreach ($reqRows as $req) {
                $reqKey = $this->normalizeCompetenceKey($req['nom_competence']);
                $reqLevel = $this->niveauToInt($req['niveau_minimum']);
                $maxPoints += $reqLevel;

                if (isset($cvComps[$reqKey])) {
                    $candidateLevel = $cvComps[$reqKey];
                    $totalPoints += min($candidateLevel, $reqLevel);
                    $matched[] = $req['nom_competence'];
                } else {
                    $missing[] = $req['nom_competence'];
                }
            }

            $skillScore = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
            $coverageScore = count($reqRows) > 0 ? (count($matched) / count($reqRows)) * 100 : 0;
            $titleScore = $this->textSimilarity($titreCv, $metier->getTitre());
            $sectorScore = $this->cvHasCompetenceInSector($cvId, $metier->getSecteur()) ? 75 : 0;

            $score = (int)round(
                ($skillScore * 0.60)
                + ($coverageScore * 0.20)
                + ($titleScore * 0.15)
                + ($sectorScore * 0.05)
            );

            if (count($matched) === 0) {
                $score = min($score, 30);
            }
            if (count($reqRows) <= 1) {
                $score = min($score, $titleScore >= 80 ? 90 : 68);
            }
            $score = $skillScore < 100 ? min($score, 92) : min($score, 95);

            $metier->setScore($score);
            $metier->setMatchedCompetences($matched);
            $metier->setMissingCompetences($missing);
            $metier->setTitleScore((int)round($titleScore));
            $result[] = $metier;
        }

        usort($result, fn(self $a, self $b) => $b->getScore() <=> $a->getScore());
        return $result;
    }

    public function findMeilleurMetierCompatible(int $cvId, int $seuil = 70): ?self
    {
        $metiers = $this->findMetiersCompatibles($cvId);
        if (!empty($metiers) && $metiers[0]->getScore() >= $seuil) {
            return $metiers[0];
        }
        return null;
    }

    public function statsNiveauxCompetences(): array
    {
        return $this->pdo->query("
            SELECT niveau, COUNT(*) AS total
            FROM competences
            GROUP BY niveau
        ")->fetchAll();
    }

    public function statsCvParSecteur(int $seuil = 70): array
    {
        $cvIds = $this->pdo->query("SELECT id FROM cv")->fetchAll(PDO::FETCH_COLUMN);
        $secteurs = [];

        foreach ($cvIds as $cvId) {
            foreach ($this->findMetiersCompatibles((int)$cvId) as $metier) {
                if ($metier->getScore() >= $seuil) {
                    $secteur = $metier->getSecteur() ?: 'Non classe';
                    $secteurs[$secteur] = ($secteurs[$secteur] ?? 0) + 1;
                    break;
                }
            }
        }

        arsort($secteurs);
        $rows = [];
        foreach ($secteurs as $secteur => $total) {
            $rows[] = ['secteur' => $secteur, 'total' => $total];
        }
        return $rows;
    }

    /** @return Metier[] */
    public function statsDashboard(int $seuil = 70): array
    {
        $metiers = $this->findAll();
        $cvIds = $this->pdo->query("SELECT id FROM cv")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($metiers as $metier) {
            $nbCompatible = 0;
            foreach ($cvIds as $cvId) {
                foreach ($this->findMetiersCompatibles((int)$cvId) as $reco) {
                    if ($reco->getId() === $metier->getId() && $reco->getScore() >= $seuil) {
                        $nbCompatible++;
                        break;
                    }
                }
            }
            $metier->setNbCompatible($nbCompatible);
        }

        usort($metiers, fn(self $a, self $b) => $b->getNbCompatible() <=> $a->getNbCompatible());
        return $metiers;
    }

    private function niveauToInt(string $niveau): int
    {
        $niveau = strtolower(trim($niveau));
        $niveau = strtr($niveau, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ã©' => 'e', 'Ã¨' => 'e', 'Ãª' => 'e', 'Ã«' => 'e',
            'à' => 'a', 'â' => 'a', 'Ã ' => 'a', 'Ã¢' => 'a',
            'î' => 'i', 'ï' => 'i', 'Ã®' => 'i', 'Ã¯' => 'i',
            'ô' => 'o', 'Ã´' => 'o',
            'ù' => 'u', 'û' => 'u', 'Ã¹' => 'u', 'Ã»' => 'u',
            'ç' => 'c', 'Ã§' => 'c',
        ]);

        return match ($niveau) {
            'debutant', 'a1', 'a2' => 1,
            'intermediaire', 'b1', 'b2' => 2,
            'avance', 'c1' => 3,
            'expert', 'c2' => 4,
            default => 1,
        };
    }

    private function normalizeCompetenceKey(string $nom): string
    {
        $nom = strtolower(trim($nom));
        $nom = strtr($nom, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ã©' => 'e', 'Ã¨' => 'e', 'Ãª' => 'e', 'Ã«' => 'e',
            'à' => 'a', 'â' => 'a', 'Ã ' => 'a', 'Ã¢' => 'a',
            'î' => 'i', 'ï' => 'i', 'Ã®' => 'i', 'Ã¯' => 'i',
            'ô' => 'o', 'Ã´' => 'o',
            'ù' => 'u', 'û' => 'u', 'Ã¹' => 'u', 'Ã»' => 'u',
            'ç' => 'c', 'Ã§' => 'c',
        ]);
        $nom = preg_replace('/[^a-z0-9]+/', ' ', $nom);
        return trim(preg_replace('/\s+/', ' ', $nom));
    }

    private function textSimilarity(string $a, string $b): float
    {
        $aTokens = $this->tokens($a);
        $bTokens = $this->tokens($b);
        if (!$aTokens || !$bTokens) {
            return 0;
        }

        $common = array_intersect($aTokens, $bTokens);
        $union = array_unique(array_merge($aTokens, $bTokens));
        $jaccard = count($union) > 0 ? count($common) / count($union) : 0;

        similar_text(implode(' ', $aTokens), implode(' ', $bTokens), $similar);
        return max($jaccard * 100, $similar);
    }

    private function tokens(string $text): array
    {
        $text = $this->normalizeCompetenceKey($text);
        $stopWords = ['de', 'du', 'des', 'le', 'la', 'les', 'un', 'une', 'et', 'en', 'ai'];
        $tokens = array_filter(explode(' ', $text), fn($token) => strlen($token) > 2 && !in_array($token, $stopWords, true));
        return array_values(array_unique($tokens));
    }

    private function cvHasCompetenceInSector(int $cvId, string $secteur): bool
    {
        if (trim($secteur) === '') {
            return false;
        }

        $st = $this->pdo->prepare("SELECT COUNT(*) FROM competences WHERE cv_id = :id AND LOWER(categorie) LIKE :secteur");
        $st->execute([
            ':id' => $cvId,
            ':secteur' => '%' . strtolower($secteur) . '%',
        ]);

        return (int)$st->fetchColumn() > 0;
    }
}
