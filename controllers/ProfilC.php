<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Profil.php';

/**
 * ProfilC.php — Contrôleur CRUD Profil
 * PDO + requêtes préparées + validation PHP (sans HTML5)
 */
class ProfilC
{
    // ════════════════════════════════════════════════════════
    //  C — CREATE
    // ════════════════════════════════════════════════════════
    public function addProfil(Profil $p): bool
    {
        if ($this->profilExistePourUtilisateur((int) $p->getIdUtilisateur())) {
            return false;
        }

        $sql = "INSERT INTO profil (bio, photoProfil, ville, pays, langue, idUtilisateur)
                VALUES (:bio, :photoProfil, :ville, :pays, :langue, :idUtilisateur)";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':bio'           => $p->getBio(),
                ':photoProfil'   => $p->getPhotoProfil(),
                ':ville'         => $p->getVille(),
                ':pays'          => $p->getPays(),
                ':langue'        => $p->getLangue(),
                ':idUtilisateur' => $p->getIdUtilisateur(),
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('addProfil: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : tous les profils (avec jointure utilisateur)
    // ════════════════════════════════════════════════════════
    public function listProfils(string $tri = 'recent'): array
    {
        try {
            $orderBy = $this->getOrderByClause($tri);
            $q = Config::getConnexion()->prepare(
                "SELECT p.*, u.nom, u.prenom, u.email, u.role
                 FROM profil p
                 JOIN utilisateur u ON p.idUtilisateur = u.idUtilisateur
                 ORDER BY $orderBy"
            );
            $q->execute();
            return $this->enrichRows($q->fetchAll());
        } catch (PDOException $e) { return []; }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : profil par ID
    // ════════════════════════════════════════════════════════
    public function getById(int $id): ?Profil
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM profil WHERE idProfil = :id");
            $q->execute([':id' => $id]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) { return null; }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : profil par idUtilisateur
    // ════════════════════════════════════════════════════════
    public function getByIdUtilisateur(int $idU): ?Profil
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM profil WHERE idUtilisateur = :id");
            $q->execute([':id' => $idU]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) { return null; }
    }

    // ════════════════════════════════════════════════════════
    //  U — UPDATE
    // ════════════════════════════════════════════════════════
    public function updateProfil(Profil $p, int $id): bool
    {
        $sql = "UPDATE profil SET
                    bio           = :bio,
                    photoProfil   = :photoProfil,
                    ville         = :ville,
                    pays          = :pays,
                    langue        = :langue
                WHERE idProfil = :id";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':bio'         => $p->getBio(),
                ':photoProfil' => $p->getPhotoProfil(),
                ':ville'       => $p->getVille(),
                ':pays'        => $p->getPays(),
                ':langue'      => $p->getLangue(),
                ':id'          => $id,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('updateProfil: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  D — DELETE
    // ════════════════════════════════════════════════════════
    public function deleteProfil(int $id): bool
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("DELETE FROM profil WHERE idProfil = :id");
            $q->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    // ════════════════════════════════════════════════════════
    //  RECHERCHE (jointure utilisateur)
    // ════════════════════════════════════════════════════════
    public function search(string $terme, string $tri = 'recent'): array
    {
        try {
            $orderBy = $this->getOrderByClause($tri);
            $q = Config::getConnexion()->prepare(
                "SELECT p.*, u.nom, u.prenom, u.email, u.role
                 FROM profil p
                 JOIN utilisateur u ON p.idUtilisateur = u.idUtilisateur
                 WHERE u.email LIKE :t
                 ORDER BY $orderBy"
            );
            $q->execute([':t' => '%' . $terme . '%']);
            return $this->enrichRows($q->fetchAll());
        } catch (PDOException $e) { return []; }
    }

    public function getCompletionStats(): array
    {
        $profils = $this->listProfils();
        $count = count($profils);

        if ($count === 0) {
            return [
                'average' => 0,
                'max' => 0,
                'min' => 0,
                'topLabel' => 'Aucun profil',
                'topCity' => 'Aucune ville',
                'topLanguage' => 'Aucune langue',
                'fullCount' => 0,
            ];
        }

        $sum = 0;
        $max = -1;
        $min = 101;
        $topLabel = 'Aucun profil';
        $cityCounts = [];
        $languageCounts = [];
        $fullCount = 0;

        foreach ($profils as $profil) {
            $completion = (int) ($profil['completion'] ?? 0);
            $sum += $completion;

            if ($completion > $max) {
                $max = $completion;
                $topLabel = trim(($profil['prenom'] ?? '') . ' ' . ($profil['nom'] ?? ''));
            }

            if ($completion < $min) {
                $min = $completion;
            }

            if ($completion >= 100) {
                $fullCount++;
            }

            $ville = trim((string) ($profil['ville'] ?? ''));
            if ($ville !== '') {
                $cityCounts[$ville] = ($cityCounts[$ville] ?? 0) + 1;
            }

            $langue = trim((string) ($profil['langue'] ?? ''));
            if ($langue !== '') {
                $languageCounts[$langue] = ($languageCounts[$langue] ?? 0) + 1;
            }
        }

        arsort($cityCounts);
        arsort($languageCounts);

        return [
            'average' => (int) round($sum / $count),
            'max' => max(0, $max),
            'min' => min(100, $min),
            'topLabel' => $topLabel !== '' ? $topLabel : 'Profil anonyme',
            'topCity' => array_key_first($cityCounts) ?? 'Aucune ville',
            'topLanguage' => array_key_first($languageCounts) ?? 'Aucune langue',
            'fullCount' => $fullCount,
        ];
    }

    // ════════════════════════════════════════════════════════
    //  VALIDATION PHP — jamais HTML5
    // ════════════════════════════════════════════════════════
    public function valider(array $d, int $excludeProfilId = 0): array
    {
        $err = [];

        // Bio
        if (!empty($d['bio']) && strlen($d['bio']) > 500)
            $err['bio'] = "La bio ne doit pas dépasser 500 caractères.";

        // Ville
        if (!empty($d['ville']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', trim($d['ville'])))
            $err['ville'] = "La ville doit contenir uniquement des lettres (2–50 caractères).";

        // Pays
        if (!empty($d['pays']) && !preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', trim($d['pays'])))
            $err['pays'] = "Le pays doit contenir uniquement des lettres (2–50 caractères).";

        // Langue
        $languesValides = ['Français','Arabe','Anglais','Espagnol','Allemand','Autre'];
        if (!empty($d['langue']) && !in_array($d['langue'], $languesValides))
            $err['langue'] = "Veuillez sélectionner une langue valide.";

        // idUtilisateur
        if (empty($d['idUtilisateur']) || !is_numeric($d['idUtilisateur']))
            $err['idUtilisateur'] = "Utilisateur associé invalide.";
        elseif ($this->profilExistePourUtilisateur((int) $d['idUtilisateur'], $excludeProfilId))
            $err['idUtilisateur'] = 'Cet utilisateur a deja un profil.';

        return $err;
    }

    // ── Helpers privés ────────────────────────────────────────
    private function rowToObject(array $row): Profil
    {
        return new Profil(
            $row['idProfil'],
            $row['bio'],
            $row['photoProfil'],
            $row['ville'],
            $row['pays'],
            $row['langue'],
            $row['idUtilisateur']
        );
    }

    private function getOrderByClause(string $tri): string
    {
        return match ($tri) {
            'nom_asc' => 'u.nom ASC, u.prenom ASC',
            'nom_desc' => 'u.nom DESC, u.prenom DESC',
            'ville_asc' => 'p.ville ASC, u.nom ASC, u.prenom ASC',
            'ville_desc' => 'p.ville DESC, u.nom ASC, u.prenom ASC',
            default => 'p.idProfil DESC',
        };
    }

    private function profilExistePourUtilisateur(int $idUtilisateur, int $excludeProfilId = 0): bool
    {
        try {
            $q = Config::getConnexion()->prepare(
                "SELECT COUNT(*) FROM profil
                 WHERE idUtilisateur = :idUtilisateur AND idProfil != :idProfil"
            );
            $q->execute([
                ':idUtilisateur' => $idUtilisateur,
                ':idProfil' => $excludeProfilId,
            ]);
            return (int) $q->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function enrichRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['completion'] = $this->calculateCompletion($row);
            $row['completionLabel'] = $this->getCompletionLabel((int) $row['completion']);
        }

        return $rows;
    }

    private function calculateCompletion(array $row): int
    {
        $fields = [
            'bio' => 25,
            'photoProfil' => 20,
            'ville' => 20,
            'pays' => 20,
            'langue' => 15,
        ];

        $score = 0;
        foreach ($fields as $field => $weight) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && $value !== 'default.png') {
                $score += $weight;
            }
        }

        return min(100, $score);
    }

    private function getCompletionLabel(int $completion): string
    {
        return match (true) {
            $completion >= 100 => 'Profil complet',
            $completion >= 70 => 'Tres complet',
            $completion >= 40 => 'En progression',
            default => 'A completer',
        };
    }
}
