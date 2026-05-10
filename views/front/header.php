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
<title><?= htmlspecialchars($pageTitle ?? 'QuizForge') ?> — QuizForge</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/front.css" rel="stylesheet">
</head>
<body class="front-office">

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= $base ?>/public/index.php">Quiz<span>Forge</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>/public/index.php?route=/front"><i class="fas fa-home me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link admin-link" href="<?= $base ?>/public/index.php?route=/back"><i class="fas fa-shield-alt me-1"></i>Admin</a></li>
      </ul>
    </div>
  </div>
</nav>
