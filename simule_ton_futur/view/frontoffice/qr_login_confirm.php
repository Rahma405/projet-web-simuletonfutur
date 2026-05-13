<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$user = $token !== '' ? $ctrl->getUtilisateurByQrToken($token) : null;
$status = $token !== '' ? $ctrl->getQrLoginStatus($token) : 'invalid';
$message = null;
$error = null;
$pageTitle = 'Validation QR';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token !== '' && $status === 'pending') {
    $confirmEmail = trim($_POST['confirm_email'] ?? '');

    if ($user === null) {
        $error = 'Ce QR code est invalide.';
    } elseif ($confirmEmail === '') {
        $error = "Entrez l'email du compte pour continuer.";
    } elseif (strcasecmp($confirmEmail, (string) $user->getEmail()) !== 0) {
        $error = "Cet email ne correspond pas au compte lie a ce QR code.";
    } elseif ($ctrl->approveQrLoginToken($token)) {
        $status = 'approved';
        $message = 'Connexion validee. Retournez sur votre ordinateur.';
    } else {
        $status = $ctrl->getQrLoginStatus($token);
        $error = 'Impossible de valider cette connexion.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #1d2b4f;
  }
  .qr-phone-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .qr-phone-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 18px 50px rgba(17, 24, 39, 0.12);
    padding: 28px 24px;
    text-align: center;
    border: 1px solid #e5eaf4;
  }
  .qr-phone-badge {
    width: 68px;
    height: 68px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 700;
  }
  .qr-phone-card h1 {
    margin: 0 0 10px;
    font-size: 28px;
  }
  .qr-phone-card p {
    margin: 0 0 18px;
    font-size: 16px;
    line-height: 1.5;
  }
  .qr-phone-user {
    background: #f7f9fd;
    border: 1px solid #d9e3f5;
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 18px;
  }
  .qr-phone-user strong {
    display: block;
    font-size: 18px;
    margin-bottom: 4px;
  }
  .qr-phone-alert {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 18px;
    font-size: 15px;
  }
  .qr-phone-success {
    background: #dcfce7;
    color: #166534;
  }
  .qr-phone-danger {
    background: #fee2e2;
    color: #991b1b;
  }
  .qr-phone-btn {
    display: inline-block;
    width: 100%;
    border: 0;
    border-radius: 12px;
    padding: 15px 18px;
    background: #e53945;
    color: #fff;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
  }
  .qr-phone-input {
    width: 100%;
    border: 1px solid #d9e3f5;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 16px;
    margin-bottom: 14px;
    box-sizing: border-box;
  }
  .qr-phone-help {
    font-size: 14px;
    color: #5f6f8d;
    margin-bottom: 14px;
  }
</style>
</head>
<body>
  <div class="qr-phone-wrap">
    <div class="qr-phone-card">
      <div class="qr-phone-badge">QR</div>
      <h1>Validation QR</h1>
      <p>Confirmez la connexion depuis votre telephone.</p>

      <?php if ($message !== null): ?>
        <div class="qr-phone-alert qr-phone-success"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <?php if ($error !== null): ?>
        <div class="qr-phone-alert qr-phone-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($user && $status === 'pending'): ?>
        <div class="qr-phone-user">
          <strong><?= htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) ?></strong>
          <span><?= htmlspecialchars($user->getEmail()) ?></span>
        </div>
        <form method="POST">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <div class="qr-phone-help">
            Pour securiser la connexion, entrez le meme email que celui du compte scanne.
          </div>
          <input
            type="text"
            name="confirm_email"
            class="qr-phone-input"
            value="<?= htmlspecialchars($_POST['confirm_email'] ?? '') ?>"
            placeholder="Entrez le meme email">
          <button type="submit" class="qr-phone-btn">Valider la connexion</button>
        </form>
      <?php elseif ($user && $status === 'approved'): ?>
        <?php if ($message === null): ?>
          <div class="qr-phone-alert qr-phone-success">Connexion deja validee. Vous pouvez revenir sur votre ordinateur.</div>
        <?php endif; ?>
      <?php elseif ($status === 'expired'): ?>
        <div class="qr-phone-alert qr-phone-danger">Ce QR code a expire. Generez-en un nouveau.</div>
      <?php else: ?>
        <div class="qr-phone-alert qr-phone-danger">Ce QR code est invalide.</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
