<?php
session_start();
require_once __DIR__ . '/../../controller/CompetenceC.php';
$ctrl       = new CompetenceC();
$pageTitle  = 'Modifier la Compétence';
$id         = (int)($_GET['id'] ?? 0);
$competence = $ctrl->getCompetence($id);
$cvs        = $ctrl->listCvs();
$errors     = [];
if (!$competence) { header('Location: list_competences.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->edit($id, $data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'Compétence modifiée !'];
        header('Location: list_competences.php'); exit;
    }
    $competence = array_merge($competence, $data);
}
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier la Compétence</div>
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">CV associé *</label>
          <select name="cv_id" class="form-select <?= isset($errors['cv_id'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach($cvs as $cv): $sel=($competence['cv_id']==$cv['id'])?'selected':''; ?>
            <option value="<?= $cv['id'] ?>" <?= $sel ?>><?= htmlspecialchars($cv['titre_poste']) ?> — <?= htmlspecialchars($cv['email']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if(isset($errors['cv_id'])): ?><div class="invalid-feedback"><?= $errors['cv_id'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nom de la compétence *</label>
          <input type="text" name="nom_competence" class="form-control <?= isset($errors['nom_competence'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($competence['nom_competence']) ?>">
          <?php if(isset($errors['nom_competence'])): ?><div class="invalid-feedback"><?= $errors['nom_competence'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Catégorie *</label>
          <input type="text" name="categorie" class="form-control <?= isset($errors['categorie'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($competence['categorie']) ?>">
          <?php if(isset($errors['categorie'])): ?><div class="invalid-feedback"><?= $errors['categorie'] ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label">Niveau *</label>
          <select name="niveau" class="form-select <?= isset($errors['niveau'])?'is-invalid':'' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach(['Débutant','Intermédiaire','Avancé','Expert'] as $n): $sel=($competence['niveau']===$n)?'selected':''; ?>
            <option value="<?= $n ?>" <?= $sel ?>><?= $n ?></option>
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
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
