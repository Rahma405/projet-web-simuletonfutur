<?php
session_start();
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Modifier le CV';
$id        = (int)($_GET['id'] ?? 0);
$cv        = $ctrl->getCv($id);
$errors    = [];

if (!$cv) { header('Location: list_cvs.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->edit($id, $data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'CV modifié avec succès !'];
        header('Location: list_cvs.php'); exit;
    }
    // Mettre à jour l'objet avec les nouvelles valeurs pour réafficher
    $cv->setEmail($data['email'] ?? $cv->getEmail());
    $cv->setTitrePoste($data['titre_poste'] ?? $cv->getTitrePoste());
    $cv->setTelephone($data['telephone'] ?? $cv->getTelephone());
    $cv->setDateNaissance($data['date_naissance'] ?? $cv->getDateNaissance());
    $cv->setAdresse($data['adresse'] ?? $cv->getAdresse());
    $cv->setGithub($data['github'] ?? $cv->getGithub());
    $cv->setLinkedin($data['linkedin'] ?? $cv->getLinkedin());
    $cv->setSiteWeb($data['site_web'] ?? $cv->getSiteWeb());
    $cv->setDescription($data['description'] ?? $cv->getDescription());
}
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier le CV #<?= $id ?></div>
  <div class="card-body p-4">
    <?php if (!empty($errors['global'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['global']) ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
          <input type="text" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getEmail()) ?>">
          <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Titre du poste <span class="text-danger">*</span></label>
          <input type="text" name="titre_poste" class="form-control <?= isset($errors['titre_poste'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getTitrePoste()) ?>">
          <?php if(isset($errors['titre_poste'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['titre_poste']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Téléphone</label>
          <input type="text" name="telephone" class="form-control <?= isset($errors['telephone'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getTelephone()) ?>">
          <?php if(isset($errors['telephone'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['telephone']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date de naissance <small class="text-muted">(YYYY-MM-DD)</small></label>
          <input type="text" name="date_naissance" class="form-control <?= isset($errors['date_naissance'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getDateNaissance()) ?>">
          <?php if(isset($errors['date_naissance'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['date_naissance']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Adresse</label>
          <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($cv->getAdresse()) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">GitHub <small class="text-muted">(URL)</small></label>
          <input type="text" name="github" class="form-control <?= isset($errors['github'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getGithub()) ?>">
          <?php if(isset($errors['github'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['github']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">LinkedIn <small class="text-muted">(URL)</small></label>
          <input type="text" name="linkedin" class="form-control <?= isset($errors['linkedin'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getLinkedin()) ?>">
          <?php if(isset($errors['linkedin'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['linkedin']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Site web <small class="text-muted">(URL)</small></label>
          <input type="text" name="site_web" class="form-control <?= isset($errors['site_web'])?'is-invalid':'' ?>"
                 value="<?= htmlspecialchars($cv->getSiteWeb()) ?>">
          <?php if(isset($errors['site_web'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['site_web']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($cv->getDescription()) ?></textarea>
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
