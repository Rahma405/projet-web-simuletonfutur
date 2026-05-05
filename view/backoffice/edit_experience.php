<?php
session_start();
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl       = new ExperienceC();
$pageTitle  = 'Modifier une Expérience';
$id         = (int)($_GET['id'] ?? 0);
$experience = $ctrl->getExperience($id);
$cvs        = $ctrl->listCvs();
$errors     = [];
if (!$experience) { header('Location: list_experiences.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->edit($id, $data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => 'Expérience modifiée avec succès !'];
        header('Location: list_experiences.php'); exit;
    }
    // En cas d'erreur : mettre à jour l'objet avec les données saisies pour réafficher
    $experience->setCvId((int)($data['cv_id'] ?? $experience->getCvId()));
    $experience->setTitre($data['titre'] ?? $experience->getTitre());
    $experience->setEntreprise($data['entreprise'] ?? $experience->getEntreprise());
    $experience->setLieu($data['lieu'] ?? $experience->getLieu());
    $experience->setDateDebut($data['date_debut'] ?? $experience->getDateDebut());
    $experience->setDateFin(!empty($data['date_fin']) ? $data['date_fin'] : null);
    $experience->setEnCours(isset($data['en_cours']));
    $experience->setDescription($data['description'] ?? $experience->getDescription());
    $experience->setTypeContrat($data['type_contrat'] ?? $experience->getTypeContrat());
}
require_once __DIR__ . '/../../templates/backoffice/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier l'Expérience #<?= $id ?></div>
  <div class="card-body p-4">

    <?php if (!empty($errors['global'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errors['global']) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label fw-semibold">CV associé <span class="text-danger">*</span></label>
          <select name="cv_id" class="form-select <?= isset($errors['cv_id']) ? 'is-invalid' : '' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($cvs as $cv): ?>
            <option value="<?= $cv->getId() ?>" <?= ($experience->getCvId() == $cv->getId()) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cv->getTitrePoste()) ?> — <?= htmlspecialchars($cv->getEmail()) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['cv_id'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['cv_id']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Titre du poste <span class="text-danger">*</span></label>
          <input type="text" name="titre"
                 class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>"
                 value="<?= htmlspecialchars($experience->getTitre()) ?>">
          <?php if (isset($errors['titre'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['titre']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Entreprise</label>
          <input type="text" name="entreprise" class="form-control"
                 value="<?= htmlspecialchars($experience->getEntreprise()) ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Lieu</label>
          <input type="text" name="lieu" class="form-control"
                 value="<?= htmlspecialchars($experience->getLieu()) ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Type de contrat <span class="text-danger">*</span></label>
          <select name="type_contrat" class="form-select <?= isset($errors['type_contrat']) ? 'is-invalid' : '' ?>">
            <option value="">-- Sélectionner --</option>
            <?php foreach (['CDI','CDD','Stage','Freelance','Alternance','Bénévolat','Autre'] as $t): ?>
            <option value="<?= $t ?>" <?= ($experience->getTypeContrat() === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['type_contrat'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['type_contrat']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
          <input type="text" name="date_debut"
                 class="form-control <?= isset($errors['date_debut']) ? 'is-invalid' : '' ?>"
                 placeholder="YYYY-MM-DD"
                 value="<?= htmlspecialchars($experience->getDateDebut()) ?>">
          <?php if (isset($errors['date_debut'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['date_debut']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Date de fin</label>
          <input type="text" name="date_fin"
                 class="form-control <?= isset($errors['date_fin']) ? 'is-invalid' : '' ?>"
                 placeholder="YYYY-MM-DD (vide si en cours)"
                 value="<?= htmlspecialchars($experience->getDateFin() ?? '') ?>">
          <?php if (isset($errors['date_fin'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['date_fin']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="en_cours" id="en_cours" value="1"
                   <?= $experience->isEnCours() ? 'checked' : '' ?>>
            <label class="form-check-label" for="en_cours">Poste actuel (en cours)</label>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($experience->getDescription()) ?></textarea>
        </div>

      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_experiences.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
