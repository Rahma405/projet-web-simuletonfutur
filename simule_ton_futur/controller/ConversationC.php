<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Conversation.php';

class ConversationC
{
    private array $erreurs = [];

    public function listerConversations(int $userId): array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    c.id,
                    c.sujet,
                    c.date_creation,
                    u.idUtilisateur AS autre_id,
                    CONCAT(u.prenom, ' ', u.nom) AS autre_pseudo,
                    u.role AS autre_categorie,
                    (
                        SELECT COUNT(*)
                        FROM   messages m
                        WHERE  m.destinataire_id = ?
                          AND  m.expediteur_id   = u.idUtilisateur
                          AND  m.est_lu          = 0
                    ) AS nb_non_lus
                 FROM conversations c
                 INNER JOIN utilisateur u
                    ON u.idUtilisateur = IF(c.utilisateur1_id = ?, c.utilisateur2_id, c.utilisateur1_id)
                 WHERE c.utilisateur1_id = ? OR c.utilisateur2_id = ?
                 ORDER BY c.date_creation DESC"
            );
            $stmt->execute([$userId, $userId, $userId, $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo '<p class="msg-erreur">Erreur BD : ' . htmlspecialchars($e->getMessage()) . '</p>';
            return [];
        }
    }

    public function creerConversation(int $u1, int $u2, string $sujet): bool
    {
        $this->erreurs = [];
        $sujet = trim($sujet);

        if ($u2 <= 0) $this->erreurs[] = "Veuillez choisir un destinataire.";
        if ($u1 === $u2) $this->erreurs[] = "Vous ne pouvez pas démarrer une conversation avec vous-même.";
        if ($sujet === '') $this->erreurs[] = "Le sujet est obligatoire.";
        elseif (strlen($sujet) > 100) $this->erreurs[] = "Le sujet ne doit pas dépasser 100 caractères.";

        if (!empty($this->erreurs)) return false;

        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "INSERT INTO conversations (utilisateur1_id, utilisateur2_id, sujet) VALUES (:u1, :u2, :sujet)"
            );
            $stmt->execute(['u1' => $u1, 'u2' => $u2, 'sujet' => htmlspecialchars($sujet)]);
            $_SESSION['last_conv_id'] = (int) $pdo->lastInsertId();
            return true;
        } catch (PDOException $e) {
            $this->erreurs[] = "Erreur BD : " . $e->getMessage();
            return false;
        }
    }

    public function getConversation(int $convId): array|false
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    c.id, c.sujet, c.date_creation,
                    c.utilisateur1_id, c.utilisateur2_id,
                    CONCAT(u1.prenom, ' ', u1.nom) AS pseudo1,
                    CONCAT(u2.prenom, ' ', u2.nom) AS pseudo2
                 FROM conversations c
                 INNER JOIN utilisateur u1 ON c.utilisateur1_id = u1.idUtilisateur
                 INNER JOIN utilisateur u2 ON c.utilisateur2_id = u2.idUtilisateur
                 WHERE c.id = :id"
            );
            $stmt->execute(['id' => $convId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function modifierConversation(int $convId, int $userId, string $sujet): bool
    {
        $this->erreurs = [];
        $sujet = trim($sujet);
        if ($sujet === '') $this->erreurs[] = "Le sujet est obligatoire.";
        elseif (strlen($sujet) > 100) $this->erreurs[] = "Le sujet ne doit pas dépasser 100 caractères.";
        if (!empty($this->erreurs)) return false;

        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "UPDATE conversations SET sujet = :sujet WHERE id = :id AND (utilisateur1_id = :u OR utilisateur2_id = :u2)"
            );
            $stmt->execute(['sujet' => htmlspecialchars($sujet), 'id' => $convId, 'u' => $userId, 'u2' => $userId]);
            if ($stmt->rowCount() === 0) { $this->erreurs[] = "Conversation introuvable ou accès refusé."; return false; }
            return true;
        } catch (PDOException $e) {
            $this->erreurs[] = "Erreur BD : " . $e->getMessage();
            return false;
        }
    }

    public function supprimerConversation(int $convId, int $userId): bool
    {
        $this->erreurs = [];
        try {
            $pdo = Config::getConnexion();
            $check = $pdo->prepare("SELECT id FROM conversations WHERE id = :id AND (utilisateur1_id = :u OR utilisateur2_id = :u2)");
            $check->execute(['id' => $convId, 'u' => $userId, 'u2' => $userId]);
            if (!$check->fetch()) { $this->erreurs[] = "Conversation introuvable ou accès refusé."; return false; }

            $conv = $this->getConversation($convId);
            $u1 = (int) $conv['utilisateur1_id'];
            $u2 = (int) $conv['utilisateur2_id'];

            $delMsg = $pdo->prepare("DELETE FROM messages WHERE (expediteur_id = :a AND destinataire_id = :b) OR (expediteur_id = :b2 AND destinataire_id = :a2)");
            $delMsg->execute(['a' => $u1, 'b' => $u2, 'b2' => $u2, 'a2' => $u1]);

            $delConv = $pdo->prepare("DELETE FROM conversations WHERE id = :id");
            $delConv->execute(['id' => $convId]);
            return true;
        } catch (PDOException $e) {
            $this->erreurs[] = "Erreur BD : " . $e->getMessage();
            return false;
        }
    }

    public function getErreurs(): array { return $this->erreurs; }
}
