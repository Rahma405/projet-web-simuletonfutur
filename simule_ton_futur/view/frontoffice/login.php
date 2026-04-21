<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$pageTitle = 'Connexion';
$erreurs = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['email'] = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['motDePasse'] ?? '';

    if ($old['email'] === '') {
        $erreurs['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'adresse email n'est pas valide.";
    }

    if ($motDePasse === '') {
        $erreurs['motDePasse'] = "Le mot de passe est obligatoire.";
    }

    if (empty($erreurs)) {
        $utilisateur = $ctrl->login($old['email'], $motDePasse);

        if ($utilisateur) {
            $_SESSION['user'] = [
                'id' => $utilisateur->getIdUtilisateur(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole(),
            ];

            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => 'Connexion reussie. Bienvenue ' . $utilisateur->getPrenom() . ' !',
            ];

            if ($utilisateur->getRole() === 'admin') {
                header('Location: ../backoffice/list_utilisateurs.php');
            } else {
                header('Location: ../../index.php');
            }
            exit;
        }

        $erreurs['global'] = "Email ou mot de passe incorrect.";
    }
}

require_once __DIR__ . '/layouts/header.php';
?>

<section class="login-page">
  <div class="login-heading">
    <h1>Se connecter</h1>
    <p>Accedez a votre espace Simule Ton Futur</p>
  </div>

  <div class="login-card">
    <h2>Connexion</h2>
    <p class="login-subtitle">Entrez vos identifiants ci-dessous</p>

    <?php if (isset($erreurs['global'])): ?>
      <div class="alert alert-danger mb-4"><?= htmlspecialchars($erreurs['global']) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-4">
        <label class="form-label">Email <span>*</span></label>
        <input
          type="text"
          name="email"
          class="form-control login-input <?= isset($erreurs['email']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($old['email']) ?>"
          placeholder="exemple@email.com">
        <?php if (isset($erreurs['email'])): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($erreurs['email']) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-4">
        <label class="form-label">Mot de passe <span>*</span></label>
        <div class="password-field">
          <input
            type="password"
            name="motDePasse"
            id="loginPassword"
            class="form-control login-input <?= isset($erreurs['motDePasse']) ? 'is-invalid' : '' ?>"
            placeholder="Votre mot de passe">
          <button type="button" class="password-toggle" data-toggle-password="loginPassword">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <?php if (isset($erreurs['motDePasse'])): ?>
          <div class="invalid-feedback d-block"><?= htmlspecialchars($erreurs['motDePasse']) ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-lock-open me-2"></i>Se connecter
      </button>

      <div class="login-register">
        Pas encore de compte ?
        <a href="<?= $baseUrl ?>/view/frontoffice/register.php">Creer un compte</a>
      </div>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
