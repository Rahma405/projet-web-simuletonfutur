<?php
require_once 'header_messagerie.php';
require_once __DIR__ . "/../../../controller/ConversationC.php";
require_once __DIR__ . "/../../../config.php";

$convC  = new ConversationC();
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer'])) {
    $destId = filter_var($_POST['destinataire'] ?? 0, FILTER_VALIDATE_INT);
    $sujet  = $_POST['sujet'] ?? '';
    if ($convC->creerConversation($userId, (int) $destId, $sujet)) {
        $newConvId = $_SESSION['last_conv_id'] ?? 0;
        header("Location: showConversation.php?id=$newConvId");
        exit;
    }
    $erreurs = $convC->getErreurs();
}

// Lister les utilisateurs (sauf moi)
$pdo = Config::getConnexion();
$stmt = $pdo->prepare("SELECT idUtilisateur AS id, CONCAT(prenom, ' ', nom) AS pseudo, role AS categorie FROM utilisateur WHERE idUtilisateur != :id ORDER BY prenom ASC");
$stmt->execute(['id' => $userId]);
$utilisateurs = $stmt->fetchAll();
?>

<div class="msg-card">
    <h1 class="msg-title">Nouvelle conversation</h1>

    <?php if (!empty($erreurs)): ?>
        <ul class="msg-erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if (empty($utilisateurs)): ?>
        <p class="msg-info">Aucun autre utilisateur disponible.</p>
    <?php else: ?>
        <form method="POST" action="" class="msg-form">
            <div class="msg-form-group">
                <label for="destinataire">Destinataire :</label>
                <select name="destinataire" id="destinataire">
                    <option value="0">-- Choisir un utilisateur --</option>
                    <?php foreach ($utilisateurs as $u): ?>
                        <?php $sel = ((int)($_POST['destinataire'] ?? 0) === (int)$u['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int) $u['id'] ?>" <?= $sel ?>><?= htmlspecialchars($u['pseudo']) ?> (<?= htmlspecialchars($u['categorie']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="msg-form-group">
                <label for="sujet">Sujet :</label>
                <input type="text" name="sujet" id="sujet" value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>">
            </div>
            <div class="msg-form-actions">
                <input type="submit" name="creer" value="Créer la conversation" class="msg-btn">
                <a href="conversations.php" class="msg-btn msg-btn-outline">Annuler</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
