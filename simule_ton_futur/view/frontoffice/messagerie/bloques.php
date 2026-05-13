<?php
require_once 'header_messagerie.php';
require_once __DIR__ . '/../../../controller/BlocageC.php';

$blocC = new BlocageC();

if (isset($_GET['debloquer'])) {
    $debId = filter_var($_GET['debloquer'], FILTER_VALIDATE_INT);
    if ($debId) { $blocC->debloquer($userId, $debId); header('Location: bloques.php'); exit; }
}

$bloques = $blocC->listerBloques($userId);
?>

<div class="msg-page">
    <div class="msg-page-header"><h1>Utilisateurs bloqués</h1></div>

    <?php if (empty($bloques)): ?>
        <p class="msg-info">Vous n'avez bloqué aucun utilisateur.</p>
    <?php else: ?>
        <ul class="conv-list">
            <?php foreach ($bloques as $user): ?>
                <li class="conv-item">
                    <div class="conv-link" style="cursor:default;">
                        <div class="conv-avatar" style="background:linear-gradient(135deg,#ef4444,#f87171);"><?= strtoupper(mb_substr($user['pseudo'], 0, 1)) ?></div>
                        <div class="conv-body">
                            <div class="conv-top">
                                <span class="conv-interlocuteur"><?= htmlspecialchars($user['pseudo']) ?> <small class="conv-categorie">(<?= htmlspecialchars($user['categorie']) ?>)</small></span>
                                <span class="conv-date">Bloqué le <?= htmlspecialchars($user['date_blocage']) ?></span>
                            </div>
                            <div class="conv-sujet"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <a href="bloques.php?debloquer=<?= (int)$user['id'] ?>" class="msg-btn msg-btn-sm msg-btn-warning" onclick="return confirm('Débloquer cet utilisateur ?')">Débloquer</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
