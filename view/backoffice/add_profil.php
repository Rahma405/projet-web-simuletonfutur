<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrlP = new ProfilC();
$ctrlU = new UtilisateurC();
$pageTitle = 'Ajouter un Profil';
$erreurs = [];
$old = [];
$utilisateurs = $ctrlU->listUtilisateurs();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'bio' => trim($_POST['bio'] ?? ''),
        'photoProfil' => trim($_POST['photoProfil'] ?? 'default.png'),
        'ville' => trim($_POST['ville'] ?? ''),
        'pays' => trim($_POST['pays'] ?? ''),
        'langue' => $_POST['langue'] ?? '',
        'idUtilisateur' => $_POST['idUtilisateur'] ?? '',
    ];

    $erreurs = $ctrlP->valider($old);

    if (empty($erreurs)) {
        $profilExistant = $ctrlP->getByIdUtilisateur((int) $old['idUtilisateur']);
        $p = new Profil(
            $profilExistant ? $profilExistant->getIdProfil() : null,
            $old['bio'],
            $old['photoProfil'] ?: 'default.png',
            $old['ville'],
            $old['pays'],
            $old['langue'],
            (int) $old['idUtilisateur']
        );

        $ok = $profilExistant
            ? $ctrlP->updateProfil($p, $profilExistant->getIdProfil())
            : $ctrlP->addProfil($p);

        if ($ok) {
            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => $profilExistant ? 'Profil mis a jour avec succes.' : 'Profil ajoute avec succes.',
            ];
            header('Location: list_profils.php');
            exit;
        }
        $erreurs['global'] = "Erreur lors de l'enregistrement du profil.";
    }
}
require_once __DIR__ . '/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Ajouter un profil</div>
      <div class="card-body p-4">

        <?php if (isset($erreurs['global'])): ?>
          <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreurs['global']) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <div class="mb-3">
            <label class="form-label">Utilisateur associé <span style="color:#e63946">*</span></label>
            <select name="idUtilisateur" class="form-select <?= isset($erreurs['idUtilisateur'])?'is-invalid':'' ?>">
              <option value="">-- Sélectionner un utilisateur --</option>
              <?php foreach ($utilisateurs as $u): ?>
                <option value="<?= $u->getIdUtilisateur() ?>"
                  <?= ($old['idUtilisateur'] ?? '') == $u->getIdUtilisateur() ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u->getPrenom().' '.$u->getNom().' — '.$u->getEmail()) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['idUtilisateur'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['idUtilisateur']) ?></div><?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Bio</label>
            <textarea name="bio" rows="3"
              class="form-control <?= isset($erreurs['bio'])?'is-invalid':'' ?>"
              placeholder="Description du profil (max 500 caractères)"><?= htmlspecialchars($old['bio'] ?? '') ?></textarea>
            <?php if (isset($erreurs['bio'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['bio']) ?></div><?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label">Photo de profil (nom du fichier)</label>
            <input type="text" name="photoProfil"
              class="form-control"
              value="<?= htmlspecialchars($old['photoProfil'] ?? 'default.png') ?>"
              placeholder="default.png">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label">Ville</label>
              <input type="text" name="ville"
                class="form-control <?= isset($erreurs['ville'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($old['ville'] ?? '') ?>" placeholder="Ex : Tunis">
              <?php if (isset($erreurs['ville'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['ville']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Pays</label>
              <input type="text" name="pays"
                class="form-control <?= isset($erreurs['pays'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($old['pays'] ?? '') ?>" placeholder="Ex : Tunisie">
              <?php if (isset($erreurs['pays'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['pays']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label">Langue</label>
              <select name="langue" class="form-select <?= isset($erreurs['langue'])?'is-invalid':'' ?>">
                <option value="">-- Choisir --</option>
                <?php foreach(['Français','Arabe','Anglais','Espagnol','Allemand','Autre'] as $l): ?>
                  <option value="<?= $l ?>" <?= ($old['langue']??'')===$l?'selected':'' ?>><?= $l ?></option>
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

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
