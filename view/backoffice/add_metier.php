<?php
require_once __DIR__ . '/../../controller/MetierC.php';

$ctrl      = new MetierC();
$pageTitle = 'Ajouter un Métier';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $ctrl->add($_POST);
    if (empty($errors)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => 'Métier ajouté avec succès.'];
        header('Location: list_metiers.php');
        exit;
    }
}

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="container py-5" style="max-width:700px">
  <a href="list_metiers.php" style="font-size:.85rem;color:#8899bb;text-decoration:none;display:flex;align-items:center;gap:6px;margin-bottom:24px">
    <i class="fas fa-arrow-left"></i> Retour aux métiers
  </a>

  <div class="card p-4">
    <h4 class="fw-bold mb-4" style="color:#1d2b4f"><i class="fas fa-plus-circle me-2" style="color:#e63946"></i>Ajouter un métier</h4>

    <form method="POST" action="">

      <div class="mb-3">
        <label class="form-label">Titre du métier *</label>
        <input type="text" name="titre" class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" placeholder="ex: Développeur Backend Senior">
        <?php if (isset($errors['titre'])): ?><div class="invalid-feedback"><?= $errors['titre'] ?></div><?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"
                  placeholder="Description du métier..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Secteur *</label>
        <input type="text" name="secteur" class="form-control <?= isset($errors['secteur']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($_POST['secteur'] ?? '') ?>" placeholder="ex: Informatique, Design, Marketing">
        <?php if (isset($errors['secteur'])): ?><div class="invalid-feedback"><?= $errors['secteur'] ?></div><?php endif; ?>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-6">
          <label class="form-label">Salaire minimum (DT/mois)</label>
          <input type="number" name="salaire_min" class="form-control <?= isset($errors['salaire_min']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($_POST['salaire_min'] ?? '') ?>" min="0" placeholder="1200">
          <?php if (isset($errors['salaire_min'])): ?><div class="invalid-feedback"><?= $errors['salaire_min'] ?></div><?php endif; ?>
        </div>
        <div class="col-6">
          <label class="form-label">Salaire maximum (DT/mois)</label>
          <input type="number" name="salaire_max" class="form-control <?= isset($errors['salaire_max']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($_POST['salaire_max'] ?? '') ?>" min="0" placeholder="3000">
          <?php if (isset($errors['salaire_max'])): ?><div class="invalid-feedback"><?= $errors['salaire_max'] ?></div><?php endif; ?>
        </div>
      </div>

      <?php if (isset($errors['global'])): ?>
      <div class="alert alert-danger"><?= $errors['global'] ?></div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-hero"><i class="fas fa-save me-2"></i>Enregistrer</button>
        <a href="list_metiers.php" class="btn-outline-hero">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
