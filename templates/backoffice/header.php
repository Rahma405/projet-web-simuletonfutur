<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — Gestion Portfolio</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--red:#e63946;--dark:#1d2b4f;--sidebar:#0f1a36;--accent:#f4a261;}
*{font-family:'Poppins',sans-serif;box-sizing:border-box;}
body{background:#eef0f5;margin:0;}
.sidebar{width:260px;min-height:100vh;background:var(--sidebar);position:fixed;left:0;top:0;z-index:999;display:flex;flex-direction:column;box-shadow:4px 0 20px rgba(0,0,0,.4);}
.sidebar-brand{padding:24px 20px 20px;background:linear-gradient(135deg,var(--dark),#162040);border-bottom:1px solid rgba(255,255,255,.07);}
.sidebar-brand h4{color:#fff;font-weight:700;font-size:1.05rem;margin:0;}
.sidebar-brand h4 span{color:var(--red);}
.sidebar-brand small{color:#8899bb;font-size:.72rem;}
.sidebar-section{padding:14px 20px 4px;font-size:.68rem;font-weight:600;color:#556;letter-spacing:.08em;text-transform:uppercase;}
.sidebar nav a{display:flex;align-items:center;gap:11px;padding:12px 22px;color:#8899bb;text-decoration:none;font-size:.86rem;border-left:3px solid transparent;transition:all .2s;}
.sidebar nav a:hover,.sidebar nav a.active{color:#fff;background:rgba(230,57,70,.12);border-left-color:var(--red);}
.sidebar nav a i{width:17px;text-align:center;font-size:.85rem;}
.sidebar-footer{margin-top:auto;padding:14px 22px;border-top:1px solid rgba(255,255,255,.05);color:#445;font-size:.72rem;}
.main-content{margin-left:260px;min-height:100vh;}
.topbar{background:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e0e4ef;box-shadow:0 2px 8px rgba(0,0,0,.05);position:sticky;top:0;z-index:100;}
.topbar h5{margin:0;font-weight:600;color:var(--dark);font-size:.95rem;}
.badge-pdo{background:#e8f4fd;color:#1a6fa8;padding:5px 12px;border-radius:20px;font-size:.75rem;font-weight:500;}
.page-body{padding:26px;}
.card{border:none;border-radius:14px;box-shadow:0 3px 16px rgba(0,0,0,.07);}
.card-header{background:linear-gradient(135deg,var(--dark),#2a3f6f);color:#fff;border-radius:14px 14px 0 0!important;padding:15px 22px;font-weight:600;font-size:.92rem;}
.stat-card{border-radius:14px;padding:18px 20px;color:#fff;border:none;display:flex;justify-content:space-between;align-items:center;}
.stat-card.red   {background:linear-gradient(135deg,#e63946,#c1121f);}
.stat-card.blue  {background:linear-gradient(135deg,#457b9d,#1d3557);}
.stat-card.orange{background:linear-gradient(135deg,#f4a261,#e76f51);}
.stat-card.green {background:linear-gradient(135deg,#2a9d8f,#264653);}
.stat-card .val{font-size:1.9rem;font-weight:700;line-height:1;}
.stat-card .lbl{font-size:.78rem;opacity:.88;margin-top:3px;}
.stat-card i{font-size:1.8rem;opacity:.22;}
.table th{background:#f0f3fa;font-weight:600;color:#4a5568;font-size:.8rem;border:none;}
.table td{vertical-align:middle;font-size:.86rem;}
.avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.78rem;flex-shrink:0;}
.badge-expert{background:#d1fadf;color:#155724;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-avance{background:#e8f0fe;color:#1a56db;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-intermediaire{background:#fff3cd;color:#856404;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-debutant{background:#fde8ea;color:#c1121f;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.form-control,.form-select{border-radius:10px;border:1.5px solid #dde3f0;padding:10px 14px;font-size:.87rem;transition:border-color .2s,box-shadow .2s;}
.form-control:focus,.form-select:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(230,57,70,.1);}
.form-label{font-weight:500;font-size:.83rem;color:#3a4567;margin-bottom:4px;}
.is-invalid{border-color:#e63946!important;}
.invalid-feedback{font-size:.78rem;color:#e63946;display:block;}
.btn-red{background:var(--red);color:#fff;border:none;border-radius:9px;font-weight:500;font-size:.86rem;}
.btn-red:hover{background:#c1121f;color:#fff;}
.alert{border:none;border-radius:10px;font-size:.86rem;}
.alert-success{background:#d1fadf;color:#155724;}
.alert-danger{background:#fde8ea;color:#7b1d1d;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <h4><i class="fas fa-briefcase me-2"></i><span>Gestion</span> Portfolio</h4>
    <small>Back Office — Administration</small>
  </div>
  <div class="sidebar-section">CV</div>
  <nav>
    <a href="../../index.php"><i class="fas fa-globe"></i> Site public</a>
    <a href="../backoffice/list_cvs.php" class="<?= strpos($_SERVER['PHP_SELF'],'cv')!==false?'active':'' ?>">
      <i class="fas fa-file-alt"></i> Liste des CV
    </a>
    <a href="../backoffice/add_cv.php"><i class="fas fa-plus-circle"></i> Ajouter un CV</a>
  </nav>
  <div class="sidebar-section">Compétences</div>
  <nav>
    <a href="../backoffice/list_competences.php" class="<?= strpos($_SERVER['PHP_SELF'],'competence')!==false?'active':'' ?>">
      <i class="fas fa-star"></i> Liste Compétences
    </a>
    <a href="../backoffice/add_competence.php"><i class="fas fa-plus-circle"></i> Ajouter Compétence</a>
  </nav>
  <div class="sidebar-section">Expériences</div>
  <nav>
    <a href="../backoffice/list_experiences.php" class="<?= strpos($_SERVER['PHP_SELF'],'experience')!==false?'active':'' ?>">
      <i class="fas fa-history"></i> Liste Expériences
    </a>
    <a href="../backoffice/add_experience.php"><i class="fas fa-plus-circle"></i> Ajouter Expérience</a>
  </nav>
  <div class="sidebar-section">Métiers</div>
  <nav>
    <a href="../backoffice/list_metiers.php"><i class="fas fa-briefcase"></i> Liste Métiers</a>
    <a href="../backoffice/add_metier.php"><i class="fas fa-plus-circle"></i> Ajouter Métier</a>
  </nav>
  <div class="sidebar-section">Fonctionnalités</div>
  <nav>
    <a href="../frontoffice/recommandation_metiers.php"><i class="fas fa-star" style="color:#e63946"></i> Recommandations</a>
    <a href="../frontoffice/evolution_carriere.php"><i class="fas fa-route" style="color:#2a9d8f"></i> Évolution Carrière</a>
  </nav>
  <div class="sidebar-footer">ESPRIT &middot; UP Web &middot; 2025/2026</div>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="fas fa-shield-alt me-2" style="color:var(--red)"></i><?= htmlspecialchars($pageTitle ?? 'Administration') ?></h5>
    <span class="badge-pdo"><i class="fas fa-database me-1"></i>PDO — MySQL connecté</span>
  </div>
  <div class="page-body">
