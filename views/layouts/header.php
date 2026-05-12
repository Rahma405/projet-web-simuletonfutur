<?php
$base        = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base        = preg_replace('#/public$#', '', $base);
$base        = rtrim($base, '/');
$sessionUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — STF QuizForge</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/style.css" rel="stylesheet">
</head>
<body class="back-office">

<div class="sidebar">
  <a href="<?= $base ?>/public/index.php" class="sidebar-brand">
    <h4>STF <span>QuizForge</span></h4>
    <small>Back Office — Administration</small>
  </a>

  <div class="sidebar-section">Quizzes</div>
  <nav>
    <a href="<?= $base ?>/public/index.php?route=/back"
       class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">
      <i class="fas fa-home"></i> Dashboard
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes/create"
       class="<?= ($activeNav ?? '') === 'create' ? 'active' : '' ?>">
      <i class="fas fa-plus-circle"></i> Create Quiz
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes"
       class="<?= ($activeNav ?? '') === 'list' ? 'active' : '' ?>">
      <i class="fas fa-list"></i> All Quizzes
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes/update-list"
       class="<?= ($activeNav ?? '') === 'update' ? 'active' : '' ?>">
      <i class="fas fa-edit"></i> Edit Quizzes
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes/delete-list"
       class="<?= ($activeNav ?? '') === 'delete' ? 'active' : '' ?>">
      <i class="fas fa-trash"></i> Delete Quizzes
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes/feedback"
       class="<?= ($activeNav ?? '') === 'feedback' ? 'active' : '' ?>">
      <i class="fas fa-comment-dots"></i> Feedback
    </a>
    <a href="<?= $base ?>/public/index.php?route=/quizzes/results"
       class="<?= ($activeNav ?? '') === 'results' ? 'active' : '' ?>">
      <i class="fas fa-chart-bar"></i> Quiz Results
    </a>
  </nav>

  <div class="sidebar-section">Utilisateurs</div>
  <nav>
    <a href="<?= $base ?>/public/index.php?route=/admin/users"
       class="<?= ($activeNav ?? '') === 'admin-users' ? 'active' : '' ?>">
      <i class="fas fa-users"></i> Utilisateurs
    </a>
    <a href="<?= $base ?>/public/index.php?route=/admin/users/add">
      <i class="fas fa-user-plus"></i> Ajouter utilisateur
    </a>
    <a href="<?= $base ?>/public/index.php?route=/admin/profiles"
       class="<?= ($activeNav ?? '') === 'admin-profiles' ? 'active' : '' ?>">
      <i class="fas fa-id-card"></i> Profils
    </a>
    <a href="<?= $base ?>/public/index.php?route=/admin/profiles/add">
      <i class="fas fa-plus-circle"></i> Ajouter profil
    </a>
  </nav>

  <div class="sidebar-section">Front Office</div>
  <nav>
    <a href="<?= $base ?>/public/index.php?route=/front"
       class="<?= ($activeNav ?? '') === 'front-home' ? 'active' : '' ?>">
      <i class="fas fa-globe"></i> Take a Quiz
    </a>
    <a href="<?= $base ?>/public/index.php">
      <i class="fas fa-arrow-left"></i> Home
    </a>
  </nav>

  <div class="sidebar-footer">MVC &middot; OOP &middot; PDO &middot; 2025/2026</div>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="fas fa-shield-alt me-2" style="color:var(--red)"></i><?= htmlspecialchars($pageTitle ?? 'Administration') ?></h5>
    <?php if ($sessionUser): ?>
      <span class="d-flex align-items-center gap-2" style="font-size:.82rem;color:#65748f">
        <div class="avatar" style="width:28px;height:28px;font-size:.7rem">
          <?= strtoupper(substr($sessionUser['prenom'],0,1).substr($sessionUser['nom'],0,1)) ?>
        </div>
        <?= htmlspecialchars($sessionUser['prenom'].' '.$sessionUser['nom']) ?>
      </span>
    <?php endif; ?>
  </div>
  <div class="page-body">

<?php if (!empty($_GET['msg'])): ?>
  <?php $msgs = ['created'=>'Quiz created!','updated'=>'Quiz updated!','deleted'=>'Deleted!']; ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($msgs[$_GET['msg']] ?? 'Done.') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
