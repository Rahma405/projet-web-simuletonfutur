<?php
session_start();
require_once __DIR__ . '/../../controller/CandidatureC.php';

$ctrl          = new CandidatureC();
$pageTitle     = 'Candidatures reçues';
$candidatures  = $ctrl->listAll();

require_once __DIR__ . '/layouts/header.php';
?>

<!-- STATS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card green">
      <div><div class="val"><?= count($candidatures) ?></div><div class="lbl">Candidatures reçues</div></div>
      <i class="fas fa-file-alt"></i>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <i class="fas fa-file-alt me-2"></i>Toutes les candidatures
  </div>
  <div class="card-body p-4">

    <?php if (empty($candidatures)): ?>
      <div class="text-center py-5">
        <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
        <p class="text-muted">Aucune candidature reçue pour le moment.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Candidat</th>
              <th>Compétences</th>
              <th>Localisation</th>
              <th>CV</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($candidatures as $c): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $c['idCandidature'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar">
                    <?= strtoupper(substr($c['prenom'], 0, 1) . substr($c['nom'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="fw-semibold" style="font-size:.85rem">
                      <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                    </div>
                    <div style="font-size:.73rem;color:#8899bb">
                      <?= htmlspecialchars($c['email']) ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  <?php foreach (array_map('trim', explode(',', $c['skills'])) as $sk): ?>
                    <span class="badge" style="background:#e6394620;color:#e63946;font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:20px">
                      <?= htmlspecialchars($sk) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </td>
              <td style="font-size:.83rem">
                <i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i>
                <?= htmlspecialchars($c['localisation']) ?>
              </td>
              <td style="font-size:.83rem">
                <?php if ($c['cv']): ?>
                  <i class="fas fa-paperclip me-1" style="color:#f4a261"></i>
                  <?= htmlspecialchars($c['cv']) ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($candidatures) ?> candidature(s)</small>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
