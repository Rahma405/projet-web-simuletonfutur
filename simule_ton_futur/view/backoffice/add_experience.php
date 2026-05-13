<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl      = new ExperienceC();
$pageTitle = 'Ajouter une Expérience';
$cvs       = $ctrl->listCvs();
$errors    = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->add($data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => 'Expérience ajoutée !'];
        header('Location: list_experiences.php'); exit;
    }
}
require_once __DIR__ . '/layouts/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Ajouter une Expérience Générale</div>
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label">CV associé *</label>
          <select name="cv_id" class="form-select <?= isset($errors['cv_id']) ? 'is-invalid' : '' ?>">
            <option value="">-- Sélectionner un CV --</option>
            <?php foreach ($cvs as $cv): ?>
            <option value="<?= $cv['id'] ?>" <?= (($_POST['cv_id'] ?? '') == $cv['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cv['titre_poste']) ?> — <?= htmlspecialchars($cv['email']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['cv_id'])): ?><div class="invalid-feedback"><?= $errors['cv_id'] ?></div><?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Titre du poste *</label>
          <input type="text" name="titre" class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>"
                 placeholder="Ex : Développeur Full-Stack" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
          <?php if (isset($errors['titre'])): ?><div class="invalid-feedback"><?= $errors['titre'] ?></div><?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Entreprise</label>
          <input type="text" name="entreprise" class="form-control"
                 placeholder="Ex : Vermeg" value="<?= htmlspecialchars($_POST['entreprise'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Lieu</label>
          <input type="text" name="lieu" class="form-control"
                 placeholder="Ex : Tunis" value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Type de contrat *</label>
          <select name="type_contrat" class="form-select <?= isset($errors['type_contrat']) ? 'is-invalid' : '' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach (['CDI','CDD','Stage','Freelance','Alternance','Bénévolat','Autre'] as $t): ?>
            <option value="<?= $t ?>" <?= (($_POST['type_contrat'] ?? '') === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['type_contrat'])): ?><div class="invalid-feedback"><?= $errors['type_contrat'] ?></div><?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Date de début *</label>
          <input type="date" name="date_debut" class="form-control <?= isset($errors['date_debut']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($_POST['date_debut'] ?? '') ?>">
          <?php if (isset($errors['date_debut'])): ?><div class="invalid-feedback"><?= $errors['date_debut'] ?></div><?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Date de fin</label>
          <input type="date" name="date_fin" class="form-control <?= isset($errors['date_fin']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>">
          <?php if (isset($errors['date_fin'])): ?><div class="invalid-feedback"><?= $errors['date_fin'] ?></div><?php endif; ?>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="en_cours" id="en_cours" value="1"
                   <?= isset($_POST['en_cours']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="en_cours" style="font-size:.86rem">Poste actuel (en cours)</label>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"
                    placeholder="Décrivez les missions, responsabilités..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_experiences.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
