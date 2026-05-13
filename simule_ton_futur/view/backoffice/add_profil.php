<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/ProfilC.php';
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrlP = new ProfilC();
$ctrlU = new UtilisateurC();
$pageTitle = 'Ajouter un Profil';
$erreurs = [];
$old = [];
$utilisateurs = $ctrlU->listUtilisateurs();

if (!function_exists('stf_handle_profile_upload')) {
    function stf_handle_profile_upload(array $file, string $current = 'default.png'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['filename' => $current ?: 'default.png', 'error' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['filename' => $current ?: 'default.png', 'error' => "Erreur lors de l'envoi de l'image."];
        }

        if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
            return ['filename' => $current ?: 'default.png', 'error' => "L'image ne doit pas depasser 3 Mo."];
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            return ['filename' => $current ?: 'default.png', 'error' => 'Format image invalide.'];
        }

        $uploadDir = dirname(__DIR__) . '/assets/img/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = 'profil-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['filename' => $current ?: 'default.png', 'error' => "Impossible d'enregistrer l'image."];
        }

        return ['filename' => $filename, 'error' => null];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload = stf_handle_profile_upload($_FILES['photoProfilFile'] ?? [], 'default.png');
    $old = [
        'bio' => trim($_POST['bio'] ?? ''),
        'photoProfil' => $upload['filename'],
        'ville' => trim($_POST['ville'] ?? ''),
        'pays' => trim($_POST['pays'] ?? ''),
        'langue' => $_POST['langue'] ?? '',
        'idUtilisateur' => $_POST['idUtilisateur'] ?? '',
    ];

    if ($upload['error'] !== null) {
        $erreurs['photoProfil'] = $upload['error'];
    }

    $erreurs = array_merge($erreurs, $ctrlP->valider($old));

    if (empty($erreurs)) {
        $p = new Profil(
            null,
            $old['bio'],
            $old['photoProfil'] ?: 'default.png',
            $old['ville'],
            $old['pays'],
            $old['langue'],
            (int) $old['idUtilisateur']
        );

        if ($ctrlP->addProfil($p)) {
            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => 'Profil ajoute avec succes.',
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

        <form method="POST" enctype="multipart/form-data" novalidate>

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
            <label class="form-label">Photo de profil</label>
            <input type="file" name="photoProfilFile"
              class="form-control <?= isset($erreurs['photoProfil'])?'is-invalid':'' ?>"
              accept=".jpg,.jpeg,.png,.webp,.gif">
            <?php if (!empty($old['photoProfil']) && ($old['photoProfil'] ?? '') !== 'default.png'): ?>
              <small class="text-muted d-block mt-2">Image choisie : <?= htmlspecialchars($old['photoProfil']) ?></small>
            <?php endif; ?>
            <?php if (isset($erreurs['photoProfil'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['photoProfil']) ?></div><?php endif; ?>
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
