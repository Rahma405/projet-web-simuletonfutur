<?php
$pageTitle = 'Not Found – QuizForge';
$activeNav = '';
require __DIR__ . '/../layouts/header.php';
?>

<div class="empty-state empty-state--large">
    <p class="empty-state__code">404</p>
    <p>The quiz you're looking for doesn't exist.</p>
    <a href="index.php?route=/quizzes" class="btn btn--primary">Back to Quizzes</a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
