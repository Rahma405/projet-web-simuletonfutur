<?php
session_start();
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Ajouter un CV';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->add($data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'CV ajouté avec succès !'];
        header('Location: list_cvs.php'); exit;
    }
}
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Ajouter un CV</div>
  <div class="card-body p-4">
    <?php if (!empty($errors['global'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['global']) ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
          <input type="text" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>"
                 placeholder="nom@exemple.com"
                 value="<?= htmlspecialchars($_POST['email']??'') ?>">
          <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Titre du poste <span class="text-danger">*</span></label>
          <input type="text" name="titre_poste" class="form-control <?= isset($errors['titre_poste'])?'is-invalid':'' ?>"
                 placeholder="Ex : Développeur Web"
                 value="<?= htmlspecialchars($_POST['titre_poste']??'') ?>">
          <?php if(isset($errors['titre_poste'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['titre_poste']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Téléphone</label>
          <input type="text" name="telephone" class="form-control <?= isset($errors['telephone'])?'is-invalid':'' ?>"
                 placeholder="Ex : +216 20 000 000"
                 value="<?= htmlspecialchars($_POST['telephone']??'') ?>">
          <?php if(isset($errors['telephone'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['telephone']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date de naissance <small class="text-muted">(YYYY-MM-DD)</small></label>
          <input type="text" name="date_naissance" class="form-control <?= isset($errors['date_naissance'])?'is-invalid':'' ?>"
                 placeholder="2000-01-31"
                 value="<?= htmlspecialchars($_POST['date_naissance']??'') ?>">
          <?php if(isset($errors['date_naissance'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['date_naissance']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Adresse</label>
          <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($_POST['adresse']??'') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">GitHub <small class="text-muted">(URL)</small></label>
          <input type="text" name="github" class="form-control <?= isset($errors['github'])?'is-invalid':'' ?>"
                 placeholder="https://github.com/..."
                 value="<?= htmlspecialchars($_POST['github']??'') ?>">
          <?php if(isset($errors['github'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['github']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">LinkedIn <small class="text-muted">(URL)</small></label>
          <input type="text" name="linkedin" class="form-control <?= isset($errors['linkedin'])?'is-invalid':'' ?>"
                 placeholder="https://linkedin.com/in/..."
                 value="<?= htmlspecialchars($_POST['linkedin']??'') ?>">
          <?php if(isset($errors['linkedin'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['linkedin']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Site web <small class="text-muted">(URL)</small></label>
          <input type="text" name="site_web" class="form-control <?= isset($errors['site_web'])?'is-invalid':'' ?>"
                 placeholder="https://monsite.com"
                 value="<?= htmlspecialchars($_POST['site_web']??'') ?>">
          <?php if(isset($errors['site_web'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['site_web']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_cvs.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
