<?php
session_start();
require_once __DIR__ . '/../../controller/CompetenceC.php';
$ctrl      = new CompetenceC();
$pageTitle = 'Gestion des Compétences';
$liste     = $ctrl->listCompetences();
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
    <span><i class="fas fa-star me-2"></i>Liste des Compétences</span>
    <a href="add_competence.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">
    <?php if(empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-star fa-3x mb-3 text-muted"></i><p class="text-muted">Aucune compétence.</p></div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>#</th><th>CV</th><th>Compétence</th><th>Niveau</th><th>Catégorie</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
        <?php foreach($liste as $c):
          $b = match(strtolower($c->getNiveau())) {
            'expert'        => 'badge-expert',
            'avancé'        => 'badge-avance',
            'intermédiaire' => 'badge-intermediaire',
            default         => 'badge-debutant'
          };
        ?>
        <tr>
          <td style="color:#8899bb;font-size:.78rem"><?= $c->getId() ?></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($c->getTitrePoste() ?: '-') ?></td>
          <td class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($c->getNomCompetence()) ?></td>
          <td><span class="<?= $b ?>"><?= htmlspecialchars($c->getNiveau()) ?></span></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($c->getCategorie()) ?></td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <a href="edit_competence.php?id=<?= $c->getId() ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="delete_competence.php?id=<?= $c->getId() ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <small class="text-muted"><?= count($liste) ?> compétence(s)</small>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
