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
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — Simule Ton Futur</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= $baseUrl ?>/view/assets/css/style.css" rel="stylesheet">
</head>

<body class="back-office">

  <div class="sidebar">
    <div class="sidebar-brand">
      <h4>STF</h4>
      <small>Back Office - Administration</small>
    </div>
    <div class="sidebar-section">Gestion</div>
    <nav>
      <a href="<?= $baseUrl ?>/index.php"><i class="fas fa-globe"></i> Site public</a>
      <a href="<?= $baseUrl ?>/view/backoffice/list_utilisateurs.php"
        class="<?= strpos($_SERVER['PHP_SELF'], 'utilisateur') !== false ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Utilisateurs
      </a>
      <a href="<?= $baseUrl ?>/view/backoffice/add_utilisateur.php"><i class="fas fa-user-plus"></i> Ajouter
        utilisateur</a>
    </nav>
    <div class="sidebar-section">Profils</div>
    <nav>
      <a href="<?= $baseUrl ?>/view/backoffice/list_profils.php"
        class="<?= strpos($_SERVER['PHP_SELF'], 'profil') !== false ? 'active' : '' ?>">
        <i class="fas fa-id-card"></i> Profils
      </a>
      <a href="<?= $baseUrl ?>/view/backoffice/add_profil.php"><i class="fas fa-plus-circle"></i> Ajouter profil</a>
    </nav>
    <div class="sidebar-section">Offres</div>
    <nav>
      <a href="<?= $baseUrl ?>/view/backoffice/list_offres.php"
        class="<?= strpos($_SERVER['PHP_SELF'], 'offre') !== false ? 'active' : '' ?>">
        <i class="fas fa-briefcase"></i> Offres
      </a>
      <a href="<?= $baseUrl ?>/view/backoffice/add_offre.php"><i class="fas fa-plus-circle"></i> Ajouter offre</a>
    </nav>
    <div class="sidebar-section">Candidatures</div>
    <nav>
      <a href="<?= $baseUrl ?>/view/backoffice/list_candidatures.php"
        class="<?= strpos($_SERVER['PHP_SELF'], 'candidature') !== false ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i> Candidatures
      </a>
    </nav>
    <div class="sidebar-footer">ESPRIT &middot; UP Web &middot; 2025/2026</div>
  </div>

  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-shield-alt me-2"
          style="color:var(--red)"></i><?= htmlspecialchars($pageTitle ?? 'Administration') ?></h5>
    </div>
    <div class="page-body">