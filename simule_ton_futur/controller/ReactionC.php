<?php

require_once __DIR__ . '/../config.php';

class ReactionC
{
    private array $erreurs = [];
    const EMOJIS = ['👍', '❤️', '😂', '😮', '😢', '🔥'];

    public function reagir(int $messageId, int $userId, string $emoji): bool
    {
        $this->erreurs = [];
        if (!in_array($emoji, self::EMOJIS)) { $this->erreurs[] = "Réaction invalide."; return false; }

        try {
            $pdo = Config::getConnexion();
            $check = $pdo->prepare("SELECT id, emoji FROM reactions WHERE message_id = :mid AND utilisateur_id = :uid");
            $check->execute(['mid' => $messageId, 'uid' => $userId]);
            $existing = $check->fetch();

            if ($existing) {
                if ($existing['emoji'] === $emoji) {
                    $del = $pdo->prepare("DELETE FROM reactions WHERE id = :id");
                    $del->execute(['id' => $existing['id']]);
                } else {
                    $upd = $pdo->prepare("UPDATE reactions SET emoji = :emoji WHERE id = :id");
                    $upd->execute(['emoji' => $emoji, 'id' => $existing['id']]);
                }
            } else {
                $ins = $pdo->prepare("INSERT INTO reactions (message_id, utilisateur_id, emoji) VALUES (:mid, :uid, :emoji)");
                $ins->execute(['mid' => $messageId, 'uid' => $userId, 'emoji' => $emoji]);
            }
            return true;
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function getReactions(int $messageId): array
    {
        $pdo  = Config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT r.emoji, r.utilisateur_id, CONCAT(u.prenom, ' ', u.nom) AS pseudo
             FROM reactions r
             INNER JOIN utilisateur u ON r.utilisateur_id = u.idUtilisateur
             WHERE r.message_id = :mid"
        );
        $stmt->execute(['mid' => $messageId]);
        return $stmt->fetchAll();
    }

    public function getReactionsPourMessages(array $messageIds): array
    {
        if (empty($messageIds)) return [];
        $pdo = Config::getConnexion();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT r.message_id, r.emoji, r.utilisateur_id, CONCAT(u.prenom, ' ', u.nom) AS pseudo
             FROM reactions r
             INNER JOIN utilisateur u ON r.utilisateur_id = u.idUtilisateur
             WHERE r.message_id IN ($placeholders)
             ORDER BY r.date_reaction ASC"
        );
        $stmt->execute($messageIds);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) { $result[$row['message_id']][] = $row; }
        return $result;
    }

    public function getErreurs(): array { return $this->erreurs; }
}
