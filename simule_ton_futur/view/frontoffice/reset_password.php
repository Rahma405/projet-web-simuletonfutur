<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$pageTitle = 'Changer le mot de passe';
$erreurs = [];
$success = null;
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$user = $token !== '' ? $ctrl->getUtilisateurByResetToken($token) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($token === '' || !$user) {
        $erreurs['global'] = "Le lien de reinitialisation est invalide ou expire.";
    }

    if ($newPassword === '') {
        $erreurs['new_password'] = "Le nouveau mot de passe est obligatoire.";
    } elseif (strlen($newPassword) < 6) {
        $erreurs['new_password'] = "Le mot de passe doit contenir au moins 6 caracteres.";
    }

    if ($confirmPassword === '') {
        $erreurs['confirm_password'] = "Confirmez le mot de passe.";
    } elseif ($confirmPassword !== $newPassword) {
        $erreurs['confirm_password'] = "Les deux mots de passe ne correspondent pas.";
    }

    if (empty($erreurs) && $ctrl->resetPasswordWithToken($token, $newPassword)) {
        $success = "Votre mot de passe a ete change avec succes. Vous pouvez maintenant vous connecter.";
        $user = null;
    } elseif (empty($erreurs)) {
        $erreurs['global'] = "Impossible de changer le mot de passe avec ce lien.";
    }
}

require_once __DIR__ . '/layouts/header.php';
?>

<section class="login-page">
  <div class="login-heading">
    <h1>Changer le mot de passe</h1>
    <p>Reinitialisez votre acces en toute securite</p>
  </div>

  <div class="login-card">
    <h2>Nouveau mot de passe</h2>
    <p class="login-subtitle">Entrez votre nouveau mot de passe</p>

    <?php if ($success !== null): ?>
      <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
      <a class="btn-login" href="<?= htmlspecialchars($baseUrl) ?>/view/frontoffice/login.php">Retour a la connexion</a>
    <?php elseif ($user): ?>
      <?php if (isset($erreurs['global'])): ?>
        <div class="alert alert-danger mb-4"><?= htmlspecialchars($erreurs['global']) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="mb-4">
          <label class="form-label">Nouveau mot de passe <span>*</span></label>
          <div class="password-field">
            <input
              type="password"
              name="new_password"
              id="resetPassword"
              class="form-control login-input <?= isset($erreurs['new_password']) ? 'is-invalid' : '' ?>"
              placeholder="Nouveau mot de passe">
            <button type="button" class="password-toggle" data-toggle-password="resetPassword">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <?php if (isset($erreurs['new_password'])): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['new_password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-4">
          <label class="form-label">Confirmer le mot de passe <span>*</span></label>
          <div class="password-field">
            <input
              type="password"
              name="confirm_password"
              id="resetPasswordConfirm"
              class="form-control login-input <?= isset($erreurs['confirm_password']) ? 'is-invalid' : '' ?>"
              placeholder="Confirmer le mot de passe">
            <button type="button" class="password-toggle" data-toggle-password="resetPasswordConfirm">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <?php if (isset($erreurs['confirm_password'])): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['confirm_password']) ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-login">Enregistrer</button>
      </form>
    <?php else: ?>
      <div class="alert alert-danger mb-4">Le lien de reinitialisation est invalide ou expire.</div>
      <a class="btn-login" href="<?= htmlspecialchars($baseUrl) ?>/view/frontoffice/login.php">Retour a la connexion</a>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
