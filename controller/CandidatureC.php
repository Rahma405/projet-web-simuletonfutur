<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Candidature.php';

/**
 * CandidatureC.php — Contrôleur CRUD Candidature
 * PDO + requêtes préparées + validation PHP (sans HTML5)
 */
class CandidatureC
{
    // ════════════════════════════════════════════════════════
    //  C — CREATE
    // ════════════════════════════════════════════════════════
    public function addCandidature(Candidature $c): bool
    {
        $sql = "INSERT INTO candidature (idUtilisateur, skills, cv, localisation)
                VALUES (:idUtilisateur, :skills, :cv, :localisation)";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':idUtilisateur' => $c->getIdUtilisateur(),
                ':skills' => $c->getSkills(),
                ':cv' => $c->getCv(),
                ':localisation' => $c->getLocalisation(),
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('addCandidature: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : par idUtilisateur
    // ════════════════════════════════════════════════════════
    public function getByIdUtilisateur(int $idU): ?Candidature
    {
        try {
            $q = Config::getConnexion()
                ->prepare("SELECT * FROM candidature WHERE idUtilisateur = :id");
            $q->execute([':id' => $idU]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // ════════════════════════════════════════════════════════
    //  U — UPDATE
    // ════════════════════════════════════════════════════════
    public function updateCandidature(Candidature $c, int $id): bool
    {
        $sql = "UPDATE candidature SET
                    skills       = :skills,
                    cv           = :cv,
                    localisation = :localisation
                WHERE idCandidature = :id";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':skills' => $c->getSkills(),
                ':cv' => $c->getCv(),
                ':localisation' => $c->getLocalisation(),
                ':id' => $id,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('updateCandidature: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  D — DELETE
    // ════════════════════════════════════════════════════════
    public function deleteCandidature(int $id): bool
    {
        try {
            $q = Config::getConnexion()
                ->prepare("DELETE FROM candidature WHERE idCandidature = :id");
            $q->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : toutes les candidatures (avec infos utilisateur)
    // ════════════════════════════════════════════════════════
    public function listAll(): array
    {
        try {
            $q = Config::getConnexion()->prepare(
                "SELECT c.*, u.nom, u.prenom, u.email
                 FROM candidature c
                 JOIN utilisateur u ON c.idUtilisateur = u.idUtilisateur
                 ORDER BY c.idCandidature DESC"
            );
            $q->execute();
            return $q->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    // ════════════════════════════════════════════════════════
    //  VALIDATION PHP — jamais HTML5
    // ════════════════════════════════════════════════════════
    public function valider(array $d): array
    {
        $err = [];

        // Skills
        if (empty(trim($d['skills'] ?? '')))
            $err['skills'] = "Les compétences sont obligatoires.";

        // Localisation
        if (empty(trim($d['localisation'] ?? '')))
            $err['localisation'] = "La localisation est obligatoire.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,100}$/u', trim($d['localisation'])))
            $err['localisation'] = "La localisation doit contenir uniquement des lettres (2–100 caractères).";

        return $err;
    }

    // ── Helpers privés ───────────────────────────────────────
    private function rowToObject(array $row): Candidature
    {
        return new Candidature(
            $row['idCandidature'],
            $row['idUtilisateur'],
            $row['skills'],
            $row['cv'],
            $row['localisation']
        );
    }
}
