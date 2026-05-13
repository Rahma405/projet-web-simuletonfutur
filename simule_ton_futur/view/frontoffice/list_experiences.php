<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl      = new ExperienceC();
$pageTitle = 'Expériences Générales';
$liste     = $ctrl->listExperiences();
require_once __DIR__ . '/layouts/header.php';
?>
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f">
        <i class="fas fa-history me-2" style="color:#2a9d8f"></i>Toutes les Expériences
      </h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= count($liste) ?> expérience(s)</p>
    </div>
    <a href="../../index.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;background:#2a9d8f;border-color:#2a9d8f;">
      <i class="fas fa-home me-1"></i>Accueil
    </a>
  </div>

  <?php if (empty($liste)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-history fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucune expérience enregistrée.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($liste as $exp): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-start gap-3 mb-2">
            <div style="width:46px;height:46px;border-radius:12px;background:rgba(42,157,143,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-briefcase" style="color:#2a9d8f;font-size:1.1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?= htmlspecialchars($exp['titre']) ?></div>
              <div style="font-size:.78rem;color:#457b9d;font-weight:600;"><?= htmlspecialchars($exp['entreprise'] ?? '') ?></div>
              <div style="font-size:.73rem;color:#8899bb;"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($exp['lieu'] ?? '') ?></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span style="font-size:.72rem;color:#8899bb;">
              <i class="fas fa-calendar me-1"></i>
              <?= date('m/Y', strtotime($exp['date_debut'])) ?> —
              <?= $exp['en_cours'] ? '<span style="color:#2a9d8f;font-weight:600">En cours</span>' : date('m/Y', strtotime($exp['date_fin'])) ?>
            </span>
            <span style="font-size:.7rem;font-weight:600;padding:2px 10px;border-radius:20px;background:rgba(69,123,157,.1);color:#457b9d;">
              <?= htmlspecialchars($exp['type_contrat']) ?>
            </span>
          </div>
          <?php if ($exp['description']): ?>
            <p style="font-size:.78rem;color:#666;margin:0;"><?= htmlspecialchars(mb_substr($exp['description'], 0, 100)) ?>…</p>
          <?php endif; ?>
          <?php if ($exp['titre_poste']): ?>
            <div class="mt-2" style="font-size:.72rem;color:#8899bb;border-top:1px solid #eee;padding-top:8px;">
              <i class="fas fa-id-card me-1" style="color:#e63946"></i>CV : <?= htmlspecialchars($exp['titre_poste']) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
