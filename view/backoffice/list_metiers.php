<?php
require_once __DIR__ . '/../../controller/MetierC.php';

$ctrl      = new MetierC();
$pageTitle = 'Gestion des Métiers';

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

// Suppression
if (isset($_GET['delete'])) {
    $ctrl->delete((int)$_GET['delete']);
    $_SESSION['message'] = ['type' => 'success', 'texte' => 'Métier supprimé avec succès.'];
    header('Location: list_metiers.php');
    exit;
}

$metiers  = $ctrl->listMetiers();
$secteurs = $ctrl->getAllSecteurs();

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="container py-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f"><i class="fas fa-briefcase me-2" style="color:#e63946"></i>Gestion des Métiers</h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= count($metiers) ?> métier(s) enregistré(s)</p>
    </div>
    <a href="add_metier.php" class="btn-hero"><i class="fas fa-plus me-2"></i>Ajouter un métier</a>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <?php if (empty($metiers)): ?>
  <div class="card text-center p-5">
    <i class="fas fa-briefcase fa-3x mb-3 text-muted"></i>
    <p class="text-muted">Aucun métier enregistré. <a href="add_metier.php">Ajouter le premier</a></p>
  </div>
  <?php else: ?>
  <div class="card" style="overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
      <thead style="background:#f8f9fa">
        <tr>
          <th style="padding:12px 16px;font-size:.8rem;color:#8899bb;font-weight:600;text-align:left">MÉTIER</th>
          <th style="padding:12px 16px;font-size:.8rem;color:#8899bb;font-weight:600;text-align:left">SECTEUR</th>
          <th style="padding:12px 16px;font-size:.8rem;color:#8899bb;font-weight:600;text-align:left">SALAIRE (DT/mois)</th>
          <th style="padding:12px 16px;font-size:.8rem;color:#8899bb;font-weight:600;text-align:center">ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($metiers as $m): ?>
        <tr style="border-top:1px solid #f0f2f8">
          <td style="padding:14px 16px">
            <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?= htmlspecialchars($m->getTitre()) ?></div>
            <?php if ($m->getDescription()): ?>
            <div style="font-size:.75rem;color:#8899bb;margin-top:2px"><?= htmlspecialchars(mb_substr($m->getDescription(), 0, 60)) ?>…</div>
            <?php endif; ?>
          </td>
          <td style="padding:14px 16px">
            <span style="font-size:.75rem;padding:3px 10px;border-radius:20px;background:rgba(69,123,157,.1);color:#457b9d;font-weight:600">
              <?= htmlspecialchars($m->getSecteur()) ?>
            </span>
          </td>
          <td style="padding:14px 16px;font-size:.82rem;color:#2a9d8f;font-weight:600">
            <?php if ($m->getSalaireMin() > 0): ?>
            <?= number_format($m->getSalaireMin(), 0, '', ' ') ?> — <?= number_format($m->getSalaireMax(), 0, '', ' ') ?>
            <?php else: ?>
            <span style="color:#8899bb">N/A</span>
            <?php endif; ?>
          </td>
          <td style="padding:14px 16px;text-align:center">
            <div class="d-flex gap-2 justify-content-center">
              <a href="edit_metier.php?id=<?= $m->getId() ?>" class="btn-hero" style="padding:6px 14px;font-size:.78rem">
                <i class="fas fa-edit"></i>
              </a>
              <a href="?delete=<?= $m->getId() ?>"
                 onclick="return confirm('Supprimer ce métier ?')"
                 class="btn-hero" style="padding:6px 14px;font-size:.78rem;background:linear-gradient(135deg,#e63946,#c1121f)">
                <i class="fas fa-trash"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
