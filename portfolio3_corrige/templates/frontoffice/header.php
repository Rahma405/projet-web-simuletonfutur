<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Accueil') ?> — Gestion Portfolio</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--red:#e63946;--dark:#1d2b4f;--accent:#f4a261;}
*{font-family:'Poppins',sans-serif;box-sizing:border-box;}
body{background:#f0f2f8;margin:0;}
.navbar{background:var(--dark)!important;padding:12px 0;box-shadow:0 2px 20px rgba(0,0,0,.3);}
.navbar-brand{font-weight:800;font-size:1.15rem;color:#fff!important;}
.navbar-brand span{color:var(--red);}
.nav-link{color:#8899bb!important;font-size:.87rem;font-weight:500;transition:color .2s;}
.nav-link:hover{color:#fff!important;}
.nav-link.admin-link{background:rgba(230,57,70,.15);color:var(--red)!important;border-radius:20px;padding:5px 14px!important;}
.card{border:none;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,.08);transition:transform .25s,box-shadow .25s;}
.card:hover{transform:translateY(-4px);box-shadow:0 8px 28px rgba(0,0,0,.12);}
.form-control,.form-select{border-radius:10px;border:1.5px solid #dde3f0;padding:10px 14px;font-size:.9rem;}
.form-control:focus,.form-select:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(230,57,70,.1);}
.form-label{font-weight:500;font-size:.85rem;color:#3a4567;margin-bottom:4px;}
.is-invalid{border-color:#e63946!important;}
.invalid-feedback{font-size:.8rem;color:#e63946;display:block;}
.btn-hero{background:linear-gradient(135deg,var(--red),#c1121f);color:#fff;border:none;border-radius:30px;padding:11px 30px;font-weight:600;font-size:.92rem;box-shadow:0 6px 20px rgba(230,57,70,.3);transition:transform .2s,box-shadow .2s;text-decoration:none;display:inline-block;}
.btn-hero:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(230,57,70,.4);color:#fff;}
.btn-outline-hero{border:2px solid var(--dark);color:var(--dark);border-radius:30px;padding:9px 24px;font-weight:600;background:transparent;text-decoration:none;display:inline-block;font-size:.9rem;}
.btn-outline-hero:hover{background:var(--dark);color:#fff;}
.alert{border:none;border-radius:10px;font-size:.87rem;}
.alert-success{background:#d1fadf;color:#155724;}
.alert-danger{background:#fde8ea;color:#7b1d1d;}
.badge-expert{background:#d1fadf;color:#155724;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-avance{background:#e8f0fe;color:#1a56db;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-intermediaire{background:#fff3cd;color:#856404;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.badge-debutant{background:#fde8ea;color:#c1121f;font-size:.72rem;padding:3px 9px;border-radius:20px;font-weight:600;}
.comp-bar{height:7px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-top:6px;}
.comp-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--red),var(--accent));}
.avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;flex-shrink:0;}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="../../index.php"><i class="fas fa-briefcase me-2"></i><span>Gestion</span> Portfolio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="../../index.php"><i class="fas fa-home me-1"></i>Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="list_cvs.php"><i class="fas fa-file-alt me-1"></i>CV</a></li>
        <li class="nav-item"><a class="nav-link admin-link" href="../backoffice/list_cvs.php"><i class="fas fa-shield-alt me-1"></i>Admin</a></li>
      </ul>
    </div>
  </div>
</nav>
