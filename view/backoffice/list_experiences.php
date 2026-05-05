<?php
session_start();
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl      = new ExperienceC();
$pageTitle = 'Gestion des Expériences';
$liste     = $ctrl->listExperiences();
$message   = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<?php if ($message): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
  <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fas fa-history me-2"></i>Liste des Expériences</span>
    <a href="add_experience.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">
    <?php if (empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-history fa-3x mb-3 text-muted"></i><p class="text-muted">Aucune expérience.</p></div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr><th>#</th><th>CV</th><th>Titre</th><th>Entreprise</th><th>Type</th><th>Période</th><th class="text-center">Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($liste as $exp):
          $contratColor = match($exp->getTypeContrat()) {
            'CDI'       => 'badge-expert',
            'CDD'       => 'badge-avance',
            'Stage'     => 'badge-intermediaire',
            default     => 'badge-debutant'
          };
        ?>
        <tr>
          <td style="color:#8899bb;font-size:.78rem"><?= $exp->getId() ?></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($exp->getTitrePoste() ?: '-') ?></td>
          <td class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($exp->getTitre()) ?></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($exp->getEntreprise() ?: '-') ?></td>
          <td><span class="<?= $contratColor ?>"><?= htmlspecialchars($exp->getTypeContrat()) ?></span></td>
          <td style="font-size:.78rem;color:#8899bb">
            <?= date('m/Y', strtotime($exp->getDateDebut())) ?>
            —
            <?= $exp->isEnCours() ? '<span style="color:#2a9d8f;font-weight:600">En cours</span>' : date('m/Y', strtotime($exp->getDateFin())) ?>
          </td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <a href="edit_experience.php?id=<?= $exp->getId() ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="delete_experience.php?id=<?= $exp->getId() ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <small class="text-muted"><?= count($liste) ?> expérience(s)</small>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
