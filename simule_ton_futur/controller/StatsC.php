<?php

require_once __DIR__ . '/../config.php';

class StatsC
{
    public function getStats(int $userId): array
    {
        $pdo = Config::getConnexion();

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE expediteur_id = :id");
        $stmt->execute(['id' => $userId]);
        $msgEnvoyes = (int) $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE destinataire_id = :id");
        $stmt->execute(['id' => $userId]);
        $msgRecus = (int) $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE destinataire_id = :id AND est_lu = 0");
        $stmt->execute(['id' => $userId]);
        $msgNonLus = (int) $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM conversations WHERE utilisateur1_id = :id OR utilisateur2_id = :id2");
        $stmt->execute(['id' => $userId, 'id2' => $userId]);
        $nbConversations = (int) $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM blocages WHERE bloqueur_id = :id");
        $stmt->execute(['id' => $userId]);
        $nbBloques = (int) $stmt->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT CONCAT(u.prenom, ' ', u.nom) AS pseudo, COUNT(*) AS nb_messages FROM messages m
             INNER JOIN utilisateur u ON u.idUtilisateur = IF(m.expediteur_id = :id, m.destinataire_id, m.expediteur_id)
             WHERE m.expediteur_id = :id2 OR m.destinataire_id = :id3
             GROUP BY u.idUtilisateur ORDER BY nb_messages DESC LIMIT 5"
        );
        $stmt->execute(['id' => $userId, 'id2' => $userId, 'id3' => $userId]);
        $topContacts = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            "SELECT DATE(date_envoi) AS jour, COUNT(*) AS nb FROM messages
             WHERE (expediteur_id = :id OR destinataire_id = :id2) AND date_envoi >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(date_envoi) ORDER BY jour ASC"
        );
        $stmt->execute(['id' => $userId, 'id2' => $userId]);
        $messagesParJour = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            "SELECT r.emoji, COUNT(*) AS nb FROM reactions r
             INNER JOIN messages m ON r.message_id = m.id
             WHERE m.expediteur_id = :id GROUP BY r.emoji ORDER BY nb DESC"
        );
        $stmt->execute(['id' => $userId]);
        $reactionsRecues = $stmt->fetchAll();

        return [
            'msg_envoyes' => $msgEnvoyes, 'msg_recus' => $msgRecus, 'msg_non_lus' => $msgNonLus,
            'nb_conversations' => $nbConversations, 'nb_bloques' => $nbBloques,
            'top_contacts' => $topContacts, 'messages_par_jour' => $messagesParJour,
            'reactions_recues' => $reactionsRecues,
        ];
    }
}
