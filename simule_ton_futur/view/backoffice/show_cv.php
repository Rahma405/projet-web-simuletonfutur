<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Détail du CV';
$id        = (int)($_GET['id'] ?? 0);
$result    = $ctrl->getCvWithCompetences($id);
if (!$result) { header('Location: list_cvs.php'); exit; }
$cv          = $result['cv'];
$competences = $result['competences'];
require_once __DIR__ . '/layouts/header.php';
?>
<div class="d-flex gap-2 mb-4">
  <a href="edit_cv.php?id=<?= $id ?>" class="btn btn-sm btn-red"><i class="fas fa-edit me-1"></i>Modifier</a>
  <a href="list_cvs.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>

<div class="card mb-4">
  <div class="card-header"><i class="fas fa-file-alt me-2"></i>Informations générales</div>
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-md-6"><strong>Titre du poste :</strong> <?= htmlspecialchars($cv['titre_poste']) ?></div>
      <div class="col-md-6"><strong>Email :</strong> <?= htmlspecialchars($cv['email']) ?></div>
      <div class="col-md-6"><strong>Téléphone :</strong> <?= htmlspecialchars($cv['telephone']) ?></div>
      <div class="col-md-6"><strong>Date de naissance :</strong> <?= htmlspecialchars($cv['date_naissance']) ?></div>
      <div class="col-12"><strong>Adresse :</strong> <?= htmlspecialchars($cv['adresse']) ?></div>
      <?php if($cv['github']): ?><div class="col-md-4"><strong>GitHub :</strong> <?= htmlspecialchars($cv['github']) ?></div><?php endif; ?>
      <?php if($cv['linkedin']): ?><div class="col-md-4"><strong>LinkedIn :</strong> <?= htmlspecialchars($cv['linkedin']) ?></div><?php endif; ?>
      <?php if($cv['description']): ?><div class="col-12"><strong>Description :</strong> <?= htmlspecialchars($cv['description']) ?></div><?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fas fa-star me-2"></i>Compétences liées</span>
    <a href="add_competence.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">
    <?php if(empty($competences)): ?>
      <p class="text-muted">Aucune compétence associée.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Compétence</th><th>Niveau</th><th>Catégorie</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
        <?php foreach($competences as $c):
          $b=match(strtolower($c['niveau'])){'expert'=>'badge-expert','avancé'=>'badge-avance','intermédiaire'=>'badge-intermediaire',default=>'badge-debutant'}; ?>
        <tr>
          <td><?= htmlspecialchars($c['nom_competence']) ?></td>
          <td><span class="<?= $b ?>"><?= htmlspecialchars($c['niveau']) ?></span></td>
          <td><?= htmlspecialchars($c['categorie']) ?></td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <a href="edit_competence.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
              <a href="delete_competence.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
