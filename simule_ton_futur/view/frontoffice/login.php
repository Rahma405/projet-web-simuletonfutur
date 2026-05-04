<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$pageTitle = 'Connexion';
$erreurs = [];
$captchaNotice = null;
$resetNotice = null;
$resetLinkPreview = null;
$qrNotice = null;
$qrError = null;
$old = ['email' => ''];
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = preg_replace('#/view(?:/.*)?$#', '', $scriptDir);
$baseUrl = rtrim($baseUrl, '/');

$captchaThemes = [
    'voiture' => [
        'title' => 'Selectionnez toutes les images avec des voitures',
        'answer' => [0, 2, 6],
        'folder' => 'cars-real',
    ],
    'feu' => [
        'title' => 'Selectionnez toutes les images avec des feux de circulation',
        'answer' => [0, 1, 4, 7],
        'folder' => 'lights-real',
    ],
];

$buildCaptcha = static function () use ($captchaThemes): array {
    $themeKeys = array_keys($captchaThemes);
    $target = $themeKeys[array_rand($themeKeys)];
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $textCode = '';

    for ($i = 0; $i < 5; $i++) {
        $textCode .= $letters[random_int(0, strlen($letters) - 1)];
    }

    $a = random_int(1, 5);
    $b = random_int(1, 5);

    return [
        'stage' => 1,
        'target' => $target,
        'answer' => $captchaThemes[$target]['answer'],
        'text_code' => $textCode,
        'math_a' => $a,
        'math_b' => $b,
        'math_answer' => $a + $b,
    ];
};

$isValidCaptchaData = static function ($data) use ($captchaThemes): bool {
    if (!is_array($data)) {
        return false;
    }

    if (
        empty($data['target']) ||
        !isset($data['answer'], $data['stage'], $data['text_code'], $data['math_a'], $data['math_b'], $data['math_answer'])
    ) {
        return false;
    }

    if (!isset($captchaThemes[$data['target']]) || !is_array($data['answer'])) {
        return false;
    }

    if (!is_numeric($data['stage']) || (int) $data['stage'] < 1 || (int) $data['stage'] > 4) {
        return false;
    }

    if (!is_string($data['text_code']) || strlen($data['text_code']) < 4) {
        return false;
    }

    if (!is_numeric($data['math_a']) || !is_numeric($data['math_b']) || !is_numeric($data['math_answer'])) {
        return false;
    }

    foreach ($data['answer'] as $index) {
        if (!is_int($index) && !ctype_digit((string) $index)) {
            return false;
        }
    }

    return true;
};

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (isset($_SESSION['login_pending_email']) && $_SESSION['login_pending_email'] !== '') {
    $old['email'] = (string) $_SESSION['login_pending_email'];
}

if (isset($_SESSION['login_captcha_ok'])) {
    $captchaNotice = "Verification terminee. Vous pouvez maintenant vous reconnecter.";
    unset($_SESSION['login_captcha_ok']);
}

if (isset($_SESSION['login_reset_notice'])) {
    $resetNotice = (string) $_SESSION['login_reset_notice'];
    unset($_SESSION['login_reset_notice']);
}

if (isset($_SESSION['login_reset_link_preview'])) {
    $resetLinkPreview = (string) $_SESSION['login_reset_link_preview'];
    unset($_SESSION['login_reset_link_preview']);
}

if (isset($_SESSION['qr_login_notice'])) {
    $qrNotice = (string) $_SESSION['qr_login_notice'];
    unset($_SESSION['qr_login_notice']);
}

if (isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_attempts'] = 0;
    unset($_SESSION['login_puzzle']);
    unset($_SESSION['login_pending_password']);
}

$captchaRequired = $_SESSION['login_attempts'] >= 3;

if ($captchaRequired && !$isValidCaptchaData($_SESSION['login_puzzle'] ?? null)) {
    $_SESSION['login_puzzle'] = $buildCaptcha();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_qr_login'])) {
        $qrEmail = trim($_POST['qr_email'] ?? '');

        if ($qrEmail === '') {
            $qrError = "L'email est obligatoire pour generer le QR code.";
        } elseif (!filter_var($qrEmail, FILTER_VALIDATE_EMAIL)) {
            $qrError = "L'adresse email n'est pas valide.";
        } else {
            $token = $ctrl->createQrLoginToken($qrEmail);

            if ($token === null) {
                $qrError = "Aucun compte n'est associe a cet email.";
            } else {
                $_SESSION['qr_login_token'] = $token;
                $_SESSION['qr_login_email'] = $qrEmail;
                $_SESSION['qr_login_notice'] = "QR code genere. Scanne-le avec ton telephone puis valide la connexion.";
                header('Location: login.php');
                exit;
            }
        }
    }

    if (isset($_POST['reset_choice'])) {
        $choice = $_POST['reset_choice'];
        $emailForReset = (string) ($_SESSION['login_reset_choice_email'] ?? '');

        if ($choice === 'yes' && $emailForReset !== '') {
            $token = $ctrl->createPasswordResetToken($emailForReset);

            if ($token !== null) {
                $resetLink = Config::getPublicBaseUrl() . '/view/frontoffice/reset_password.php?token=' . urlencode($token);
                $sent = $ctrl->sendPasswordResetEmail($emailForReset, $resetLink);
                $resetLinkPreview = $resetLink;

                if ($sent) {
                    $resetNotice = "Un email a ete envoye a $emailForReset pour changer le mot de passe. Si tu ne le recois pas en local, utilise le lien ci-dessous.";
                } else {
                    $resetNotice = "L'envoi automatique d'email n'est pas configure ici. Utilisez ce lien de reinitialisation.";
                }
            } else {
                $resetNotice = "Impossible de preparer la reinitialisation pour cet email.";
            }
        } else {
            $resetNotice = "Vous avez choisi de garder votre mot de passe actuel.";
        }

        unset($_SESSION['login_reset_choice_email']);
    }

    $old['email'] = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['motDePasse'] ?? '';

    if (!$captchaRequired && $old['email'] === '') {
        $erreurs['email'] = "L'email est obligatoire.";
    } elseif (!$captchaRequired && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'adresse email n'est pas valide.";
    }

    if (!$captchaRequired && $motDePasse === '') {
        $erreurs['motDePasse'] = "Le mot de passe est obligatoire.";
    }

    $captchaUnlocked = !$captchaRequired;

    if (empty($erreurs) && $captchaRequired) {
        $old['email'] = (string) ($_SESSION['login_pending_email'] ?? $old['email']);

        if (!$isValidCaptchaData($_SESSION['login_puzzle'] ?? null)) {
            $_SESSION['login_puzzle'] = $buildCaptcha();
        }

        $captchaData = $_SESSION['login_puzzle'];
        $stage = (int) $captchaData['stage'];

        if ($stage === 1) {
            if (($_POST['captcha_not_robot'] ?? '') !== '1') {
                $erreurs['captcha'] = "Cochez la case 'I'm not a robot' pour continuer.";
            } else {
                $_SESSION['login_puzzle']['stage'] = 2;
                $captchaNotice = "Etape 1 validee. Passez a la selection d'images.";
            }
        } elseif ($stage === 2) {
            $selected = array_map('intval', $_POST['captcha_choices'] ?? []);
            sort($selected);
            $expected = $captchaData['answer'];
            sort($expected);

            if (empty($selected) || $selected !== $expected) {
                $erreurs['captcha'] = "Selection d'images incorrecte. Reessayez.";
            } else {
                $_SESSION['login_puzzle']['stage'] = 3;
                $captchaNotice = "Etape 2 validee. Saisissez maintenant le texte affiche.";
            }
        } elseif ($stage === 3) {
            $captchaText = strtoupper(trim($_POST['captcha_text'] ?? ''));

            if ($captchaText === '') {
                $erreurs['captcha'] = "Recopiez le texte affiche.";
            } elseif ($captchaText !== strtoupper($captchaData['text_code'])) {
                $erreurs['captcha'] = "Le texte saisi est incorrect.";
            } else {
                $_SESSION['login_puzzle']['stage'] = 4;
                $captchaNotice = "Etape 3 validee. Repondez a la question simple.";
            }
        } elseif ($stage === 4) {
            $captchaMath = trim($_POST['captcha_math'] ?? '');

            if ($captchaMath === '') {
                $erreurs['captcha'] = "Entrez la reponse a la question.";
            } elseif (!ctype_digit($captchaMath) || (int) $captchaMath !== (int) $captchaData['math_answer']) {
                $erreurs['captcha'] = "La reponse a la question est incorrecte.";
            } else {
                $_SESSION['login_attempts'] = 0;
                unset($_SESSION['login_puzzle']);
                unset($_SESSION['login_pending_password']);
                $_SESSION['login_reset_choice_email'] = (string) ($_SESSION['login_pending_email'] ?? $old['email']);
                $_SESSION['login_captcha_ok'] = true;
                header('Location: login.php');
                exit;
            }
        }
    }

    if (empty($erreurs) && $captchaUnlocked) {
        $utilisateur = $ctrl->login($old['email'], $motDePasse);

        if ($utilisateur) {
            $_SESSION['user'] = [
                'id' => $utilisateur->getIdUtilisateur(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole(),
                'statut' => $utilisateur->getStatut(),
            ];
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['login_puzzle']);
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

        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['login_puzzle'] = $buildCaptcha();
            $_SESSION['login_pending_email'] = $old['email'];
            $_SESSION['login_pending_password'] = $motDePasse;
        }
        $erreurs['global'] = $ctrl->getLastLoginError() ?? "Email ou mot de passe incorrect.";
    }

    $captchaRequired = $_SESSION['login_attempts'] >= 3;
}

$captchaData = null;
$captchaStage = 0;
$submitLabel = 'Se connecter';
$qrLoginToken = (string) ($_SESSION['qr_login_token'] ?? '');
$qrLoginEmail = (string) ($_SESSION['qr_login_email'] ?? '');
$qrLoginUrl = '';
$qrLoginStatus = '';

if ($qrLoginToken !== '') {
    $qrLoginStatus = $ctrl->getQrLoginStatus($qrLoginToken);

    if ($qrLoginStatus === 'approved') {
        $utilisateur = $ctrl->consumeQrLoginToken($qrLoginToken);

        if ($utilisateur) {
            $_SESSION['user'] = [
                'id' => $utilisateur->getIdUtilisateur(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole(),
                'statut' => $utilisateur->getStatut(),
            ];
            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => 'Connexion QR reussie. Bienvenue ' . $utilisateur->getPrenom() . ' !',
            ];
            unset($_SESSION['qr_login_token'], $_SESSION['qr_login_email']);

            header('Location: ' . ($utilisateur->getRole() === 'admin'
                ? '../backoffice/list_utilisateurs.php'
                : '../../index.php'));
            exit;
        }
    }

    if ($qrLoginStatus === 'invalid' || $qrLoginStatus === 'used' || $qrLoginStatus === 'expired') {
        unset($_SESSION['qr_login_token'], $_SESSION['qr_login_email']);
        $qrLoginToken = '';
        $qrLoginEmail = '';
    } else {
        $qrLoginUrl = Config::getPublicBaseUrl() . '/view/frontoffice/qr_login_confirm.php?token=' . urlencode($qrLoginToken);
    }
}

if ($captchaRequired) {
    if (!$isValidCaptchaData($_SESSION['login_puzzle'] ?? null)) {
        $_SESSION['login_puzzle'] = $buildCaptcha();
    }

    $captchaData = $_SESSION['login_puzzle'];
    $captchaStage = (int) $captchaData['stage'];
    $submitLabel = $captchaStage < 4 ? 'Verifier et continuer' : 'Se connecter';
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

    <?php if ($captchaNotice !== null): ?>
      <div class="alert alert-success mb-4"><?= htmlspecialchars($captchaNotice) ?></div>
    <?php endif; ?>

    <?php if ($resetNotice !== null): ?>
      <div class="alert alert-success mb-4"><?= htmlspecialchars($resetNotice) ?></div>
    <?php endif; ?>

    <?php if ($resetLinkPreview !== null): ?>
      <div class="alert alert-warning mb-4">
        <div class="mb-2">Lien de reinitialisation :</div>
        <a href="<?= htmlspecialchars($resetLinkPreview) ?>" class="btn-login btn-login-small d-inline-block mb-3">Ouvrir le lien</a>
        <div style="word-break:break-all;">
          <a href="<?= htmlspecialchars($resetLinkPreview) ?>"><?= htmlspecialchars($resetLinkPreview) ?></a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($qrNotice !== null): ?>
      <div class="alert alert-success mb-4"><?= htmlspecialchars($qrNotice) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['login_reset_choice_email'])): ?>
      <div class="reset-choice-card mb-4">
        <h3>Changer le mot de passe ?</h3>
        <p>
          Un email peut etre envoye a
          <strong><?= htmlspecialchars((string) $_SESSION['login_reset_choice_email']) ?></strong>
          pour changer le mot de passe.
        </p>
        <form method="POST" class="reset-choice-actions">
          <button type="submit" name="reset_choice" value="yes" class="btn-login btn-login-small">
            Oui, envoyer le lien
          </button>
          <button type="submit" name="reset_choice" value="no" class="btn-reset-choice-no">
            Non
          </button>
        </form>
      </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['login_reset_choice_email'])): ?>
    <div class="qr-login-card mb-4">
      <h3>Connexion par QR code</h3>
      <p>Entrez votre email, genere un QR code puis scanne-le avec votre telephone.</p>
      <form method="POST" class="qr-login-form">
        <input
          type="text"
          name="qr_email"
          class="form-control login-input <?= $qrError !== null ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($qrLoginEmail !== '' ? $qrLoginEmail : ($old['email'] ?? '')) ?>"
          placeholder="exemple@email.com">
        <button type="submit" name="generate_qr_login" value="1" class="btn-login btn-login-small">
          Generer le QR
        </button>
      </form>
      <?php if ($qrError !== null): ?>
        <div class="invalid-feedback d-block mt-2"><?= htmlspecialchars($qrError) ?></div>
      <?php endif; ?>

      <?php if ($qrLoginUrl !== ''): ?>
        <div class="qr-login-preview" data-qr-login data-qr-token="<?= htmlspecialchars($qrLoginToken) ?>" data-qr-mode="<?= htmlspecialchars($qrLoginStatus) ?>">
          <div class="qr-login-box">
            <img
              src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($qrLoginUrl) ?>"
              alt="QR code de connexion"
              class="qr-login-image">
          </div>
          <div class="qr-login-meta">
            <strong>Scannez puis validez</strong>
            <p><?= htmlspecialchars($qrLoginEmail) ?></p>
            <div class="qr-login-status" data-qr-status>
              <?= $qrLoginStatus === 'approved' ? 'Validation recue. Connexion en cours...' : 'En attente de validation sur le telephone' ?>
            </div>
            <a href="<?= htmlspecialchars($qrLoginUrl) ?>" class="qr-login-link" target="_blank">Ouvrir le lien de validation</a>
            <button type="button" class="btn-reset-choice-no mt-3" onclick="window.location.reload()">J'ai valide sur mon telephone</button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <form method="POST" novalidate>
      <?php if (!$captchaRequired): ?>
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
      <?php endif; ?>

      <?php if ($captchaRequired && $captchaData): ?>
        <div class="login-captcha <?= isset($erreurs['captcha']) ? 'login-captcha-error' : '' ?>">
          <?php if ($captchaStage === 1): ?>
            <div class="captcha-panel">
              <label class="captcha-check-card">
                <input type="checkbox" name="captcha_not_robot" value="1">
                <span class="captcha-check-box"></span>
                <span class="captcha-check-text">I'm not a robot</span>
                <span class="captcha-check-brand">
                  <span class="captcha-check-logo">
                    <i class="fas fa-rotate-right"></i>
                  </span>
                  <small>reCAPTCHA</small>
                </span>
              </label>
            </div>
          <?php elseif ($captchaStage === 2): ?>
            <div class="captcha-panel">
              <div class="captcha-grid-card captcha-grid-card-<?= htmlspecialchars($captchaData['target']) ?>">
                <div class="captcha-grid-title">
                  <?= htmlspecialchars($captchaThemes[$captchaData['target']]['title']) ?>
                </div>

                <div class="captcha-grid-tiles">
                  <?php for ($index = 0; $index < 9; $index++): ?>
                    <label class="captcha-tile">
                      <input type="checkbox" name="captcha_choices[]" value="<?= $index ?>">
                      <span class="captcha-tile-frame">
                        <img
                          src="<?= htmlspecialchars($baseUrl . '/view/assets/img/captcha/' . $captchaThemes[$captchaData['target']]['folder'] . '/tile-' . $index . '.jpg') ?>"
                          alt="Tuile captcha <?= $index + 1 ?>">
                        <span class="captcha-tile-check"><i class="fas fa-check"></i></span>
                      </span>
                    </label>
                  <?php endfor; ?>
                </div>

                <div class="captcha-grid-actions">
                  <span><i class="fas fa-rotate-right"></i></span>
                  <span><i class="fas fa-headphones"></i></span>
                  <span><i class="fas fa-circle-info"></i></span>
                  <button type="submit" class="captcha-grid-verify">VERIFIER</button>
                </div>
              </div>
            </div>
          <?php elseif ($captchaStage === 3): ?>
            <div class="captcha-panel">
              <div class="captcha-text-card">
                <p>Entrez le texte que vous voyez</p>
                <div class="captcha-text-visual"><?= htmlspecialchars($captchaData['text_code']) ?></div>
                <input
                  type="text"
                  name="captcha_text"
                  class="form-control captcha-input"
                  placeholder="Entrez le texte">
                <div class="captcha-text-actions">
                  <span><i class="fas fa-rotate-right"></i></span>
                  <span><i class="fas fa-volume-high"></i></span>
                  <button type="submit" class="captcha-grid-verify">VERIFIER</button>
                </div>
              </div>
            </div>
          <?php elseif ($captchaStage === 4): ?>
            <div class="captcha-panel">
              <div class="captcha-math-card">
                <p>Quelle est la somme de :</p>
                <div class="captcha-math-question">
                  <?= (int) $captchaData['math_a'] ?> + <?= (int) $captchaData['math_b'] ?> = ?
                </div>
                <input
                  type="text"
                  name="captcha_math"
                  class="form-control captcha-input"
                  placeholder="Votre reponse">
                <div class="captcha-math-actions">
                  <button type="submit" class="captcha-grid-verify">VERIFIER</button>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if (isset($erreurs['captcha'])): ?>
            <div class="invalid-feedback d-block mt-3"><?= htmlspecialchars($erreurs['captcha']) ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!$captchaRequired || $captchaStage === 1): ?>
        <button type="submit" class="btn-login">
          <i class="fas fa-lock-open me-2"></i><?= htmlspecialchars($submitLabel) ?>
        </button>
      <?php endif; ?>

      <div class="login-register">
        Pas encore de compte ?
        <a href="<?= $baseUrl ?>/view/frontoffice/register.php">Creer un compte</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
