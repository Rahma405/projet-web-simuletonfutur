<?php
session_start();
require_once __DIR__ . '/controller/UtilisateurC.php';
require_once __DIR__ . '/controller/ProfilC.php';

$ctrlU     = new UtilisateurC();
$ctrlP     = new ProfilC();
$stats     = $ctrlU->getStats();
$profils   = $ctrlP->listProfils();
$profilStats = $ctrlP->getCompletionStats();
$pageTitle = stf_t('home');

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/view/frontoffice/layouts/header.php';
?>

<!-- HERO -->
<section style="background:linear-gradient(135deg,#1d2b4f 0%,#0f1a36 60%,#1d2b4f 100%);padding:80px 0 60px;">
  <div class="container text-center text-white">
    <div style="display:inline-block;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#e63946;margin-bottom:18px;">
      <?= htmlspecialchars(stf_t('hero_badge')) ?>
    </div>
    <h1 style="font-size:2.8rem;font-weight:800;line-height:1.2;margin-bottom:14px;">
      <?= htmlspecialchars(stf_t('hero_title_before')) ?> <span style="color:#e63946;"><?= htmlspecialchars(stf_t('hero_title_highlight')) ?></span> <?= htmlspecialchars(stf_t('hero_title_after')) ?>
    </h1>
    <p style="font-size:1rem;color:#8899bb;max-width:560px;margin:0 auto 32px;">
      <?= htmlspecialchars(stf_t('hero_text_1')) ?>
      <?= htmlspecialchars(stf_t('hero_text_2')) ?>
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="view/frontoffice/register.php" class="btn-hero">
        <i class="fas fa-rocket me-2"></i><?= htmlspecialchars(stf_t('start')) ?>
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
        <div style="font-size:.82rem;color:#8899bb"><?= htmlspecialchars(stf_t('users_registered')) ?></div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#f4a261"><?= $stats['admins'] ?></div>
        <div style="font-size:.82rem;color:#8899bb"><?= htmlspecialchars(stf_t('administrators')) ?></div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#2a9d8f"><?= count($profils) ?></div>
        <div style="font-size:.82rem;color:#8899bb"><?= htmlspecialchars(stf_t('profiles_created')) ?></div>
      </div>
    </div>
  </div>
</section>

<section class="container py-5">
  <div class="row g-3">
    <div class="col-md-6 col-xl-3">
      <div class="stat-card red h-100">
        <div>
          <div class="val"><?= $profilStats['average'] ?>%</div>
          <div class="lbl"><?= htmlspecialchars(stf_t('avg_completion')) ?></div>
        </div>
        <i class="fas fa-chart-line"></i>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card blue h-100">
        <div>
          <div class="val"><?= $profilStats['fullCount'] ?></div>
          <div class="lbl"><?= htmlspecialchars(stf_t('full_profiles')) ?></div>
        </div>
        <i class="fas fa-award"></i>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card orange h-100">
        <div>
          <div class="val" style="font-size:1.25rem"><?= htmlspecialchars($profilStats['topCity']) ?></div>
          <div class="lbl"><?= htmlspecialchars(stf_t('top_city')) ?></div>
        </div>
        <i class="fas fa-city"></i>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card green h-100">
        <div>
          <div class="val" style="font-size:1.15rem"><?= htmlspecialchars($profilStats['topLabel']) ?></div>
          <div class="lbl"><?= htmlspecialchars(stf_t('best_profile')) ?></div>
        </div>
        <i class="fas fa-star"></i>
      </div>
    </div>
  </div>
</section>

<!-- PARCOURS -->
<section class="container py-5">
  <h2 class="text-center fw-bold mb-2" style="color:#1d2b4f"><?= htmlspecialchars(stf_t('choose_path')) ?></h2>
  <p class="text-center text-muted mb-4" style="font-size:.88rem"><?= htmlspecialchars(stf_t('path_subtitle')) ?></p>
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
      <h3 class="fw-bold mb-1" style="color:#1d2b4f"><?= htmlspecialchars(stf_t('community')) ?></h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= count($profils) ?> profil(s) cree(s)</p>
    </div>
    <a href="view/frontoffice/register.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
      <i class="fas fa-plus me-1"></i><?= htmlspecialchars(stf_t('join')) ?>
    </a>
  </div>

  <?php if (empty($profils)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-users fa-3x mb-3 text-muted"></i>
      <p class="text-muted"><?= htmlspecialchars(stf_t('no_profiles')) ?></p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($profils as $p): ?>
      <?php
        $photoName = trim((string) ($p['photoProfil'] ?? ''));
        $photoUrl = ($photoName !== '' && $photoName !== 'default.png')
          ? $baseUrl . '/view/assets/img/profiles/' . rawurlencode($photoName)
          : '';
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-center gap-3 mb-2">
            <?php if ($photoUrl !== ''): ?>
              <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo de <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>" class="avatar avatar-photo">
            <?php else: ?>
              <div class="avatar"><?= strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1)) ?></div>
            <?php endif; ?>
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
          <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span style="font-size:.76rem;color:#8899bb"><?= htmlspecialchars($p['completionLabel']) ?></span>
              <span style="font-size:.76rem;font-weight:700;color:#1d2b4f"><?= (int) $p['completion'] ?>%</span>
            </div>
            <div class="completion-track">
              <div class="completion-fill" style="width:<?= (int) $p['completion'] ?>%"></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/view/frontoffice/layouts/footer.php'; ?>
