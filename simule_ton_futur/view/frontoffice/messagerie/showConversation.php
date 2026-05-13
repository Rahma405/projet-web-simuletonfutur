<?php
require_once 'header_messagerie.php';
require_once __DIR__ . "/../../../controller/ConversationC.php";
require_once __DIR__ . "/../../../controller/MessageC.php";
require_once __DIR__ . "/../../../controller/BlocageC.php";
require_once __DIR__ . "/../../../controller/ReactionC.php";

$convC    = new ConversationC();
$msgC     = new MessageC();
$blocC    = new BlocageC();
$reactC   = new ReactionC();

$convId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if ($convId === false || $convId <= 0) { echo '<p class="msg-erreur">Identifiant de conversation invalide.</p>'; require_once 'footer.php'; exit; }

$conv = $convC->getConversation($convId);
if (!$conv) { echo '<p class="msg-erreur">Cette conversation n\'existe pas.</p>'; require_once 'footer.php'; exit; }
if ($conv['utilisateur1_id'] != $userId && $conv['utilisateur2_id'] != $userId) { echo '<p class="msg-erreur">Accès refusé.</p>'; require_once 'footer.php'; exit; }

if ($conv['utilisateur1_id'] == $userId) { $autreId = (int) $conv['utilisateur2_id']; $autrePseudo = $conv['pseudo2']; }
else { $autreId = (int) $conv['utilisateur1_id']; $autrePseudo = $conv['pseudo1']; }

$estBloqueParMoi = $blocC->estBloque($userId, $autreId);
$blocageExiste   = $blocC->estBloqueEntreDeux($userId, $autreId);

if (isset($_GET['bloquer'])) { $blocC->bloquer($userId, $autreId); header("Location: showConversation.php?id=$convId"); exit; }
if (isset($_GET['debloquer'])) { $blocC->debloquer($userId, $autreId); header("Location: showConversation.php?id=$convId"); exit; }

if (isset($_GET['react_msg']) && isset($_GET['emoji'])) {
    $reactMsgId = filter_var($_GET['react_msg'], FILTER_VALIDATE_INT);
    $emoji = $_GET['emoji'];
    if ($reactMsgId) { $reactC->reagir($reactMsgId, $userId, $emoji); header("Location: showConversation.php?id=$convId"); exit; }
}

$msgC->marquerCommeLus($userId, $autreId);

if (isset($_GET['supprimer_msg'])) {
    $delMsgId = filter_var($_GET['supprimer_msg'], FILTER_VALIDATE_INT);
    if ($delMsgId && $msgC->supprimerMessage($delMsgId, $userId)) { header("Location: showConversation.php?id=$convId"); exit; }
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_msg'])) {
    $editMsgId = (int) ($_POST['msg_id'] ?? 0);
    $editContenu = $_POST['contenu'] ?? '';
    if ($msgC->modifierMessage($editMsgId, $userId, $editContenu)) { header("Location: showConversation.php?id=$convId"); exit; }
    $erreurs = $msgC->getErreurs();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    if ($blocageExiste) { $erreurs[] = "Impossible d'envoyer un message. Un blocage existe entre vous."; }
    else {
        $contenu = $_POST['contenu'] ?? '';
        $fileImage = $_FILES['image'] ?? null;
        if ($msgC->envoyerMessage($userId, $autreId, $contenu, $fileImage)) { header("Location: showConversation.php?id=$convId"); exit; }
        $erreurs = $msgC->getErreurs();
    }
}

$messages  = $msgC->listerMessages($userId, $autreId);
$msgIds    = array_column($messages, 'id');
$reactions = $reactC->getReactionsPourMessages($msgIds);
$editingMsgId = isset($_GET['edit_msg']) ? (int) $_GET['edit_msg'] : 0;
$emojis    = ReactionC::EMOJIS;
?>

<div class="msg-page">
    <div class="msg-page-header">
        <a href="conversations.php" class="msg-btn msg-btn-outline">&#8592; Retour</a>
        <div class="msg-conv-title">
            <h1><?= htmlspecialchars($autrePseudo) ?></h1>
            <span class="msg-conv-sujet"><?= htmlspecialchars($conv['sujet']) ?></span>
        </div>
        <a href="exportPDF.php?id=<?= $convId ?>" class="msg-btn msg-btn-sm" target="_blank">Exporter PDF</a>
        <?php if ($estBloqueParMoi): ?>
            <a href="showConversation.php?id=<?= $convId ?>&debloquer=1" class="msg-btn msg-btn-sm msg-btn-warning">Débloquer</a>
        <?php else: ?>
            <a href="showConversation.php?id=<?= $convId ?>&bloquer=1" class="msg-btn msg-btn-sm msg-btn-danger"
               onclick="return confirm('Bloquer <?= htmlspecialchars($autrePseudo) ?> ?')">Bloquer</a>
        <?php endif; ?>
    </div>

    <?php if ($blocageExiste): ?>
        <div class="msg-erreur">Un blocage existe entre vous et <?= htmlspecialchars($autrePseudo) ?>. Les messages ne peuvent pas être envoyés.</div>
    <?php endif; ?>

    <div class="msg-thread" id="msg-thread">
        <?php if (empty($messages)): ?>
            <p class="msg-info msg-info-center">Aucun message. Envoyez le premier !</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <?php
                    $estMoi   = ((int)$msg['expediteur_id'] === $userId);
                    $msgId    = (int) $msg['id'];
                    $msgReacts = $reactions[$msgId] ?? [];
                ?>
                <div class="msg-bubble-wrap <?= $estMoi ? 'msg-right' : 'msg-left' ?>">
                    <div class="msg-bubble <?= $estMoi ? 'msg-bubble-moi' : 'msg-bubble-autre' ?>">
                        <div class="msg-bubble-meta">
                            <span class="msg-bubble-pseudo"><?= htmlspecialchars($msg['pseudo_expediteur']) ?></span>
                            <span class="msg-bubble-date"><?= htmlspecialchars($msg['date_envoi']) ?></span>
                            <?php if (!$estMoi): ?>
                                <span class="msg-lu-statut"><?= $msg['est_lu'] ? '&#10003; Lu' : '&#9679; Non lu' ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($estMoi && $editingMsgId === $msgId): ?>
                            <form method="POST" action="" class="msg-edit-form">
                                <input type="hidden" name="msg_id" value="<?= $msgId ?>">
                                <textarea name="contenu" rows="2"><?= htmlspecialchars($msg['contenu']) ?></textarea>
                                <button type="submit" name="modifier_msg" class="edit-save">OK</button>
                                <a href="showConversation.php?id=<?= $convId ?>" class="edit-cancel" style="padding:6px 14px;text-decoration:none;">&#10005;</a>
                            </form>
                        <?php else: ?>
                            <?php if (!empty($msg['image'])): ?>
                                <div class="msg-bubble-image">
                                    <a href="../../../uploads/<?= htmlspecialchars($msg['image']) ?>" target="_blank">
                                        <img src="../../../uploads/<?= htmlspecialchars($msg['image']) ?>" alt="Image">
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($msg['contenu'])): ?>
                                <div class="msg-bubble-contenu"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                            <?php endif; ?>

                            <?php if (!empty($msgReacts)): ?>
                                <div class="reaction-display">
                                    <?php $grouped = []; foreach ($msgReacts as $r) { $grouped[$r['emoji']][] = $r['pseudo']; } ?>
                                    <?php foreach ($grouped as $emoji => $pseudos): ?>
                                        <span class="reaction-badge" title="<?= htmlspecialchars(implode(', ', $pseudos)) ?>"><?= $emoji ?> <?= count($pseudos) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="msg-bubble-actions">
                                <?php foreach ($emojis as $em): ?>
                                    <a href="showConversation.php?id=<?= $convId ?>&react_msg=<?= $msgId ?>&emoji=<?= urlencode($em) ?>" class="reaction-btn" title="<?= $em ?>"><?= $em ?></a>
                                <?php endforeach; ?>
                                <?php if ($estMoi): ?>
                                    <a href="showConversation.php?id=<?= $convId ?>&edit_msg=<?= $msgId ?>" class="action-edit">Modifier</a>
                                    <a href="showConversation.php?id=<?= $convId ?>&supprimer_msg=<?= $msgId ?>" class="action-delete" onclick="return confirm('Supprimer ce message ?')">Supprimer</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($erreurs)): ?>
        <ul class="msg-erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if (!$blocageExiste): ?>
        <form method="POST" action="" class="msg-send-form" enctype="multipart/form-data">
            <div class="msg-send-fields">
                <textarea name="contenu" id="contenu" class="msg-textarea" placeholder="Écrivez votre message..." rows="3"><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                <div class="msg-send-file">
                    <label for="image" class="msg-btn msg-btn-sm msg-btn-outline" title="Joindre une photo">&#128247; Photo</label>
                    <input type="file" name="image" id="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <img id="img-preview" class="img-preview" style="display:none;" alt="Aperçu">
                </div>
            </div>
            <input type="submit" name="envoyer" value="Envoyer &#10148;" class="msg-btn">
        </form>
        <script>
        function previewImage(input) {
            const preview = document.getElementById('img-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>
    <?php endif; ?>
</div>

<script>
    const thread = document.getElementById('msg-thread');
    if (thread) thread.scrollTop = thread.scrollHeight;
</script>

<?php require_once 'footer.php'; ?>
