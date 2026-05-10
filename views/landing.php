<?php
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base = preg_replace('#/public$#', '', $base);
$base = rtrim($base, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuizForge</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/landing.css" rel="stylesheet">
</head>
<body>

<div class="landing">
  <div class="landing__brand">
    <span class="landing__brand-icon"><i class="fas fa-graduation-cap"></i></span>
    <span class="landing__brand-name">Quiz<span>Forge</span></span>
  </div>

  <h1 class="landing__title">Where do you<br>want to go?</h1>
  <p class="landing__sub">Choose your destination below.</p>

  <div class="landing__cards">
    <a href="<?= $base ?>/public/index.php?route=/front" class="portal-card portal-card--front">
      <div class="portal-card__icon"><i class="fas fa-play"></i></div>
      <h2 class="portal-card__title">Front Office</h2>
      <p class="portal-card__desc">Browse available quizzes, pick one, answer the questions and see your score.</p>
      <span class="portal-card__arrow"><i class="fas fa-arrow-right"></i></span>
    </a>
    <a href="<?= $base ?>/public/index.php?route=/back" class="portal-card portal-card--back">
      <div class="portal-card__icon"><i class="fas fa-shield-alt"></i></div>
      <h2 class="portal-card__title">Back Office</h2>
      <p class="portal-card__desc">Create, edit, and delete quizzes and questions. Full admin management panel.</p>
      <span class="portal-card__arrow"><i class="fas fa-arrow-right"></i></span>
    </a>
  </div>

  <p class="landing__stack">MVC &middot; OOP &middot; PDO</p>
</div>

</body>
</html>
