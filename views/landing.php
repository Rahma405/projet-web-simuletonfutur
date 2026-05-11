<?php
$base        = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base        = preg_replace('#/public$#', '', $base);
$base        = rtrim($base, '/');
$sessionUser = $_SESSION['user'] ?? null;

// If not logged in → show login/register page only
if (!$sessionUser) {
    require_once __DIR__ . '/../controllers/UtilisateurC.php';
    $ctrlU = new UtilisateurC();
    $stats = $ctrlU->getStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Simule Ton Futur</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/front.css" rel="stylesheet">
<style>
.auth-split { min-height:100vh; display:flex; }
.auth-left  { width:55%; background:linear-gradient(135deg,#0f1a36 0%,#1d2b4f 60%,#162040 100%); display:flex; flex-direction:column; justify-content:center; padding:60px 70px; position:relative; overflow:hidden; }
.auth-right { width:45%; display:flex; align-items:center; justify-content:center; padding:40px; background:#f5f7ff; }
.auth-blob  { position:absolute; border-radius:50%; filter:blur(80px); opacity:.15; pointer-events:none; }
.auth-card  { width:100%; max-width:420px; background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(29,43,79,.12); padding:44px 40px; }
.auth-tab   { display:flex; gap:4px; background:#f0f3fa; border-radius:10px; padding:4px; margin-bottom:28px; }
.auth-tab button { flex:1; border:none; border-radius:8px; padding:10px; font-family:'Poppins',sans-serif; font-size:.88rem; font-weight:600; cursor:pointer; transition:.2s; background:transparent; color:#65748f; }
.auth-tab button.active { background:#fff; color:#1d2b4f; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.auth-form  { display:none; }
.auth-form.show { display:block; }
@media(max-width:768px){ .auth-split{flex-direction:column;} .auth-left,.auth-right{width:100%;} .auth-left{padding:40px 24px;min-height:auto;} }
</style>
</head>
<body style="margin:0;font-family:'Poppins',sans-serif">

<div class="auth-split">
  <!-- Left: branding + stats -->
  <div class="auth-left">
    <div class="auth-blob" style="width:400px;height:400px;background:#e63946;top:-150px;left:-100px"></div>
    <div class="auth-blob" style="width:300px;height:300px;background:#4fffb0;bottom:-100px;right:-80px"></div>

    <div style="position:relative;z-index:1">
      <div style="display:inline-flex;align-items:center;gap:10px;font-size:1.1rem;font-weight:800;color:#fff;margin-bottom:48px">
        <span style="color:#e63946;font-size:1.5rem">◈</span> STF <span style="color:#e63946">QuizForge</span>
      </div>

      <h1 style="font-size:2.6rem;font-weight:800;color:#fff;line-height:1.15;letter-spacing:-.02em;margin-bottom:16px">
        Simule<br><span style="color:#e63946">Ton Futur</span><br>Professionnel
      </h1>
      <p style="color:#8899bb;font-size:1rem;margin-bottom:48px;max-width:380px;line-height:1.7">
        Découvre le monde du travail moderne, teste tes connaissances et suis ta progression.
      </p>

      <!-- Stats -->
      <div style="display:flex;gap:32px">
        <div>
          <div style="font-size:1.8rem;font-weight:800;color:#e63946"><?= $stats['total'] ?? '0' ?></div>
          <div style="font-size:.78rem;color:#8899bb">Utilisateurs</div>
        </div>
        <div style="width:1px;background:rgba(255,255,255,.1)"></div>
        <div>
          <div style="font-size:1.8rem;font-weight:800;color:#f4a261"><?= $stats['admins'] ?? '0' ?></div>
          <div style="font-size:.78rem;color:#8899bb">Administrateurs</div>
        </div>
        <div style="width:1px;background:rgba(255,255,255,.1)"></div>
        <div>
          <div style="font-size:1.8rem;font-weight:800;color:#4fffb0">100%</div>
          <div style="font-size:.78rem;color:#8899bb">Gratuit</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: login / register tabs -->
  <div class="auth-right">
    <div class="auth-card">
      <div style="text-align:center;margin-bottom:24px">
        <h2 style="font-size:1.5rem;font-weight:800;color:#1d2b4f;margin-bottom:4px">Bienvenue</h2>
        <p style="color:#8899bb;font-size:.88rem;margin:0">Connecte-toi ou crée ton compte</p>
      </div>

      <!-- Tabs -->
      <div class="auth-tab">
        <button class="active" id="tabLogin" onclick="switchTab('login')">
          <i class="fas fa-sign-in-alt me-1"></i>Connexion
        </button>
        <button id="tabRegister" onclick="switchTab('register')">
          <i class="fas fa-user-plus me-1"></i>Inscription
        </button>
      </div>

      <!-- Login form -->
      <div class="auth-form show" id="formLogin">
        <p style="text-align:center;font-size:.84rem;color:#8899bb;margin-bottom:20px">
          Accéder à votre espace personnel
        </p>
        <a href="<?= $base ?>/public/index.php?route=/login"
           style="display:flex;align-items:center;justify-content:center;gap:10px;background:linear-gradient(135deg,#e63946,#c1121f);color:#fff;border-radius:14px;padding:14px;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:0 10px 28px rgba(230,57,70,.25);transition:.2s"
           onmouseover="this.style.transform='translateY(-2px)'"
           onmouseout="this.style.transform='translateY(0)'">
          <i class="fas fa-sign-in-alt"></i> Connexion classique
        </a>
        <div style="display:flex;align-items:center;gap:12px;margin:16px 0">
          <div style="flex:1;height:1px;background:#e8edf5"></div>
          <span style="font-size:.78rem;color:#8899bb">ou</span>
          <div style="flex:1;height:1px;background:#e8edf5"></div>
        </div>
        <a href="<?= $base ?>/public/index.php?route=/face-login"
           style="display:flex;align-items:center;justify-content:center;gap:10px;background:#f0f3fa;color:#1d2b4f;border-radius:14px;padding:13px;font-weight:600;font-size:.88rem;text-decoration:none;border:1.5px solid #dde3f0">
          <i class="fas fa-camera" style="color:#e63946"></i> Face ID
        </a>
      </div>

      <!-- Register form -->
      <div class="auth-form" id="formRegister">
        <p style="text-align:center;font-size:.84rem;color:#8899bb;margin-bottom:20px">
          Rejoins la communauté STF
        </p>
        <a href="<?= $base ?>/public/index.php?route=/register"
           style="display:flex;align-items:center;justify-content:center;gap:10px;background:linear-gradient(135deg,#457b9d,#1d3557);color:#fff;border-radius:14px;padding:14px;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:0 10px 28px rgba(69,123,157,.25);transition:.2s"
           onmouseover="this.style.transform='translateY(-2px)'"
           onmouseout="this.style.transform='translateY(0)'">
          <i class="fas fa-rocket"></i> Créer mon compte
        </a>
      </div>

      <p style="text-align:center;margin-top:20px;font-size:.78rem;color:#8899bb">
        Admin? <a href="<?= $base ?>/public/index.php?route=/back" style="color:#e63946;font-weight:600">Back office →</a>
      </p>
    </div>
  </div>
</div>

<script>
function switchTab(tab) {
  document.getElementById('formLogin').classList.remove('show');
  document.getElementById('formRegister').classList.remove('show');
  document.getElementById('tabLogin').classList.remove('active');
  document.getElementById('tabRegister').classList.remove('active');
  document.getElementById('form' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('show');
  document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
}
</script>
</body>
</html>
<?php
  // Stop here for guests
  return;
}

// ── Logged-in: full site with sidebar ─────────────────────────────────────────
require_once __DIR__ . '/../controllers/UtilisateurC.php';
require_once __DIR__ . '/../controllers/ProfilC.php';
require_once __DIR__ . '/../models/FrontModel.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../config/Database.php';

$ctrlU      = new UtilisateurC();
$ctrlP      = new ProfilC();
$frontModel = new FrontModel();
$stats      = $ctrlU->getStats();
$profils    = $ctrlP->listProfils();
$quizzes    = $frontModel->getAllQuizzesWithCount();
$myResults  = $frontModel->getResultsByUser((int)($sessionUser['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Simule Ton Futur — Accueil</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/public/css/front.css" rel="stylesheet">
<style>
.stf-shell   { display:flex; min-height:100vh; }
.stf-sidebar { width:280px; background:#1d2b4f; position:fixed; top:0; left:0; bottom:0; z-index:200; display:flex; flex-direction:column; overflow-y:auto; }
.stf-main    { margin-left:280px; flex:1; }
.stf-topbar  { background:#fff; border-bottom:1px solid #e0e4ef; padding:0 28px; height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.stf-content { padding:28px; }
.sb-brand    { padding:22px 20px 18px; border-bottom:1px solid rgba(255,255,255,.08); text-decoration:none; display:block; }
.sb-section  { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#4a5a7a; padding:16px 20px 6px; }
.sb-link     { display:flex; align-items:center; gap:10px; padding:10px 20px; color:#8899bb; text-decoration:none; font-size:.86rem; font-weight:500; border-left:3px solid transparent; transition:.2s; }
.sb-link:hover,.sb-link.active { color:#fff; background:rgba(230,57,70,.1); border-left-color:#e63946; }
.sb-link i   { width:18px; text-align:center; }
.sb-quiz-item { display:flex; align-items:center; justify-content:space-between; padding:9px 20px 9px 32px; color:#8899bb; text-decoration:none; font-size:.83rem; border-left:3px solid transparent; transition:.2s; }
.sb-quiz-item:hover { color:#fff; background:rgba(230,57,70,.08); border-left-color:rgba(230,57,70,.4); }
.sb-footer   { margin-top:auto; padding:14px 20px; border-top:1px solid rgba(255,255,255,.06); font-size:.72rem; color:#3a4a6a; }
.stat-box    { background:#fff; border-radius:14px; padding:20px; box-shadow:0 3px 14px rgba(0,0,0,.06); border-left:4px solid; }
</style>
</head>
<body style="margin:0;font-family:'Poppins',sans-serif;background:#f0f2f8">

<div class="stf-shell">

  <!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
  <aside class="stf-sidebar">

    <a href="<?= $base ?>/public/index.php" class="sb-brand">
      <div style="font-size:1.05rem;font-weight:800;color:#fff">STF <span style="color:#e63946">QuizForge</span></div>
      <div style="font-size:.72rem;color:#4a5a7a;margin-top:2px">Simule Ton Futur</div>
    </a>

    <!-- User chip -->
    <div style="padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.06)">
      <div style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.05);border-radius:10px;padding:10px 12px">
        <div class="avatar" style="width:34px;height:34px;font-size:.75rem;flex-shrink:0">
          <?= strtoupper(substr($sessionUser['prenom'],0,1).substr($sessionUser['nom'],0,1)) ?>
        </div>
        <div style="min-width:0">
          <div style="font-size:.84rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= htmlspecialchars($sessionUser['prenom'].' '.$sessionUser['nom']) ?>
          </div>
          <div style="font-size:.72rem;color:#4a5a7a"><?= htmlspecialchars($sessionUser['role']) ?></div>
        </div>
      </div>
    </div>

    <!-- Main nav -->
    <div class="sb-section">Navigation</div>
    <nav>
      <a href="<?= $base ?>/public/index.php" class="sb-link active">
        <i class="fas fa-home"></i> Accueil
      </a>
      <a href="<?= $base ?>/public/index.php?route=/account/edit&id=<?= (int)($sessionUser['id'] ?? 0) ?>" class="sb-link">
        <i class="fas fa-user"></i> Mon Profil
      </a>
      <a href="<?= $base ?>/public/index.php?route=/front/my-results" class="sb-link">
        <i class="fas fa-history"></i> Mes Résultats
        <?php if (count($myResults) > 0): ?>
          <span style="margin-left:auto;background:#e63946;color:#fff;border-radius:20px;font-size:.68rem;padding:2px 7px;font-weight:700">
            <?= count($myResults) ?>
          </span>
        <?php endif; ?>
      </a>
    </nav>

    <!-- Quizzes section -->
    <div class="sb-section" style="margin-top:8px">
      <i class="fas fa-layer-group me-1"></i> Quizzes
    </div>

    <?php if (empty($quizzes)): ?>
      <div style="padding:8px 20px;font-size:.8rem;color:#3a4a6a;font-style:italic">Aucun quiz disponible.</div>
    <?php else: ?>
      <?php foreach ($quizzes as $q): ?>
        <a href="<?= $base ?>/public/index.php?route=/front/play&id=<?= $q['id'] ?>"
           class="sb-quiz-item">
          <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px">
            <?= htmlspecialchars($q['type']) ?>
          </span>
          <span style="background:rgba(230,57,70,.15);color:#e63946;border-radius:20px;font-size:.7rem;padding:2px 8px;font-weight:600;white-space:nowrap;flex-shrink:0">
            <?= (int)$q['question_count'] ?> Q
          </span>
        </a>
      <?php endforeach; ?>
      <a href="<?= $base ?>/public/index.php?route=/front" class="sb-link" style="font-size:.8rem;color:#4a5a7a;padding:8px 20px">
        <i class="fas fa-grid"></i> Voir tous les quizzes
      </a>
    <?php endif; ?>

    <?php if (($sessionUser['role'] ?? '') === 'admin'): ?>
      <div class="sb-section" style="margin-top:8px">Administration</div>
      <nav>
        <a href="<?= $base ?>/public/index.php?route=/back" class="sb-link">
          <i class="fas fa-shield-alt"></i> Back Office
        </a>
        <a href="<?= $base ?>/public/index.php?route=/admin/users" class="sb-link">
          <i class="fas fa-users"></i> Utilisateurs
        </a>
        <a href="<?= $base ?>/public/index.php?route=/quizzes/results" class="sb-link">
          <i class="fas fa-chart-bar"></i> Résultats
        </a>
      </nav>
    <?php endif; ?>

    <div class="sb-footer">MVC · OOP · PDO · 2025/2026</div>
    <a href="<?= $base ?>/public/index.php?route=/logout" style="display:flex;align-items:center;gap:8px;padding:12px 20px;color:#ff6b7a;text-decoration:none;font-size:.84rem;font-weight:600;border-top:1px solid rgba(255,255,255,.06)">
      <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
  </aside>

  <!-- ── Main ─────────────────────────────────────────────────────────────── -->
  <div class="stf-main">

    <!-- Topbar -->
    <div class="stf-topbar">
      <div style="font-size:.95rem;font-weight:600;color:#1d2b4f">
        Bonjour, <span style="color:#e63946"><?= htmlspecialchars($sessionUser['prenom']) ?></span> 👋
      </div>
      <div class="d-flex align-items-center gap-3">
        <span style="font-size:.78rem;color:#8899bb">
          <i class="fas fa-calendar me-1"></i><?= date('d/m/Y') ?>
        </span>
        <a href="<?= $base ?>/public/index.php?route=/account/edit&id=<?= (int)($sessionUser['id'] ?? 0) ?>"
           style="text-decoration:none">
          <div class="avatar" style="width:32px;height:32px;font-size:.72rem">
            <?= strtoupper(substr($sessionUser['prenom'],0,1).substr($sessionUser['nom'],0,1)) ?>
          </div>
        </a>
      </div>
    </div>

    <div class="stf-content">

      <!-- Stats row -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-box" style="border-color:#e63946">
            <div style="font-size:1.7rem;font-weight:800;color:#e63946"><?= $stats['total'] ?? 0 ?></div>
            <div style="font-size:.78rem;color:#8899bb">Utilisateurs</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box" style="border-color:#457b9d">
            <div style="font-size:1.7rem;font-weight:800;color:#457b9d"><?= count($profils) ?></div>
            <div style="font-size:.78rem;color:#8899bb">Profils</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box" style="border-color:#2a9d8f">
            <div style="font-size:1.7rem;font-weight:800;color:#2a9d8f"><?= count($quizzes) ?></div>
            <div style="font-size:.78rem;color:#8899bb">Quizzes</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box" style="border-color:#f4a261">
            <div style="font-size:1.7rem;font-weight:800;color:#f4a261"><?= count($myResults) ?></div>
            <div style="font-size:.78rem;color:#8899bb">Mes résultats</div>
          </div>
        </div>
      </div>

      <!-- Quick quiz pick -->
      <?php if (!empty($quizzes)): ?>
      <div class="card mb-4" style="border:none;border-radius:14px;box-shadow:0 3px 14px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;border-radius:14px 14px 0 0;padding:15px 22px;font-weight:600">
          <i class="fas fa-layer-group me-2"></i>Choisir un Quiz
          <span class="badge ms-2" style="background:rgba(230,57,70,.2);color:#ff8a9a;font-size:.75rem"><?= count($quizzes) ?> disponibles</span>
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <?php foreach (array_slice($quizzes, 0, 6) as $q): ?>
              <div class="col-md-6 col-lg-4">
                <a href="<?= $base ?>/public/index.php?route=/front/play&id=<?= $q['id'] ?>"
                   style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#f8f9fc;border:1.5px solid #dde3f0;border-radius:10px;text-decoration:none;transition:.2s"
                   onmouseover="this.style.borderColor='#e63946';this.style.background='#fde8ea'"
                   onmouseout="this.style.borderColor='#dde3f0';this.style.background='#f8f9fc'">
                  <div>
                    <div style="font-weight:600;font-size:.88rem;color:#1d2b4f"><?= htmlspecialchars($q['type']) ?></div>
                    <div style="font-size:.75rem;color:#8899bb"><?= (int)$q['question_count'] ?> question<?= $q['question_count'] != 1 ? 's' : '' ?></div>
                  </div>
                  <span style="background:#fde8ea;color:#e63946;border-radius:20px;padding:5px 10px;font-size:.78rem;font-weight:700;white-space:nowrap">
                    <i class="fas fa-play me-1"></i>Start
                  </span>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (count($quizzes) > 6): ?>
            <div class="text-center mt-3">
              <a href="<?= $base ?>/public/index.php?route=/front"
                 style="color:#e63946;font-size:.85rem;font-weight:600;text-decoration:none">
                Voir tous les <?= count($quizzes) ?> quizzes →
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- My last results -->
      <?php if (!empty($myResults)): ?>
      <div class="card mb-4" style="border:none;border-radius:14px;box-shadow:0 3px 14px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;border-radius:14px 14px 0 0;padding:15px 22px;font-weight:600">
          <i class="fas fa-history me-2"></i>Mes derniers résultats
          <a href="<?= $base ?>/public/index.php?route=/front/my-results"
             style="float:right;font-size:.78rem;color:#8899bb;font-weight:400;text-decoration:none">
            Voir tout →
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.84rem">
              <thead><tr>
                <th style="padding:12px 18px">Quiz</th>
                <th class="text-center">Score</th>
                <th class="text-center">Résultat</th>
                <th>Date</th>
              </tr></thead>
              <tbody>
                <?php foreach (array_slice($myResults, 0, 5) as $r):
                  $pct = $r['total'] > 0 ? round(($r['score']/$r['total'])*100) : 0;
                  if ($pct >= 70)     { $bc='#d1fadf'; $fc='#16a34a'; $lbl='Pass'; }
                  elseif ($pct >= 40) { $bc='#fef3c7'; $fc='#d97706'; $lbl='Moyen'; }
                  else                { $bc='#fde8ea'; $fc='#e63946'; $lbl='Fail'; }
                ?>
                <tr>
                  <td style="padding:12px 18px;font-weight:600;color:#1d2b4f"><?= htmlspecialchars($r['quiz_type']) ?></td>
                  <td class="text-center"><strong style="color:#1d2b4f"><?= $pct ?>%</strong> <span style="color:#8899bb;font-size:.78rem">(<?= $r['score'] ?>/<?= $r['total'] ?>)</span></td>
                  <td class="text-center">
                    <span style="background:<?= $bc ?>;color:<?= $fc ?>;border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:700"><?= $lbl ?></span>
                  </td>
                  <td style="color:#8899bb;font-size:.8rem"><?= date('d/m/Y H:i', strtotime($r['passed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Community -->
      <?php if (!empty($profils)): ?>
      <div class="card" style="border:none;border-radius:14px;box-shadow:0 3px 14px rgba(0,0,0,.07)">
        <div class="card-header" style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;border-radius:14px 14px 0 0;padding:15px 22px;font-weight:600">
          <i class="fas fa-users me-2"></i>Communauté
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <?php foreach (array_slice($profils, 0, 6) as $p): ?>
              <div class="col-md-6 col-lg-4">
                <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fc;border-radius:10px;border:1px solid #dde3f0">
                  <div class="avatar" style="width:36px;height:36px;font-size:.78rem;flex-shrink:0">
                    <?= strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1)) ?>
                  </div>
                  <div style="min-width:0">
                    <div style="font-weight:600;font-size:.86rem;color:#1d2b4f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                      <?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?>
                    </div>
                    <div style="font-size:.75rem;color:#8899bb">
                      <?= $p['ville'] ? '<i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i>'.htmlspecialchars($p['ville']) : htmlspecialchars($p['email']) ?>
                    </div>
                  </div>
                  <span class="ms-auto badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /.stf-content -->
  </div><!-- /.stf-main -->
</div><!-- /.stf-shell -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
