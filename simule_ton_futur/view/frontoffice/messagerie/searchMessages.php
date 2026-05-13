<?php
require_once 'header_messagerie.php';
require_once __DIR__ . "/../../../controller/MessageC.php";

$msgC          = new MessageC();
$results       = [];
$erreurs       = [];
$motCle        = '';
$rechercheFaite = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechercher'])) {
    $motCle         = $_POST['mot_cle'] ?? '';
    $results        = $msgC->rechercherMessages($userId, $motCle);
    $erreurs        = $msgC->getErreurs();
    $rechercheFaite = empty($erreurs);
}
?>

<div class="msg-page">
    <h1>Recherche dans les messages</h1>

    <form method="POST" action="" class="msg-form msg-form-inline">
        <div class="msg-form-group">
            <label for="mot_cle">Mot-clé :</label>
            <input type="text" name="mot_cle" id="mot_cle" value="<?= htmlspecialchars($motCle) ?>" placeholder="Rechercher dans vos messages...">
        </div>
        <input type="submit" name="rechercher" value="Rechercher" class="msg-btn">
    </form>

    <?php if (!empty($erreurs)): ?>
        <ul class="msg-erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if ($rechercheFaite): ?>
        <div class="search-results">
            <h2 class="search-count-title"><?= count($results) ?> message(s) trouvé(s) pour "<em><?= htmlspecialchars($motCle) ?></em>"</h2>
            <?php if (empty($results)): ?>
                <p class="msg-info">Aucun message ne correspond à votre recherche.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($results as $msg): ?>
                        <li class="search-item">
                            <div class="search-item-header">
                                <div class="search-participants">
                                    <strong><?= htmlspecialchars($msg['pseudo_expediteur']) ?></strong>
                                    <span class="search-arrow">&#8594;</span>
                                    <strong><?= htmlspecialchars($msg['pseudo_destinataire']) ?></strong>
                                </div>
                                <div class="search-meta">
                                    <span class="conv-sujet-tag"><?= htmlspecialchars($msg['sujet_conversation']) ?></span>
                                    <span class="msg-date"><?= htmlspecialchars($msg['date_envoi']) ?></span>
                                    <?php if (!$msg['est_lu']): ?><span class="badge badge-sm">Non lu</span><?php endif; ?>
                                </div>
                            </div>
                            <div class="search-item-contenu"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                            <a href="showConversation.php?id=<?= (int) $msg['conversation_id'] ?>" class="msg-btn msg-btn-sm">Voir la conversation &#10148;</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
