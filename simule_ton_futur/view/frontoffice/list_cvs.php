<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Tous les CV';
$cvs       = $ctrl->listCvs();
require_once __DIR__ . '/layouts/header.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">Portfolios</h2>
      <p class="text-muted mb-0" style="font-size:.85rem"><?= count($cvs) ?> CV disponible(s)</p>
    </div>
    <a href="../../index.php" class="btn-outline-hero"><i class="fas fa-arrow-left me-1"></i>Accueil</a>
  </div>
  <?php if(empty($cvs)): ?>
    <div class="card text-center p-5"><i class="fas fa-file-alt fa-3x mb-3 text-muted"></i><p class="text-muted">Aucun CV disponible.</p></div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach($cvs as $cv): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
          <div>
            <div class="fw-bold" style="font-size:.9rem;color:#1d2b4f"><?= htmlspecialchars($cv['titre_poste']) ?></div>
            <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($cv['email']) ?></div>
          </div>
        </div>
        <?php if($cv['description']): ?>
          <p style="font-size:.8rem;color:#666;margin:0 0 14px;"><?= htmlspecialchars(mb_substr($cv['description'],0,90)) ?>…</p>
        <?php endif; ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <?php if($cv['github']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-github me-1" style="color:#e63946"></i>GitHub</span><?php endif; ?>
          <?php if($cv['linkedin']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-linkedin me-1" style="color:#457b9d"></i>LinkedIn</span><?php endif; ?>
          <?php if($cv['telephone']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fas fa-phone me-1" style="color:#2a9d8f"></i><?= htmlspecialchars($cv['telephone']) ?></span><?php endif; ?>
        </div>
        <a href="show_cv.php?id=<?= $cv['id'] ?>" class="btn-hero d-block text-center" style="padding:8px;font-size:.82rem;">
          <i class="fas fa-eye me-1"></i>Voir le profil
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
