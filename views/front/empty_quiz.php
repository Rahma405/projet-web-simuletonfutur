<?php
$pageTitle = 'No Questions – QuizForge';
require __DIR__ . '/header.php';
?>
<div class="front-empty">
    <p><strong><?= htmlspecialchars($quiz['type']) ?></strong> has no questions yet.</p>
    <a href="index.php?route=/front" class="fo-btn fo-btn--primary">← Back to Quizzes</a>
</div>
<?php require __DIR__ . '/footer.php'; ?>
