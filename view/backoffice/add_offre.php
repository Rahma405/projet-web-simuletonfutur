<?php
session_start();
require_once __DIR__ . '/../../controller/OffreC.php';

$ctrl      = new OffreC();
$pageTitle = 'Ajouter une Offre';
$erreurs   = [];
$old       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'titre'        => trim($_POST['titre'] ?? ''),
        'competences'  => trim($_POST['competences'] ?? ''),
        'localisation' => trim($_POST['localisation'] ?? ''),
    ];

    $erreurs = $ctrl->valider($old);

    if (empty($erreurs)) {
        $o = new Offre();
        $o->setTitre($old['titre']);
        $o->setCompetences($old['competences']);
        $o->setLocalisation($old['localisation']);

        if ($ctrl->addOffre($o)) {
            $_SESSION['message'] = ['type' => 'success', 'texte' => 'Offre ajoutée avec succès.'];
            header('Location: list_offres.php');
            exit;
        }
        $erreurs['global'] = "Erreur lors de l'enregistrement de l'offre.";
    }
}

require_once __DIR__ . '/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-plus-circle me-2"></i>Ajouter une offre d'emploi
      </div>
      <div class="card-body p-4">

        <?php if (isset($erreurs['global'])): ?>
          <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreurs['global']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <!-- Titre -->
          <div class="mb-3">
            <label class="form-label">Titre du poste <span style="color:#e63946">*</span></label>
            <input type="text" name="titre"
              class="form-control <?= isset($erreurs['titre']) ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($old['titre'] ?? '') ?>"
              placeholder="Ex : Développeur PHP Full Stack">
            <?php if (isset($erreurs['titre'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($erreurs['titre']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Compétences -->
          <div class="mb-3">
            <label class="form-label">Compétences requises <span style="color:#e63946">*</span></label>
            <input type="text" name="competences"
              class="form-control <?= isset($erreurs['competences']) ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($old['competences'] ?? '') ?>"
              placeholder="PHP, MySQL, HTML, CSS, JavaScript">
            <div class="form-text">Sépare les compétences par des virgules.</div>
            <?php if (isset($erreurs['competences'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($erreurs['competences']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Localisation -->
          <div class="mb-3">
            <label class="form-label">Localisation <span style="color:#e63946">*</span></label>
            <input type="text" name="localisation"
              class="form-control <?= isset($erreurs['localisation']) ? 'is-invalid' : '' ?>"
              value="<?= htmlspecialchars($old['localisation'] ?? '') ?>"
              placeholder="Ex : Tunis">
            <?php if (isset($erreurs['localisation'])): ?>
              <div class="invalid-feedback"><?= htmlspecialchars($erreurs['localisation']) ?></div>
            <?php endif; ?>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-red px-4">
              <i class="fas fa-save me-2"></i>Enregistrer
            </button>
            <a href="list_offres.php" class="btn btn-outline-secondary px-4">
              <i class="fas fa-arrow-left me-2"></i>Annuler
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
