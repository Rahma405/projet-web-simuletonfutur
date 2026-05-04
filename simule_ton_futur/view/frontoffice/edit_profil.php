<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Veuillez vous connecter pour modifier votre profil.'];
    header('Location: login.php');
    exit;
}

if (($_SESSION['user']['statut'] ?? 'actif') === 'en_attente') {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => "Votre compte est en attente. Cette action n'est pas encore autorisee."];
    header('Location: list_profils.php');
    exit;
}

$ctrl = new ProfilC();
$erreurs = [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: list_profils.php');
    exit;
}

$profil = $ctrl->getById($id);
if (!$profil) {
    header('Location: list_profils.php');
    exit;
}

$sessionUser = $_SESSION['user'];
$estAdmin = ($sessionUser['role'] ?? '') === 'admin';
if (!$estAdmin && (int) $sessionUser['id'] !== (int) $profil->getIdUtilisateur()) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Vous ne pouvez modifier que votre propre profil.'];
    header('Location: list_profils.php');
    exit;
}

$pageTitle = 'Modifier mon profil';

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
    $upload = stf_handle_profile_upload($_FILES['photoProfilFile'] ?? [], (string) ($profil->getPhotoProfil() ?? 'default.png'));
    $old = [
        'bio' => trim($_POST['bio'] ?? ''),
        'photoProfil' => $upload['filename'],
        'ville' => trim($_POST['ville'] ?? ''),
        'pays' => trim($_POST['pays'] ?? ''),
        'langue' => $_POST['langue'] ?? '',
        'idUtilisateur' => $profil->getIdUtilisateur(),
    ];

    if ($upload['error'] !== null) {
        $erreurs['photoProfil'] = $upload['error'];
    }

    $erreurs = array_merge($erreurs, $ctrl->valider($old, $id));

    if (empty($erreurs)) {
        $profil->setBio($old['bio'])
               ->setPhotoProfil($old['photoProfil'] ?: 'default.png')
               ->setVille($old['ville'])
               ->setPays($old['pays'])
               ->setLangue($old['langue']);

        if ($ctrl->updateProfil($profil, $id)) {
            $_SESSION['message'] = ['type' => 'success', 'texte' => 'Votre profil a ete modifie avec succes.'];
            header('Location: list_profils.php');
            exit;
        }

        $erreurs['global'] = "Erreur lors de la modification du profil.";
    } else {
        $profil->setBio($old['bio'])
               ->setPhotoProfil($old['photoProfil'])
               ->setVille($old['ville'])
               ->setPays($old['pays'])
               ->setLangue($old['langue']);
    }
}
require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card">
        <div style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;padding:16px 24px;border-radius:16px 16px 0 0;">
          <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2" style="color:#e63946"></i>Modifier mon profil</h5>
        </div>
        <div class="card-body p-4">
          <?php if (isset($erreurs['global'])): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($erreurs['global']) ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
              <label class="form-label">Bio</label>
              <textarea name="bio" rows="4" class="form-control <?= isset($erreurs['bio']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($profil->getBio() ?? '') ?></textarea>
              <?php if (isset($erreurs['bio'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['bio']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Photo de profil</label>
              <input type="file" name="photoProfilFile" accept=".jpg,.jpeg,.png,.webp,.gif" class="form-control <?= isset($erreurs['photoProfil']) ? 'is-invalid' : '' ?>">
              <small class="text-muted d-block mt-2">Image actuelle : <?= htmlspecialchars((string) $profil->getPhotoProfil()) ?></small>
              <?php if (isset($erreurs['photoProfil'])): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['photoProfil']) ?></div><?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label">Ville</label>
                <input type="text" name="ville" class="form-control <?= isset($erreurs['ville']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($profil->getVille() ?? '') ?>">
                <?php if (isset($erreurs['ville'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['ville']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-4">
                <label class="form-label">Pays</label>
                <input type="text" name="pays" class="form-control <?= isset($erreurs['pays']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($profil->getPays() ?? '') ?>">
                <?php if (isset($erreurs['pays'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['pays']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-4">
                <label class="form-label">Langue</label>
                <select name="langue" class="form-select <?= isset($erreurs['langue']) ? 'is-invalid' : '' ?>">
                  <option value="">-- Choisir --</option>
                  <?php foreach (['Français', 'Arabe', 'Anglais', 'Espagnol', 'Allemand', 'Autre'] as $langue): ?>
                    <option value="<?= $langue ?>" <?= ($profil->getLangue() ?? '') === $langue ? 'selected' : '' ?>><?= $langue ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($erreurs['langue'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['langue']) ?></div><?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn-hero" style="border-radius:10px;padding:10px 22px;">
                <i class="fas fa-save me-1"></i>Enregistrer
              </button>
              <a href="list_profils.php" class="btn btn-outline-secondary" style="border-radius:10px;padding:10px 22px;">Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
