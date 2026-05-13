<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirection si non connecté
$sessionUser = $_SESSION['user'] ?? null;
if (!$sessionUser) {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $sessionUser['id'];
$userPrenom = $sessionUser['prenom'] ?? '';
$userNom = $sessionUser['nom'] ?? '';
$userPseudo = trim($userPrenom . ' ' . $userNom);

// Compteur de messages non lus
require_once __DIR__ . '/../../../controller/MessageC.php';
$msgCHeader    = new MessageC();
$nbNonLusTotal = $msgCHeader->compterMessagesNonLus($userId);

// Base URL
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = preg_replace('#/view(?:/.*)?$#', '', $scriptDir);
$baseUrl = rtrim($baseUrl, '/');

$stylePath = __DIR__ . '/../../assets/css/style.css';
$styleVersion = file_exists($stylePath) ? filemtime($stylePath) : time();
$msgStylePath = __DIR__ . '/../../assets/css/messagerie.css';
$msgStyleVersion = file_exists($msgStylePath) ? filemtime($msgStylePath) : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messagerie - Simule Ton Futur</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $baseUrl ?>/view/assets/css/style.css?v=<?= $styleVersion ?>" rel="stylesheet">
<link href="<?= $baseUrl ?>/view/assets/css/messagerie.css?v=<?= $msgStyleVersion ?>" rel="stylesheet">
</head>
<body class="messagerie-page">

<nav class="navbar navbar-expand" style="background:var(--bg-secondary);border-bottom:1px solid var(--border);">
  <div class="container">
    <a class="navbar-brand" href="<?= $baseUrl ?>/index.php" style="color:var(--text-white);font-weight:800;">STF<span style="font-weight:400;font-size:0.7em;margin-left:4px;color:var(--text-muted);">messagerie</span></a>
    <div class="navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/index.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-home me-1"></i>Accueil</a></li>
        <li class="nav-item">
          <a class="nav-link" href="conversations.php" style="color:var(--text-muted);font-size:0.85rem;">
            <i class="fas fa-comments me-1"></i>Conversations
            <?php if ($nbNonLusTotal > 0): ?>
              <span class="badge bg-danger" style="font-size:0.65rem;"><?= $nbNonLusTotal ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="newConversation.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-plus me-1"></i>Nouvelle</a></li>
        <li class="nav-item"><a class="nav-link" href="searchMessages.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-search me-1"></i>Recherche</a></li>
        <li class="nav-item"><a class="nav-link" href="chatbot.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-robot me-1"></i>Chatbot</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-chart-bar me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="bloques.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fas fa-ban me-1"></i>Bloqués</a></li>
        <li class="nav-item">
          <span style="color:var(--text-light);font-size:0.85rem;font-weight:600;padding:0 10px;">
            <?= htmlspecialchars($userPseudo) ?>
          </span>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="msg-main">
