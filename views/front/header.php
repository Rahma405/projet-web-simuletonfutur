<?php
// $base must be set by the including view before requiring this header.
// Fallback: compute it from SCRIPT_NAME if not already set.
if (!isset($base)) {
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base = preg_replace('#/public$#', '', $base);
    $base = rtrim($base, '/');
}
$sessionUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'QuizForge') ?> — STF</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/front.css" rel="stylesheet">
</head>
<body class="front-office">

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= $base ?>/public/index.php">STF <span>QuizForge</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item">
          <a class="nav-link" href="<?= $base ?>/public/index.php?route=/front">
            <i class="fas fa-home me-1"></i>Quizzes
          </a>
        </li>
        <?php if ($sessionUser): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/public/index.php?route=/front/my-results">
              <i class="fas fa-history me-1"></i>My Results
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/public/index.php?route=/account/edit&id=<?= (int)($sessionUser['id'] ?? 0) ?>">
              <i class="fas fa-user-cog me-1"></i>Mon compte
            </a>
          </li>
          <li class="nav-item">
            <span class="nav-user-chip">
              <span class="nav-user-avatar">
                <?= strtoupper(substr($sessionUser['prenom'],0,1).substr($sessionUser['nom'],0,1)) ?>
              </span>
              <span class="nav-user-text"><?= htmlspecialchars($sessionUser['prenom'].' '.$sessionUser['nom']) ?></span>
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-logout" href="<?= $base ?>/public/index.php?route=/logout">
              <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
            </a>
          </li>
          <?php if (($sessionUser['role'] ?? '') === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link admin-link" href="<?= $base ?>/public/index.php?route=/back">
                <i class="fas fa-shield-alt me-1"></i>Admin
              </a>
            </li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/public/index.php?route=/register">
              <i class="fas fa-user-plus me-1"></i>S'inscrire
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>/public/index.php?route=/login">
              <i class="fas fa-sign-in-alt me-1"></i>Connexion
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link admin-link" href="<?= $base ?>/public/index.php?route=/back">
              <i class="fas fa-shield-alt me-1"></i>Admin
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="front-main">
