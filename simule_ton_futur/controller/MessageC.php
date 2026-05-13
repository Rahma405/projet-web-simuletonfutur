<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Message.php';
require_once __DIR__ . '/TwilioC.php';

class MessageC
{
    private array $erreurs = [];
    private ?string $imageUploadee = null;

    public function traiterUploadImage(array $file): bool
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) return true;
        if ($file['error'] !== UPLOAD_ERR_OK) { $this->erreurs[] = "Erreur lors de l'upload de l'image."; return false; }

        $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $typesAutorises)) { $this->erreurs[] = "Format d'image non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP."; return false; }
        if ($file['size'] > 5 * 1024 * 1024) { $this->erreurs[] = "L'image est trop volumineuse (maximum 5 Mo)."; return false; }

        $ext = match($mimeType) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', default => 'jpg' };
        $nomFichier = uniqid('img_') . '.' . $ext;
        $destination = __DIR__ . '/../uploads/' . $nomFichier;

        if (!move_uploaded_file($file['tmp_name'], $destination)) { $this->erreurs[] = "Impossible de sauvegarder l'image."; return false; }
        $this->imageUploadee = $nomFichier;
        return true;
    }

    public function listerMessages(int $u1Id, int $u2Id): array
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    m.id, m.contenu, m.image, m.date_envoi, m.est_lu, m.expediteur_id,
                    CONCAT(u.prenom, ' ', u.nom) AS pseudo_expediteur
                 FROM messages m
                 INNER JOIN utilisateur u ON m.expediteur_id = u.idUtilisateur
                 WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                    OR (m.expediteur_id = ? AND m.destinataire_id = ?)
                 ORDER BY m.date_envoi ASC"
            );
            $stmt->execute([$u1Id, $u2Id, $u2Id, $u1Id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo '<p class="msg-erreur">Erreur BD : ' . htmlspecialchars($e->getMessage()) . '</p>';
            return [];
        }
    }

    public function envoyerMessage(int $expedId, int $destId, string $contenu, ?array $fileImage = null): bool
    {
        $this->erreurs = [];
        $this->imageUploadee = null;
        $contenu = trim($contenu);

        if ($fileImage && $fileImage['error'] !== UPLOAD_ERR_NO_FILE) {
            $this->traiterUploadImage($fileImage);
            if (!empty($this->erreurs)) return false;
        }

        if ($contenu === '' && $this->imageUploadee === null) $this->erreurs[] = "Le message ne peut pas être vide.";
        elseif (strlen($contenu) > 5000) $this->erreurs[] = "Le message est trop long (5000 caractères maximum).";
        if (!empty($this->erreurs)) return false;

        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, contenu, image) VALUES (:exp, :dest, :contenu, :image)");
            $stmt->execute(['exp' => $expedId, 'dest' => $destId, 'contenu' => htmlspecialchars($contenu), 'image' => $this->imageUploadee]);

            // Notification SMS
            $twilio = new TwilioC();
            $stmtU  = $pdo->prepare("SELECT prenom FROM utilisateur WHERE idUtilisateur = :id");
            $stmtU->execute(['id' => $expedId]);
            $expediteur = $stmtU->fetch();
            $pseudoExp  = $expediteur ? $expediteur['prenom'] : 'Utilisateur';
            $twilio->notifierNouveauMessage($destId, $pseudoExp, $contenu);

            return true;
        } catch (PDOException $e) {
            $this->erreurs[] = "Erreur BD : " . $e->getMessage();
            return false;
        }
    }

    public function marquerCommeLus(int $destId, int $expId): void
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("UPDATE messages SET est_lu = 1 WHERE destinataire_id = :dest AND expediteur_id = :exp AND est_lu = 0");
            $stmt->execute(['dest' => $destId, 'exp' => $expId]);
        } catch (PDOException $e) {}
    }

    public function rechercherMessages(int $userId, string $motCle): array
    {
        $this->erreurs = [];
        $motCle = trim($motCle);
        if ($motCle === '') { $this->erreurs[] = "Veuillez saisir un mot-clé."; return []; }
        if (strlen($motCle) < 2) { $this->erreurs[] = "Le mot-clé doit contenir au moins 2 caractères."; return []; }

        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare(
                "SELECT
                    m.id, m.contenu, m.date_envoi, m.est_lu,
                    CONCAT(u_exp.prenom, ' ', u_exp.nom) AS pseudo_expediteur,
                    CONCAT(u_dest.prenom, ' ', u_dest.nom) AS pseudo_destinataire,
                    c.sujet AS sujet_conversation,
                    c.id AS conversation_id
                 FROM messages m
                 INNER JOIN utilisateur u_exp  ON m.expediteur_id   = u_exp.idUtilisateur
                 INNER JOIN utilisateur u_dest ON m.destinataire_id = u_dest.idUtilisateur
                 INNER JOIN conversations c
                    ON ((c.utilisateur1_id = m.expediteur_id AND c.utilisateur2_id = m.destinataire_id)
                     OR (c.utilisateur1_id = m.destinataire_id AND c.utilisateur2_id = m.expediteur_id))
                 WHERE (m.expediteur_id = ? OR m.destinataire_id = ?)
                   AND m.contenu LIKE ?
                 ORDER BY m.date_envoi DESC"
            );
            $stmt->execute([$userId, $userId, '%' . $motCle . '%']);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->erreurs[] = "Erreur BD : " . $e->getMessage();
            return [];
        }
    }

    public function compterMessagesNonLus(int $userId): int
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE destinataire_id = :userId AND est_lu = 0");
            $stmt->execute(['userId' => $userId]);
            return (int) ($stmt->fetch()['total'] ?? 0);
        } catch (PDOException $e) { return 0; }
    }

    public function modifierMessage(int $msgId, int $userId, string $contenu): bool
    {
        $this->erreurs = [];
        $contenu = trim($contenu);
        if ($contenu === '') $this->erreurs[] = "Le message ne peut pas être vide.";
        elseif (strlen($contenu) > 5000) $this->erreurs[] = "Le message est trop long (5000 caractères maximum).";
        if (!empty($this->erreurs)) return false;

        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("UPDATE messages SET contenu = :contenu WHERE id = :id AND expediteur_id = :userId");
            $stmt->execute(['contenu' => htmlspecialchars($contenu), 'id' => $msgId, 'userId' => $userId]);
            if ($stmt->rowCount() === 0) { $this->erreurs[] = "Message introuvable ou vous n'êtes pas l'expéditeur."; return false; }
            return true;
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function supprimerMessage(int $msgId, int $userId): bool
    {
        $this->erreurs = [];
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id AND expediteur_id = :userId");
            $stmt->execute(['id' => $msgId, 'userId' => $userId]);
            if ($stmt->rowCount() === 0) { $this->erreurs[] = "Message introuvable ou vous n'êtes pas l'expéditeur."; return false; }
            return true;
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function getErreurs(): array { return $this->erreurs; }
}
