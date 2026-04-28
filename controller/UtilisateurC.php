<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Utilisateur.php';

/**
 * UtilisateurC.php — Contrôleur CRUD Utilisateur
 * PDO + requêtes préparées + validation PHP (sans HTML5)
 */
class UtilisateurC
{
    // ════════════════════════════════════════════════════════
    //  C — CREATE
    // ════════════════════════════════════════════════════════
    public function addUtilisateur(Utilisateur $u): bool
    {
        return $this->createUtilisateur($u) !== null;
    }

    public function createUtilisateur(Utilisateur $u): ?int
    {
        $sql = "INSERT INTO utilisateur (nom, prenom, email, motDePasse, role)
                VALUES (:nom, :prenom, :email, :motDePasse, :role)";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':nom'        => $u->getNom(),
                ':prenom'     => $u->getPrenom(),
                ':email'      => $u->getEmail(),
                ':motDePasse' => password_hash($u->getMotDePasse(), PASSWORD_BCRYPT),
                ':role'       => $u->getRole(),
            ]);
            return (int) Config::getConnexion()->lastInsertId();
        } catch (PDOException $e) {
            error_log('addUtilisateur: ' . $e->getMessage());
            return null;
        }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : liste complète
    // ════════════════════════════════════════════════════════
    public function listUtilisateurs(): array
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur ORDER BY idUtilisateur DESC");
            $q->execute();
            return $this->rowsToObjects($q->fetchAll());
        } catch (PDOException $e) { return []; }
    }

    // ════════════════════════════════════════════════════════
    //  R — READ : par ID
    // ════════════════════════════════════════════════════════
    public function getById(int $id): ?Utilisateur
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur WHERE idUtilisateur = :id");
            $q->execute([':id' => $id]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) { return null; }
    }

    // ════════════════════════════════════════════════════════
    //  U — UPDATE
    // ════════════════════════════════════════════════════════
    public function login(string $email, string $motDePasse): ?Utilisateur
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $q->execute([':email' => trim($email)]);
            $row = $q->fetch();

            if ($row && password_verify($motDePasse, $row['motDePasse'])) {
                return $this->rowToObject($row);
            }

            return null;
        } catch (PDOException $e) {
            error_log('login: ' . $e->getMessage());
            return null;
        }
    }

    public function updateUtilisateur(Utilisateur $u, int $id, bool $changerMdp = false): bool
    {
        if ($changerMdp) {
            $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email,
                        motDePasse=:motDePasse, role=:role
                    WHERE idUtilisateur=:id";
            $params = [
                ':nom'        => $u->getNom(),
                ':prenom'     => $u->getPrenom(),
                ':email'      => $u->getEmail(),
                ':motDePasse' => password_hash($u->getMotDePasse(), PASSWORD_BCRYPT),
                ':role'       => $u->getRole(),
                ':id'         => $id,
            ];
        } else {
            $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, role=:role
                    WHERE idUtilisateur=:id";
            $params = [
                ':nom'    => $u->getNom(),
                ':prenom' => $u->getPrenom(),
                ':email'  => $u->getEmail(),
                ':role'   => $u->getRole(),
                ':id'     => $id,
            ];
        }
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log('updateUtilisateur: ' . $e->getMessage());
            return false;
        }
    }

    // ════════════════════════════════════════════════════════
    //  D — DELETE
    // ════════════════════════════════════════════════════════
    public function deleteUtilisateur(int $id): bool
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("DELETE FROM utilisateur WHERE idUtilisateur = :id");
            $q->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    // ════════════════════════════════════════════════════════
    //  RECHERCHE
    // ════════════════════════════════════════════════════════
    public function search(string $terme): array
    {
        try {
            $q = Config::getConnexion()->prepare(
                "SELECT * FROM utilisateur
                 WHERE nom LIKE :t OR prenom LIKE :t OR email LIKE :t
                 ORDER BY idUtilisateur DESC"
            );
            $q->execute([':t' => '%' . $terme . '%']);
            return $this->rowsToObjects($q->fetchAll());
        } catch (PDOException $e) { return []; }
    }

    // ════════════════════════════════════════════════════════
    //  STATS
    // ════════════════════════════════════════════════════════
    public function getStats(): array
    {
        try {
            $db     = Config::getConnexion();
            $total  = $db->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
            $admins = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role='admin'")->fetchColumn();
            $users  = $db->query("SELECT COUNT(*) FROM utilisateur WHERE role='user'")->fetchColumn();
            return compact('total', 'admins', 'users');
        } catch (PDOException $e) {
            return ['total'=>0,'admins'=>0,'users'=>0];
        }
    }

    // ════════════════════════════════════════════════════════
    //  VALIDATION PHP — jamais HTML5
    // ════════════════════════════════════════════════════════
    public function valider(array $d, int $excludeId = 0, bool $creation = true): array
    {
        $err = [];

        // Nom
        if (empty(trim($d['nom'] ?? '')))
            $err['nom'] = "Le nom est obligatoire.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', trim($d['nom'])))
            $err['nom'] = "Le nom doit contenir uniquement des lettres (2–50 caractères).";

        // Prénom
        if (empty(trim($d['prenom'] ?? '')))
            $err['prenom'] = "Le prénom est obligatoire.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', trim($d['prenom'])))
            $err['prenom'] = "Le prénom doit contenir uniquement des lettres (2–50 caractères).";

        // Email
        if (empty(trim($d['email'] ?? '')))
            $err['email'] = "L'email est obligatoire.";
        elseif (!filter_var(trim($d['email']), FILTER_VALIDATE_EMAIL))
            $err['email'] = "L'adresse email n'est pas valide.";
        elseif ($this->emailExiste(trim($d['email']), $excludeId))
            $err['email'] = "Cet email est déjà utilisé.";

        // Mot de passe (obligatoire uniquement à la création)
        if ($creation) {
            if (empty($d['motDePasse'] ?? ''))
                $err['motDePasse'] = "Le mot de passe est obligatoire.";
            elseif (strlen($d['motDePasse']) < 6)
                $err['motDePasse'] = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            // En modification : valider seulement si renseigné
            if (!empty($d['motDePasse']) && strlen($d['motDePasse']) < 6)
                $err['motDePasse'] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        // Rôle
        if (!in_array($d['role'] ?? '', ['admin', 'user']))
            $err['role'] = "Le rôle sélectionné est invalide.";

        return $err;
    }

    // ── Helpers privés ───────────────────────────────────────
    private function emailExiste(string $email, int $excludeId = 0): bool
    {
        try {
            $q = Config::getConnexion()->prepare(
                "SELECT COUNT(*) FROM utilisateur
                 WHERE email = :email AND idUtilisateur != :id"
            );
            $q->execute([':email' => $email, ':id' => $excludeId]);
            return (int)$q->fetchColumn() > 0;
        } catch (PDOException $e) { return false; }
    }

    private function rowToObject(array $row): Utilisateur
    {
        return new Utilisateur(
            $row['idUtilisateur'],
            $row['nom'],
            $row['prenom'],
            $row['email'],
            $row['motDePasse'],
            $row['role']
        );
    }

    private function rowsToObjects(array $rows): array
    {
        return array_map([$this, 'rowToObject'], $rows);
    }
}
