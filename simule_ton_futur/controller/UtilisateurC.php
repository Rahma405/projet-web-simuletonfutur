<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Utilisateur.php';

/**
 * UtilisateurC.php — Contrôleur CRUD Utilisateur
 * PDO + requêtes préparées + validation PHP (sans HTML5)
 */
class UtilisateurC
{
    private ?string $lastLoginError = null;
    // ════════════════════════════════════════════════════════
    //  C — CREATE
    // ════════════════════════════════════════════════════════
    public function addUtilisateur(Utilisateur $u): bool
    {
        return $this->createUtilisateur($u) !== null;
    }

    public function createUtilisateur(Utilisateur $u): ?int
    {
        $sql = "INSERT INTO utilisateur (nom, prenom, email, motDePasse, role, statut)
                VALUES (:nom, :prenom, :email, :motDePasse, :role, :statut)";
        try {
            $q = Config::getConnexion()->prepare($sql);
            $q->execute([
                ':nom'        => $u->getNom(),
                ':prenom'     => $u->getPrenom(),
                ':email'      => $u->getEmail(),
                ':motDePasse' => password_hash($u->getMotDePasse(), PASSWORD_BCRYPT),
                ':role'       => $u->getRole(),
                ':statut'     => $u->getStatut(),
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
    public function listUtilisateurs(string $tri = 'recent'): array
    {
        try {
            $orderBy = $this->getOrderByClause($tri);
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur ORDER BY $orderBy");
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
            $this->lastLoginError = null;
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $q->execute([':email' => trim($email)]);
            $row = $q->fetch();

            if ($row && ($row['statut'] ?? 'actif') === 'bloque') {
                $this->lastLoginError = "Ce compte est bloque. Contactez l'administrateur.";
                return null;
            }

            if ($row && password_verify($motDePasse, $row['motDePasse'])) {
                return $this->rowToObject($row);
            }

            if ($row) {
                $this->lastLoginError = 'Mot de passe incorrect.';
            } else {
                $this->lastLoginError = "Aucun compte n'est associe a cet email.";
            }
            return null;
        } catch (PDOException $e) {
            error_log('login: ' . $e->getMessage());
            return null;
        }
    }

    public function getLastLoginError(): ?string
    {
        return $this->lastLoginError ?? null;
    }

    public function getByEmail(string $email): ?Utilisateur
    {
        try {
            $q = Config::getConnexion()
                       ->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $q->execute([':email' => trim($email)]);
            $row = $q->fetch();
            return $row ? $this->rowToObject($row) : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->getByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $hash = password_hash($token, PASSWORD_BCRYPT);
        $expireAt = date('Y-m-d H:i:s', time() + 3600);

        try {
            $db = Config::getConnexion();
            $cleanup = $db->prepare("DELETE FROM password_reset WHERE idUtilisateur = :id");
            $cleanup->execute([':id' => $user->getIdUtilisateur()]);

            $insert = $db->prepare(
                "INSERT INTO password_reset (idUtilisateur, tokenHash, expireAt)
                 VALUES (:idUtilisateur, :tokenHash, :expireAt)"
            );
            $insert->execute([
                ':idUtilisateur' => $user->getIdUtilisateur(),
                ':tokenHash' => $hash,
                ':expireAt' => $expireAt,
            ]);

            return $token;
        } catch (Throwable $e) {
            error_log('createPasswordResetToken: ' . $e->getMessage());
            return null;
        }
    }

    public function sendPasswordResetEmail(string $email, string $resetLink): bool
    {
        $subject = 'Reinitialisation de mot de passe - Simule Ton Futur';
        $message = "Bonjour,\n\n";
        $message .= "Vous avez demande la reinitialisation de votre mot de passe.\n";
        $message .= "Cliquez sur ce lien pour le changer :\n";
        $message .= $resetLink . "\n\n";
        $message .= "Ce lien expire dans 1 heure.\n";
        $message .= "Si vous n'etes pas a l'origine de cette demande, ignorez cet email.\n\n";
        $message .= "Simule Ton Futur";

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=UTF-8';
        $headers[] = 'From: no-reply@simuletonfutur.local';

        return @mail($email, $subject, $message, implode("\r\n", $headers));
    }

    public function getUtilisateurByResetToken(string $token): ?Utilisateur
    {
        try {
            $q = Config::getConnexion()->query(
                "SELECT pr.tokenHash, pr.expireAt, u.*
                 FROM password_reset pr
                 INNER JOIN utilisateur u ON u.idUtilisateur = pr.idUtilisateur"
            );
            $rows = $q->fetchAll();

            foreach ($rows as $row) {
                if (strtotime((string) $row['expireAt']) < time()) {
                    continue;
                }

                if (password_verify($token, (string) $row['tokenHash'])) {
                    return $this->rowToObject($row);
                }
            }
        } catch (Throwable $e) {
            error_log('getUtilisateurByResetToken: ' . $e->getMessage());
        }

        return null;
    }

    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        $user = $this->getUtilisateurByResetToken($token);
        if (!$user) {
            return false;
        }

        try {
            $db = Config::getConnexion();
            $update = $db->prepare(
                "UPDATE utilisateur SET motDePasse = :motDePasse WHERE idUtilisateur = :id"
            );
            $update->execute([
                ':motDePasse' => password_hash($newPassword, PASSWORD_BCRYPT),
                ':id' => $user->getIdUtilisateur(),
            ]);

            $delete = $db->prepare("DELETE FROM password_reset WHERE idUtilisateur = :id");
            $delete->execute([':id' => $user->getIdUtilisateur()]);

            return true;
        } catch (Throwable $e) {
            error_log('resetPasswordWithToken: ' . $e->getMessage());
            return false;
        }
    }

    public function createQrLoginToken(string $email): ?string
    {
        $user = $this->getByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $hash = password_hash($token, PASSWORD_BCRYPT);
        $expireAt = date('Y-m-d H:i:s', time() + 600);

        try {
            $db = Config::getConnexion();
            $cleanup = $db->prepare("DELETE FROM qr_login WHERE idUtilisateur = :id");
            $cleanup->execute([':id' => $user->getIdUtilisateur()]);

            $insert = $db->prepare(
                "INSERT INTO qr_login (idUtilisateur, tokenHash, status, expireAt)
                 VALUES (:idUtilisateur, :tokenHash, 'pending', :expireAt)"
            );
            $insert->execute([
                ':idUtilisateur' => $user->getIdUtilisateur(),
                ':tokenHash' => $hash,
                ':expireAt' => $expireAt,
            ]);

            return $token;
        } catch (Throwable $e) {
            error_log('createQrLoginToken: ' . $e->getMessage());
            return null;
        }
    }

    public function getQrLoginStatus(string $token): string
    {
        $row = $this->getQrLoginRowByToken($token);
        if (!$row) {
            return 'invalid';
        }

        if (strtotime((string) $row['expireAt']) < time()) {
            $this->markQrLoginExpired((int) $row['idQrLogin']);
            return 'expired';
        }

        return (string) $row['status'];
    }

    public function approveQrLoginToken(string $token): bool
    {
        $row = $this->getQrLoginRowByToken($token);
        if (!$row) {
            return false;
        }

        if (strtotime((string) $row['expireAt']) < time()) {
            $this->markQrLoginExpired((int) $row['idQrLogin']);
            return false;
        }

        try {
            $q = Config::getConnexion()->prepare(
                "UPDATE qr_login SET status = 'approved' WHERE idQrLogin = :id"
            );
            $q->execute([':id' => $row['idQrLogin']]);
            return true;
        } catch (Throwable $e) {
            error_log('approveQrLoginToken: ' . $e->getMessage());
            return false;
        }
    }

    public function consumeQrLoginToken(string $token): ?Utilisateur
    {
        $row = $this->getQrLoginRowByToken($token);
        if (!$row) {
            return null;
        }

        if ((string) $row['status'] !== 'approved') {
            return null;
        }

        if (strtotime((string) $row['expireAt']) < time()) {
            $this->markQrLoginExpired((int) $row['idQrLogin']);
            return null;
        }

        try {
            $q = Config::getConnexion()->prepare(
                "UPDATE qr_login SET status = 'used' WHERE idQrLogin = :id"
            );
            $q->execute([':id' => $row['idQrLogin']]);
        } catch (Throwable $e) {
            error_log('consumeQrLoginToken: ' . $e->getMessage());
        }

        return $this->getById((int) $row['idUtilisateur']);
    }

    public function getUtilisateurByQrToken(string $token): ?Utilisateur
    {
        $row = $this->getQrLoginRowByToken($token);
        if (!$row) {
            return null;
        }

        if (strtotime((string) $row['expireAt']) < time()) {
            $this->markQrLoginExpired((int) $row['idQrLogin']);
            return null;
        }

        return $this->getById((int) $row['idUtilisateur']);
    }

    public function updateUtilisateur(Utilisateur $u, int $id, bool $changerMdp = false): bool
    {
        if ($changerMdp) {
            $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email,
                        motDePasse=:motDePasse, role=:role, statut=:statut
                    WHERE idUtilisateur=:id";
            $params = [
                ':nom'        => $u->getNom(),
                ':prenom'     => $u->getPrenom(),
                ':email'      => $u->getEmail(),
                ':motDePasse' => password_hash($u->getMotDePasse(), PASSWORD_BCRYPT),
                ':role'       => $u->getRole(),
                ':statut'     => $u->getStatut(),
                ':id'         => $id,
            ];
        } else {
            $sql = "UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, role=:role, statut=:statut
                    WHERE idUtilisateur=:id";
            $params = [
                ':nom'    => $u->getNom(),
                ':prenom' => $u->getPrenom(),
                ':email'  => $u->getEmail(),
                ':role'   => $u->getRole(),
                ':statut' => $u->getStatut(),
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
    public function search(string $terme, string $tri = 'recent'): array
    {
        try {
            $orderBy = $this->getOrderByClause($tri);
            $q = Config::getConnexion()->prepare(
                "SELECT * FROM utilisateur
                 WHERE email LIKE :t
                 ORDER BY $orderBy"
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

        if (!in_array($d['statut'] ?? '', ['actif', 'bloque', 'en_attente']))
            $err['statut'] = "Le statut sélectionné est invalide.";

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
            $row['role'],
            $row['statut'] ?? 'actif'
        );
    }

    private function rowsToObjects(array $rows): array
    {
        return array_map([$this, 'rowToObject'], $rows);
    }

    private function getOrderByClause(string $tri): string
    {
        return match ($tri) {
            'nom_asc' => 'nom ASC, prenom ASC',
            'nom_desc' => 'nom DESC, prenom DESC',
            default => 'idUtilisateur DESC',
        };
    }

    private function getQrLoginRowByToken(string $token): ?array
    {
        try {
            $q = Config::getConnexion()->query("SELECT * FROM qr_login ORDER BY idQrLogin DESC");
            $rows = $q->fetchAll();

            foreach ($rows as $row) {
                if (password_verify($token, (string) $row['tokenHash'])) {
                    return $row;
                }
            }
        } catch (Throwable $e) {
            error_log('getQrLoginRowByToken: ' . $e->getMessage());
        }

        return null;
    }

    private function markQrLoginExpired(int $idQrLogin): void
    {
        try {
            $q = Config::getConnexion()->prepare(
                "UPDATE qr_login SET status = 'expired' WHERE idQrLogin = :id"
            );
            $q->execute([':id' => $idQrLogin]);
        } catch (Throwable $e) {
            error_log('markQrLoginExpired: ' . $e->getMessage());
        }
    }
}
