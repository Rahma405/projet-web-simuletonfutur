<?php
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = preg_replace('#/view(?:/.*)?$#', '', $scriptDir);
$baseUrl = rtrim($baseUrl, '/');
$sessionUser = $_SESSION['user'] ?? null;
$stylePath = __DIR__ . '/../../assets/css/style.css';
$styleVersion = file_exists($stylePath) ? filemtime($stylePath) : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Accueil') ?> â€” Simule Ton Futur</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $baseUrl ?>/view/assets/css/style.css?v=<?= urlencode((string) $styleVersion) ?>" rel="stylesheet">
</head>
<body class="front-office">
<nav class="navbar navbar-expand">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseUrl ?>/index.php">STF<span>simuletonfutur</span></a>
    <div class="navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/index.php"><i class="fas fa-home me-1"></i>Accueil</a></li>
        <?php if ($sessionUser): ?>
          <li class="nav-item">
            <span class="nav-user-chip">
              <span class="nav-user-avatar">
                <?= htmlspecialchars(strtoupper(substr($sessionUser['prenom'], 0, 1) . substr($sessionUser['nom'], 0, 1))) ?>
              </span>
              <span class="nav-user-text"><?= htmlspecialchars($sessionUser['prenom'] . ' ' . $sessionUser['nom']) ?></span>
            </span>
          </li>
          <li class="nav-item"><a class="nav-link nav-logout" href="<?= $baseUrl ?>/view/frontoffice/logout.php"><i class="fas fa-sign-out-alt me-1"></i>Deconnexion</a></li>
          <?php if (($sessionUser['role'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link admin-link" href="<?= $baseUrl ?>/view/backoffice/list_utilisateurs.php"><i class="fas fa-shield-alt me-1"></i>Admin</a></li>
          <?php endif; ?>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/view/frontoffice/register.php"><i class="fas fa-user-plus me-1"></i>S'inscrire</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/view/frontoffice/login.php"><i class="fas fa-sign-in-alt me-1"></i>Connexion</a></li>
          <li class="nav-item"><a class="nav-link admin-link" href="<?= $baseUrl ?>/view/backoffice/list_utilisateurs.php"><i class="fas fa-shield-alt me-1"></i>Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>  
</nav>

