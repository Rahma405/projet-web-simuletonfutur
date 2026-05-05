<?php
session_start();
require_once __DIR__ . '/../../controller/CompetenceC.php';
$ctrl      = new CompetenceC();
$pageTitle = 'Ajouter une Compétence';
$cvs       = $ctrl->listCvs();
$errors    = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->add($data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'Compétence ajoutée avec succès !'];
        header('Location: list_competences.php'); exit;
    }
}
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Ajouter une Compétence</div>
  <div class="card-body p-4">
    <?php if(!empty($errors['global'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['global']) ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold">CV associé <span class="text-danger">*</span></label>
          <select name="cv_id" class="form-select <?= isset($errors['cv_id'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner un CV --</option>
            <?php foreach($cvs as $cv): ?>
            <option value="<?= $cv->getId() ?>" <?= (($_POST['cv_id']??'')==$cv->getId())?'selected':'' ?>>
              <?= htmlspecialchars($cv->getTitrePoste()) ?> — <?= htmlspecialchars($cv->getEmail()) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['cv_id'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['cv_id']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nom de la compétence <span class="text-danger">*</span></label>
          <input type="text" name="nom_competence"
                 class="form-control <?= isset($errors['nom_competence'])?'is-invalid':'' ?>"
                 placeholder="Ex : PHP, React, Docker..."
                 value="<?= htmlspecialchars($_POST['nom_competence']??'') ?>">
          <?php if(isset($errors['nom_competence'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['nom_competence']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
          <select name="categorie" class="form-select <?= isset($errors['categorie'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach(['Programmation','Framework','Base de données','DevOps','Design','Soft skills','Autre'] as $cat): ?>
            <option value="<?= $cat ?>" <?= (($_POST['categorie']??'')===$cat)?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['categorie'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['categorie']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Niveau <span class="text-danger">*</span></label>
          <select name="niveau" class="form-select <?= isset($errors['niveau'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner un niveau --</option>
            <?php foreach(['Débutant','Intermédiaire','Avancé','Expert'] as $n): ?>
            <option value="<?= $n ?>" <?= (($_POST['niveau']??'')===$n)?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['niveau'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['niveau']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_competences.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
