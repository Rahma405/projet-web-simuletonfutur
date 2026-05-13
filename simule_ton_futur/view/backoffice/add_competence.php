<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/CompetenceC.php';
$ctrl      = new CompetenceC();
$pageTitle = 'Ajouter une Compétence';
$cvs       = $ctrl->listCvs();
$errors    = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->add($data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'Compétence ajoutée !'];
        header('Location: list_competences.php'); exit;
    }
}
require_once __DIR__ . '/layouts/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Ajouter une Compétence</div>
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">CV associé *</label>
          <select name="cv_id" class="form-select <?= isset($errors['cv_id'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner un CV --</option>
            <?php foreach($cvs as $cv): ?>
            <option value="<?= $cv['id'] ?>" <?= (($_POST['cv_id']??'')==$cv['id'])?'selected':'' ?>><?= htmlspecialchars($cv['titre_poste']) ?> — <?= htmlspecialchars($cv['email']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['cv_id'])): ?><div class="invalid-feedback"><?= $errors['cv_id'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nom de la compétence *</label>
          <input type="text" name="nom_competence" class="form-control <?= isset($errors['nom_competence'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($_POST['nom_competence']??'') ?>">
          <?php if(isset($errors['nom_competence'])): ?><div class="invalid-feedback"><?= $errors['nom_competence'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Catégorie *</label>
          <input type="text" name="categorie" class="form-control <?= isset($errors['categorie'])?'is-invalid':'' ?>" placeholder="Frontend, Backend, DevOps..." value="<?= htmlspecialchars($_POST['categorie']??'') ?>">
          <?php if(isset($errors['categorie'])): ?><div class="invalid-feedback"><?= $errors['categorie'] ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label">Niveau *</label>
          <select name="niveau" class="form-select <?= isset($errors['niveau'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner un niveau --</option>
            <?php foreach(['Débutant','Intermédiaire','Avancé','Expert'] as $n): ?>
            <option value="<?= $n ?>" <?= (($_POST['niveau']??'')===$n)?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['niveau'])): ?><div class="invalid-feedback"><?= $errors['niveau'] ?></div><?php endif; ?>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_competences.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
