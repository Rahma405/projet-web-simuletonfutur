<?php
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = preg_replace('#/view(?:/.*)?$#', '', $scriptDir);
$baseUrl = rtrim($baseUrl, '/');
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
<link href="<?= $baseUrl ?>/view/assets/css/style.css" rel="stylesheet">
</head>
<body class="front-office">
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseUrl ?>/index.php">STF <span>Simule</span> Ton Futur</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/index.php"><i class="fas fa-home me-1"></i>Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/view/frontoffice/register.php"><i class="fas fa-user-plus me-1"></i>S'inscrire</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/view/frontoffice/login.php"><i class="fas fa-sign-in-alt me-1"></i>Connexion</a></li>
        <li class="nav-item"><a class="nav-link admin-link" href="<?= $baseUrl ?>/view/backoffice/list_utilisateurs.php"><i class="fas fa-shield-alt me-1"></i>Admin</a></li>
      </ul>
    </div>
  </div>  
</nav>

