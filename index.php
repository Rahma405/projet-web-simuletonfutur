<?php
session_start();
require_once __DIR__ . '/controller/CvC.php';
require_once __DIR__ . '/controller/CompetenceC.php';

$ctrlCv   = new CvC();
$ctrlComp = new CompetenceC();

$cvs        = $ctrlCv->listCvs();
$totalCvs   = $ctrlCv->countCvs();
$totalComps = $ctrlComp->countCompetences();
$pageTitle  = 'Accueil';

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/templates/frontoffice/header.php';
?>

<!-- HERO -->
<section style="background:linear-gradient(135deg,#1d2b4f 0%,#0f1a36 60%,#1d2b4f 100%);padding:80px 0 60px;">
  <div class="container text-center text-white">
    <div style="display:inline-block;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#e63946;margin-bottom:18px;">
      <i class="fas fa-briefcase me-1"></i> Gestion de Portfolio
    </div>
    <h1 style="font-size:2.8rem;font-weight:800;line-height:1.2;margin-bottom:14px;">
      Gérez votre <span style="color:#e63946;">Portfolio</span> Professionnel
    </h1>
    <p style="font-size:1rem;color:#8899bb;max-width:560px;margin:0 auto 32px;">
      Centralisez vos CV et vos compétences en un seul endroit.
      Présentez votre profil de façon moderne et structurée.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="view/frontoffice/list_cvs.php" class="btn-hero">
        <i class="fas fa-eye me-2"></i>Voir les CV
      </a>
      <a href="view/backoffice/list_cvs.php" class="btn-outline-hero" style="border-color:#fff;color:#fff;">
        <i class="fas fa-shield-alt me-2"></i>Administration
      </a>
    </div>
  </div>
</section>

<!-- STATS -->
<section style="background:#1d2b4f;padding:44px 0;">
  <div class="container">
    <div class="row g-3 text-center text-white">
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#e63946"><?= $totalCvs ?></div>
        <div style="font-size:.82rem;color:#8899bb">CV créés</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#f4a261"><?= $totalComps ?></div>
        <div style="font-size:.82rem;color:#8899bb">Compétences</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#2a9d8f"><?= count($cvs) > 0 ? round($totalComps / $totalCvs, 1) : 0 ?></div>
        <div style="font-size:.82rem;color:#8899bb">Comp. / CV</div>
      </div>
    </div>
  </div>
</section>

<!-- CV LIST -->
<section class="container py-5">
  <?php if ($message): ?>
    <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f">Portfolios récents</h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= $totalCvs ?> CV disponible(s)</p>
    </div>
    <a href="view/frontoffice/list_cvs.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
      <i class="fas fa-th me-1"></i>Voir tout
    </a>
  </div>

  <?php if (empty($cvs)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucun CV pour le moment.</p>
      <a href="view/backoffice/add_cv.php" class="btn-hero d-inline-block mx-auto" style="width:fit-content">Ajouter un CV</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach (array_slice($cvs, 0, 6) as $cv): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar"><?= strtoupper(substr($cv['titre_poste'], 0, 2)) ?></div>
            <div>
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?= htmlspecialchars($cv['titre_poste']) ?></div>
              <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($cv['email']) ?></div>
            </div>
          </div>
          <?php if ($cv['description']): ?>
            <p style="font-size:.8rem;color:#666;margin:0 0 12px;"><?= htmlspecialchars(mb_substr($cv['description'], 0, 80)) ?>…</p>
          <?php endif; ?>
          <div class="d-flex gap-2 flex-wrap mb-3">
            <?php if ($cv['github']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fab fa-github me-1" style="color:#e63946"></i>GitHub</span>
            <?php endif; ?>
            <?php if ($cv['linkedin']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fab fa-linkedin me-1" style="color:#457b9d"></i>LinkedIn</span>
            <?php endif; ?>
            <?php if ($cv['telephone']): ?>
              <span style="font-size:.75rem;color:#8899bb"><i class="fas fa-phone me-1" style="color:#2a9d8f"></i><?= htmlspecialchars($cv['telephone']) ?></span>
            <?php endif; ?>
          </div>
          <a href="view/frontoffice/show_cv.php?id=<?= $cv['id'] ?>" class="btn-hero d-block text-center" style="padding:8px;font-size:.82rem;">
            <i class="fas fa-eye me-1"></i>Voir le profil
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/templates/frontoffice/footer.php'; ?>
