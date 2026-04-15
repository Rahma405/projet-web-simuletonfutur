<?php
session_start();
require_once __DIR__ . '/../../controller/CvC.php';
require_once __DIR__ . '/../../controller/CompetenceC.php';

$ctrl      = new CvC();
$ctrlComp  = new CompetenceC();
$pageTitle = 'Gestion des CV';
$liste     = $ctrl->listCvs();
$totalCvs  = $ctrl->countCvs();
$totalComps= $ctrlComp->countCompetences();

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card red"><div><div class="val"><?= $totalCvs ?></div><div class="lbl">Total CV</div></div><i class="fas fa-file-alt"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card blue"><div><div class="val"><?= $totalComps ?></div><div class="lbl">Compétences</div></div><i class="fas fa-star"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card green"><div><div class="val"><?= $totalCvs > 0 ? round($totalComps/$totalCvs,1) : 0 ?></div><div class="lbl">Comp. / CV</div></div><i class="fas fa-chart-bar"></i></div>
  </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
  <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fas fa-file-alt me-2"></i>Liste des CV</span>
    <a href="add_cv.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">
    <?php if (empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-file-alt fa-3x mb-3 text-muted"></i><p class="text-muted">Aucun CV trouvé.</p></div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr><th>#</th><th>Titre du poste</th><th>Email</th><th>Téléphone</th><th class="text-center">Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($liste as $cv): ?>
        <tr>
          <td style="color:#8899bb;font-size:.78rem"><?= $cv['id'] ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
              <span class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($cv['titre_poste']) ?></span>
            </div>
          </td>
          <td style="font-size:.83rem"><?= htmlspecialchars($cv['email']) ?></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($cv['telephone']) ?></td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <a href="show_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Voir"><i class="fas fa-eye"></i></a>
              <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <small class="text-muted"><?= count($liste) ?> CV trouvé(s)</small>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
