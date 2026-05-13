<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Veuillez vous connecter pour creer votre profil.'];
    header('Location: login.php');
    exit;
}

if (($_SESSION['user']['statut'] ?? 'actif') === 'en_attente') {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => "Votre compte est en attente. Cette action n'est pas encore autorisee."];
    header('Location: list_profils.php');
    exit;
}

$ctrl = new ProfilC();
$pageTitle = stf_t('complete_profile');
$erreurs = [];
$old = [];
$userId = (int) $_SESSION['user']['id'];

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

if ($ctrl->getByIdUtilisateur($userId)) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Vous avez deja un profil.'];
    header('Location: list_profils.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload = stf_handle_profile_upload($_FILES['photoProfilFile'] ?? [], 'default.png');
    $old = [
        'bio' => trim($_POST['bio'] ?? ''),
        'photoProfil' => $upload['filename'],
        'ville' => trim($_POST['ville'] ?? ''),
        'pays' => trim($_POST['pays'] ?? ''),
        'langue' => $_POST['langue'] ?? '',
        'idUtilisateur' => $userId,
    ];

    if ($upload['error'] !== null) {
        $erreurs['photoProfil'] = $upload['error'];
    }

    $erreurs = array_merge($erreurs, $ctrl->valider($old));

    if (empty($erreurs)) {
        $profil = new Profil(
            null,
            $old['bio'],
            $old['photoProfil'] ?: 'default.png',
            $old['ville'],
            $old['pays'],
            $old['langue'],
            $userId
        );

        if ($ctrl->addProfil($profil)) {
            $_SESSION['site_lang'] = stf_language_code_from_value($old['langue'] ?? '');
            $_SESSION['message'] = ['type' => 'success', 'texte' => 'Votre profil a ete ajoute avec succes.'];
            header('Location: list_profils.php');
            exit;
        }

        $erreurs['global'] = "Erreur lors de l'ajout du profil.";
    }
}
require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card">
        <div style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;padding:16px 24px;border-radius:16px 16px 0 0;">
          <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2" style="color:#e63946"></i><?= htmlspecialchars(stf_t('complete_profile')) ?></h5>
        </div>
        <div class="card-body p-4">
          <?php if (isset($erreurs['global'])): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($erreurs['global']) ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
              <label class="form-label"><?= htmlspecialchars(stf_t('bio')) ?></label>
              <textarea name="bio" rows="4" class="form-control <?= isset($erreurs['bio']) ? 'is-invalid' : '' ?>" placeholder="Parlez de vous en quelques lignes"><?= htmlspecialchars($old['bio'] ?? '') ?></textarea>
              <?php if (isset($erreurs['bio'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['bio']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label"><?= htmlspecialchars(stf_t('profile_photo')) ?></label>
              <input type="file" name="photoProfilFile" accept=".jpg,.jpeg,.png,.webp,.gif" class="form-control <?= isset($erreurs['photoProfil']) ? 'is-invalid' : '' ?>">
              <?php if (!empty($old['photoProfil']) && ($old['photoProfil'] ?? '') !== 'default.png'): ?>
                <small class="text-muted d-block mt-2">Image choisie : <?= htmlspecialchars($old['photoProfil']) ?></small>
              <?php endif; ?>
              <?php if (isset($erreurs['photoProfil'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['photoProfil']) ?></div><?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label"><?= htmlspecialchars(stf_t('city')) ?></label>
                <input type="text" name="ville" class="form-control <?= isset($erreurs['ville']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['ville'] ?? '') ?>">
                <?php if (isset($erreurs['ville'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['ville']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-4">
                <label class="form-label"><?= htmlspecialchars(stf_t('country')) ?></label>
                <input type="text" name="pays" class="form-control <?= isset($erreurs['pays']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['pays'] ?? '') ?>">
                <?php if (isset($erreurs['pays'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['pays']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-4">
                <label class="form-label"><?= htmlspecialchars(stf_t('language')) ?></label>
                <select name="langue" class="form-select <?= isset($erreurs['langue']) ? 'is-invalid' : '' ?>">
                  <option value=""><?= htmlspecialchars(stf_t('choose')) ?></option>
                  <?php foreach (['Français', 'Arabe', 'Anglais', 'Espagnol', 'Allemand', 'Autre'] as $langue): ?>
                    <option value="<?= $langue ?>" <?= ($old['langue'] ?? '') === $langue ? 'selected' : '' ?>><?= $langue ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($erreurs['langue'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['langue']) ?></div><?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn-hero" style="border-radius:10px;padding:10px 22px;">
                <i class="fas fa-save me-1"></i><?= htmlspecialchars(stf_t('save')) ?>
              </button>
              <a href="list_profils.php" class="btn btn-outline-secondary" style="border-radius:10px;padding:10px 22px;"><?= htmlspecialchars(stf_t('cancel')) ?></a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
