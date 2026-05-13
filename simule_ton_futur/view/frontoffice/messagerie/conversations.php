<?php
require_once 'header_messagerie.php';
require_once __DIR__ . "/../../../controller/ConversationC.php";

$convC  = new ConversationC();

// --- Suppression ---
if (isset($_GET['supprimer'])) {
    $delId = filter_var($_GET['supprimer'], FILTER_VALIDATE_INT);
    if ($delId && $convC->supprimerConversation($delId, $userId)) {
        header('Location: conversations.php');
        exit;
    }
}

// --- Modification du sujet ---
$editErreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_conv'])) {
    $editConvId = (int) ($_POST['conv_id'] ?? 0);
    $editSujet  = $_POST['sujet'] ?? '';
    if ($convC->modifierConversation($editConvId, $userId, $editSujet)) {
        header('Location: conversations.php');
        exit;
    }
    $editErreurs = $convC->getErreurs();
}

$conversations = $convC->listerConversations($userId);

// --- Tri et filtres ---
$filtre = $_GET['filtre'] ?? 'tous';
$tri    = $_GET['tri'] ?? 'recent';

if ($filtre === 'non_lus') {
    $conversations = array_filter($conversations, fn($c) => (int) $c['nb_non_lus'] > 0);
} elseif ($filtre === 'lus') {
    $conversations = array_filter($conversations, fn($c) => (int) $c['nb_non_lus'] === 0);
}

if ($tri === 'ancien') usort($conversations, fn($a, $b) => strcmp($a['date_creation'], $b['date_creation']));
elseif ($tri === 'non_lus') usort($conversations, fn($a, $b) => (int) $b['nb_non_lus'] - (int) $a['nb_non_lus']);
elseif ($tri === 'nom') usort($conversations, fn($a, $b) => strcasecmp($a['autre_pseudo'], $b['autre_pseudo']));
?>

<div class="msg-page">
    <div class="msg-page-header">
        <h1>Mes conversations</h1>
        <a href="newConversation.php" class="msg-btn">+ Nouvelle conversation</a>
    </div>

    <div class="filter-bar">
        <div class="filter-group">
            <span class="filter-label">Filtrer :</span>
            <a href="conversations.php?filtre=tous&tri=<?= $tri ?>" class="filter-chip <?= $filtre === 'tous' ? 'active' : '' ?>">Tous</a>
            <a href="conversations.php?filtre=non_lus&tri=<?= $tri ?>" class="filter-chip <?= $filtre === 'non_lus' ? 'active' : '' ?>">Non lus</a>
            <a href="conversations.php?filtre=lus&tri=<?= $tri ?>" class="filter-chip <?= $filtre === 'lus' ? 'active' : '' ?>">Lus</a>
        </div>
        <div class="filter-group">
            <span class="filter-label">Trier :</span>
            <a href="conversations.php?filtre=<?= $filtre ?>&tri=recent" class="filter-chip <?= $tri === 'recent' ? 'active' : '' ?>">Récent</a>
            <a href="conversations.php?filtre=<?= $filtre ?>&tri=ancien" class="filter-chip <?= $tri === 'ancien' ? 'active' : '' ?>">Ancien</a>
            <a href="conversations.php?filtre=<?= $filtre ?>&tri=non_lus" class="filter-chip <?= $tri === 'non_lus' ? 'active' : '' ?>">Non lus d'abord</a>
            <a href="conversations.php?filtre=<?= $filtre ?>&tri=nom" class="filter-chip <?= $tri === 'nom' ? 'active' : '' ?>">Nom</a>
        </div>
    </div>

    <?php if (!empty($editErreurs)): ?>
        <ul class="msg-erreurs"><?php foreach ($editErreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <?php if (empty($conversations)): ?>
        <p class="msg-info">Aucune conversation <?= $filtre !== 'tous' ? 'pour ce filtre' : '' ?>.</p>
    <?php else: ?>
        <ul class="conv-list">
            <?php foreach ($conversations as $conv): ?>
                <?php
                    $nonLu  = (int) $conv['nb_non_lus'];
                    $convId = (int) $conv['id'];
                    $isEditing = (isset($_GET['edit']) && (int)$_GET['edit'] === $convId);
                ?>
                <li class="conv-item <?= $nonLu > 0 ? 'conv-unread' : '' ?>">
                    <a href="showConversation.php?id=<?= $convId ?>" class="conv-link">
                        <div class="conv-avatar"><?= strtoupper(mb_substr($conv['autre_pseudo'], 0, 1)) ?></div>
                        <div class="conv-body">
                            <div class="conv-top">
                                <span class="conv-interlocuteur">
                                    <?= htmlspecialchars($conv['autre_pseudo']) ?>
                                    <small class="conv-categorie">(<?= htmlspecialchars($conv['autre_categorie']) ?>)</small>
                                </span>
                                <span class="conv-date"><?= htmlspecialchars($conv['date_creation']) ?></span>
                            </div>
                            <div class="conv-sujet"><?= htmlspecialchars($conv['sujet']) ?></div>
                        </div>
                        <?php if ($nonLu > 0): ?><span class="badge conv-badge"><?= $nonLu ?></span><?php endif; ?>
                    </a>
                    <?php if ($isEditing): ?>
                        <form method="POST" action="" class="conv-edit-form">
                            <input type="hidden" name="conv_id" value="<?= $convId ?>">
                            <input type="text" name="sujet" value="<?= htmlspecialchars($conv['sujet']) ?>" class="conv-edit-input">
                            <button type="submit" name="modifier_conv" class="msg-btn msg-btn-sm">Sauvegarder</button>
                            <a href="conversations.php" class="msg-btn msg-btn-sm msg-btn-outline">Annuler</a>
                        </form>
                    <?php else: ?>
                        <div class="conv-actions">
                            <a href="conversations.php?edit=<?= $convId ?>" class="msg-btn msg-btn-sm msg-btn-warning">Modifier</a>
                            <a href="conversations.php?supprimer=<?= $convId ?>" class="msg-btn msg-btn-sm msg-btn-danger"
                               onclick="return confirm('Supprimer cette conversation et tous ses messages ?')">Supprimer</a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
