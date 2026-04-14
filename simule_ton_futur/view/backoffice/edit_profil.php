<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

$ctrl    = new ProfilC();
$erreurs = [];
$id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: list_profils.php'); exit; }

$p = $ctrl->getById($id);
if (!$p)  { header('Location: list_profils.php'); exit; }

$pageTitle = 'Modifier le Profil #' . $id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'bio'           => trim($_POST['bio']         ?? ''),
        'photoProfil'   => trim($_POST['photoProfil'] ?? 'default.png'),
        'ville'         => trim($_POST['ville']       ?? ''),
        'pays'          => trim($_POST['pays']        ?? ''),
        'langue'        => $_POST['langue']           ?? '',
        'idUtilisateur' => $p->getIdUtilisateur(),   // on ne change pas l'utilisateur lié
    ];

    $erreurs = $ctrl->valider($old);

    if (empty($erreurs)) {
        $p->setBio($old['bio'])
          ->setPhotoProfil($old['photoProfil'] ?: 'default.png')
          ->setVille($old['ville'])
          ->setPays($old['pays'])
          ->setLangue($old['langue']);

        if ($ctrl->updateProfil($p, $id)) {
            $_SESSION['message'] = ['type'=>'success','texte'=>"✅ Profil mis à jour avec succès."];
            header('Location: list_profils.php');
            exit;
        }
        $erreurs['global'] = "Erreur lors de la modification.";
    } else {
        $p->setBio($old['bio'])->setPhotoProfil($old['photoProfil'])
          ->setVille($old['ville'])->setPays($old['pays'])->setLangue($old['langue']);
    }
}

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="fas fa-id-card me-2"></i>Modifier le Profil #<?= $id ?></div>
      <div class="card-body p-4">

        <?php if (isset($erreurs['global'])): ?>
          <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreurs['global']) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" rows="3"
              class="form-control <?= isset($erreurs['bio'])?'is-invalid':'' ?>"
              placeholder="Description (max 500 caractères)"><?= htmlspecialchars($p->getBio() ?? '') ?></textarea>
            <?php if (isset($erreurs['bio'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['bio']) ?></div><?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Photo de profil (nom du fichier)</label>
            <input type="text" name="photoProfil" class="form-control"
              value="<?= htmlspecialchars($p->getPhotoProfil()) ?>" placeholder="default.png">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Ville</label>
              <input type="text" name="ville"
                class="form-control <?= isset($erreurs['ville'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($p->getVille() ?? '') ?>">
              <?php if (isset($erreurs['ville'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['ville']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Pays</label>
              <input type="text" name="pays"
                class="form-control <?= isset($erreurs['pays'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($p->getPays() ?? '') ?>">
              <?php if (isset($erreurs['pays'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['pays']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Langue</label>
              <select name="langue" class="form-select <?= isset($erreurs['langue'])?'is-invalid':'' ?>">
                <option value="">-- Choisir --</option>
                <?php foreach(['Français','Arabe','Anglais','Espagnol','Allemand','Autre'] as $l): ?>
                  <option value="<?= $l ?>" <?= $p->getLangue()===$l?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($erreurs['langue'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['langue']) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-red px-4"><i class="fas fa-save me-2"></i>Enregistrer</button>
            <a href="list_profils.php" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
