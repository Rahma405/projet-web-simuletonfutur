<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Profil.php';

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
    public function listProfils(string $search = '', string $sort = 'p.idProfil DESC'): array
    {
        try {
            $sql = "SELECT p.*, u.nom, u.prenom, u.email, u.role
                    FROM profil p
                    JOIN utilisateur u ON p.idUtilisateur = u.idUtilisateur";
            $params = [];
            if (!empty($search)) {
                $sql .= " WHERE u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR p.bio LIKE :search OR p.ville LIKE :search OR p.pays LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
            $sql .= " ORDER BY " . $sort;
            $q = Config::getConnexion()->prepare($sql);
            $q->execute($params);
            return $q->fetchAll();
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
    public function search(string $terme): array
    {
        try {
            $q = Config::getConnexion()->prepare(
                "SELECT p.*, u.nom, u.prenom, u.email, u.role
                 FROM profil p
                 JOIN utilisateur u ON p.idUtilisateur = u.idUtilisateur
                 WHERE u.nom LIKE :t OR u.prenom LIKE :t
                    OR p.ville LIKE :t OR p.pays LIKE :t
                 ORDER BY p.idProfil DESC"
            );
            $q->execute([':t' => '%' . $terme . '%']);
            return $q->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // ════════════════════════════════════════════════════════
    //  VALIDATION PHP — jamais HTML5
    // ════════════════════════════════════════════════════════
    public function valider(array $d): array
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
}
