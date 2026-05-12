<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Offre.php';
require_once __DIR__ . '/../model/Candidature.php';
require_once __DIR__ . '/CandidatureC.php';

/**
 * OffreC.php — Contrôleur Offre + Algorithme de Matching
 * PDO + requêtes préparées + validation PHP (sans HTML5)
 */
class OffreC
{
    // ════════════════════════════════════════════════════════
    //  C — CREATE
    // ════════════════════════════════════════════════════════
    public function addOffre(Offre $o): bool
    {
        $sql = "INSERT INTO offre (titre, competences, localisation)
                VALUES (:titre, :competences, :localisation)";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':titre' => $o->getTitre(),
                ':competences' => $o->getCompetences(),
                ':localisation' => $o->getLocalisation(),
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('addOffre: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : liste complète
    // ════════════════════════════════════════════════════════
    public function listOffres(string $search = '', string $sort = 'idOffre DESC'): array
    {
        try {
            $sql = "SELECT * FROM offre";
            $params = [];
            if (!empty($search)) {
                $sql .= " WHERE titre LIKE :search OR competences LIKE :search OR localisation LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
            $sql .= " ORDER BY " . $sort;
            $q = Config::getConnexion()->prepare($sql);
            $q->execute($params);
            return $q->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : par ID
    // ════════════════════════════════════════════════════════
    public function getById(int $id): ?Offre
    {
        try {
            $q = Config::getConnexion()
                ->prepare("SELECT * FROM offre WHERE idOffre = :id");
            $q->execute([':id' => $id]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // ════════════════════════════════════════════════════════
    //  MATCHING — Calcul du score entre candidature et offre
    // ════════════════════════════════════════════════════════

    /**
     * Calcule le score de matching.
     * Score = (skills matchés / skills requis) × 100 + bonus localisation
     */
    public function calculMatch(string $skillsUser, string $skillsOffre, string $locUser, string $locOffre): array
    {
        // Étape 1 : Séparer les skills par virgule et normaliser
        $tabUser = $this->parseSkills($skillsUser);
        $tabOffre = $this->parseSkills($skillsOffre);

        // Étape 2 : Compter les skills en commun
        $matched = array_intersect($tabUser, $tabOffre);
        $totalRequired = count($tabOffre);

        if ($totalRequired === 0) {
            return ['score' => 0, 'matched' => [], 'total' => 0, 'locMatch' => false];
        }

        // Étape 3 : Score = (matchés / requis) × 100
        $score = (count($matched) / $totalRequired) * 100;

        // Étape 4 : Bonus +10 si même localisation
        $locMatch = (strtolower(trim($locUser)) !== ''
            && strtolower(trim($locUser)) === strtolower(trim($locOffre)));
        if ($locMatch) {
            $score += 10;
        }

        // Étape 5 : Plafonner à 100
        $score = min(100, round($score, 1));

        return [
            'score' => $score,
            'matched' => array_values($matched),
            'total' => $totalRequired,
            'locMatch' => $locMatch,
        ];
    }

    /**
     * Retourne toutes les offres avec score de matching, triées DESC.
     */
    public function getMatchedOffres(int $idUtilisateur): array
    {
        // Récupérer la candidature
        $ctrlC = new CandidatureC();
        $candidature = $ctrlC->getByIdUtilisateur($idUtilisateur);

        if (!$candidature) {
            return [];
        }

        // Comparer avec chaque offre
        $offres = $this->listOffres();
        $results = [];

        foreach ($offres as $row) {
            $match = $this->calculMatch(
                $candidature->getSkills(),
                $row['competences'],
                $candidature->getLocalisation(),
                $row['localisation']
            );

            $results[] = [
                'offre' => $row,
                'score' => $match['score'],
                'matched' => $match['matched'],
                'total' => $match['total'],
                'locMatch' => $match['locMatch'],
            ];
        }

        // Trier par score décroissant
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }

    // ════════════════════════════════════════════════════════
    //  D — DELETE
    // ════════════════════════════════════════════════════════
    public function deleteOffre(int $id): bool
    {
        try {
            $q = Config::getConnexion()
                ->prepare("DELETE FROM offre WHERE idOffre = :id");
            $q->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    // ════════════════════════════════════════════════════════
    //  U — UPDATE
    // ════════════════════════════════════════════════════════
    public function updateOffre(Offre $o): bool
    {
        $id = (int)($o->getIdOffre() ?? 0);
        if ($id <= 0) return false;

        $sql = "UPDATE offre
                SET titre = :titre,
                    competences = :competences,
                    localisation = :localisation
                WHERE idOffre = :id";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':titre' => $o->getTitre(),
                ':competences' => $o->getCompetences(),
                ':localisation' => $o->getLocalisation(),
                ':id' => $id,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('updateOffre: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  VALIDATION PHP — jamais HTML5
    // ════════════════════════════════════════════════════════
    public function valider(array $d): array
    {
        $err = [];

        if (empty(trim($d['titre'] ?? '')))
            $err['titre'] = "Le titre est obligatoire.";
        elseif (mb_strlen(trim($d['titre'])) > 100)
            $err['titre'] = "Le titre ne doit pas dépasser 100 caractères.";

        if (empty(trim($d['competences'] ?? '')))
            $err['competences'] = "Les compétences sont obligatoires.";

        if (empty(trim($d['localisation'] ?? '')))
            $err['localisation'] = "La localisation est obligatoire.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,100}$/u', trim($d['localisation'])))
            $err['localisation'] = "La localisation doit contenir uniquement des lettres (2–100 caractères).";

        return $err;
    }

    // ── Helpers privés ───────────────────────────────────────

    /**
     * Sépare une chaîne par virgule → tableau normalisé (trim + lowercase).
     */
    private function parseSkills(string $str): array
    {
        if (empty(trim($str)))
            return [];

        $skills = explode(',', $str);
        $result = [];
        foreach ($skills as $s) {
            $result[] = strtolower(trim($s));
        }
        return $result;
    }

    private function rowToObject(array $row): Offre
    {
        return new Offre(
            $row['idOffre'],
            $row['titre'],
            $row['competences'],
            $row['localisation']
        );
    }
}
