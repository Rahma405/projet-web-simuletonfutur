<?php
session_start();
require_once __DIR__ . '/controller/UtilisateurC.php';
require_once __DIR__ . '/controller/ProfilC.php';

$ctrlU     = new UtilisateurC();
$ctrlP     = new ProfilC();
$stats     = $ctrlU->getStats();
$profils   = $ctrlP->listProfils();
$pageTitle = 'Accueil';

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/view/frontoffice/layouts/header.php';
?>

<!-- HERO -->
<section style="background:linear-gradient(135deg,#1d2b4f 0%,#0f1a36 60%,#1d2b4f 100%);padding:80px 0 60px;">
  <div class="container text-center text-white">
    <div style="display:inline-block;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#e63946;margin-bottom:18px;">
      Plateforme Interactive
    </div>
    <h1 style="font-size:2.8rem;font-weight:800;line-height:1.2;margin-bottom:14px;">
      Simule <span style="color:#e63946;">Ton Futur</span> Professionnel
    </h1>
    <p style="font-size:1rem;color:#8899bb;max-width:560px;margin:0 auto 32px;">
      Decouvre le monde du travail moderne en faisant des choix reels.
      Gagne de l'argent, de l'experience et de la reputation.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="view/frontoffice/register.php" class="btn-hero">
        <i class="fas fa-rocket me-2"></i>Commencer
      </a>
    </div>
  </div>
</section>

<!-- STATS -->
<section style="background:#1d2b4f;padding:44px 0;">
  <div class="container">
    <div class="row g-3 text-center text-white">
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#e63946"><?= $stats['total'] ?></div>
        <div style="font-size:.82rem;color:#8899bb">Utilisateurs inscrits</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#f4a261"><?= $stats['admins'] ?></div>
        <div style="font-size:.82rem;color:#8899bb">Administrateurs</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#2a9d8f"><?= count($profils) ?></div>
        <div style="font-size:.82rem;color:#8899bb">Profils crees</div>
      </div>
    </div>
  </div>
</section>

<!-- PARCOURS -->
<section class="container py-5">
  <h2 class="text-center fw-bold mb-2" style="color:#1d2b4f">Choisis ton parcours</h2>
  <p class="text-center text-muted mb-4" style="font-size:.88rem">Chaque choix donne un resultat different</p>
  <div class="row g-3 justify-content-center">
    <?php foreach([
      ['Freelance','Travaille a ton rythme, gere tes clients.','#e63946'],
      ['Startup','Cree ton entreprise et conquiers le marche.','#f4a261'],
      ['Remote Work','Travaille depuis n\'importe ou dans le monde.','#457b9d'],
      ['Createur','Cree ta communaute et ton contenu.','#2a9d8f'],
      ['Digital Skills','Apprends le code, le design ou le marketing.','#9b59b6'],
    ] as [$title,$desc,$color]): ?>
    <div class="col-md-4 col-lg">
      <div class="card h-100 p-4 text-center" style="border-top:4px solid <?= $color ?>">
        <h6 class="fw-bold mb-1" style="color:<?= $color ?>"><?= $title ?></h6>
        <p class="text-muted mb-0" style="font-size:.8rem"><?= $desc ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PROFILS -->
<section class="container pb-5">
  <?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f">Communaute</h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= count($profils) ?> profil(s) cree(s)</p>
    </div>
    <a href="view/frontoffice/register.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
      <i class="fas fa-plus me-1"></i>Rejoindre
    </a>
  </div>

  <?php if (empty($profils)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-users fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucun profil pour le moment.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($profils as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-center gap-3 mb-2">
            <div class="avatar"><?= strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1)) ?></div>
            <div>
              <div class="fw-bold" style="font-size:.88rem"><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></div>
              <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($p['email']) ?></div>
            </div>
            <span class="ms-auto badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span>
          </div>
          <?php if ($p['bio']): ?>
            <p style="font-size:.8rem;color:#666;margin:8px 0 6px;"><?= htmlspecialchars(mb_substr($p['bio'],0,80)).'...' ?></p>
          <?php endif; ?>
          <div class="d-flex gap-2 flex-wrap mt-1">
            <?php if ($p['ville']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i><?= htmlspecialchars($p['ville']) ?></span>
            <?php endif; ?>
            <?php if ($p['pays']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fas fa-globe me-1" style="color:#457b9d"></i><?= htmlspecialchars($p['pays']) ?></span>
            <?php endif; ?>
            <?php if ($p['langue']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fas fa-language me-1" style="color:#2a9d8f"></i><?= htmlspecialchars($p['langue']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/view/frontoffice/layouts/footer.php'; ?>
