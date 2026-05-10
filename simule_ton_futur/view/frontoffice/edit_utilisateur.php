<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Veuillez vous connecter pour modifier votre compte.'];
    header('Location: login.php');
    exit;
}

if (($_SESSION['user']['statut'] ?? 'actif') === 'en_attente') {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => "Votre compte est en attente. Cette action n'est pas encore autorisee."];
    header('Location: list_utilisateurs.php');
    exit;
}

$ctrl = new UtilisateurC();
$erreurs = [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: list_utilisateurs.php');
    exit;
}

$u = $ctrl->getById($id);
if (!$u) {
    header('Location: list_utilisateurs.php');
    exit;
}

$sessionUser = $_SESSION['user'];
$estAdmin = ($sessionUser['role'] ?? '') === 'admin';
$faceIdEnabled = $ctrl->hasFaceDescriptor((int) $u->getIdUtilisateur());
if (!$estAdmin && (int) $sessionUser['id'] !== (int) $u->getIdUtilisateur()) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Vous ne pouvez modifier que votre propre compte.'];
    header('Location: ../../index.php');
    exit;
}

$pageTitle = 'Modifier utilisateur';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'motDePasse' => $_POST['motDePasse'] ?? '',
        'role' => $estAdmin ? ($_POST['role'] ?? $u->getRole()) : $u->getRole(),
        'statut' => $estAdmin ? ($_POST['statut'] ?? $u->getStatut()) : $u->getStatut(),
    ];

    $erreurs = $ctrl->valider($old, $id, false);
    $changerMdp = !empty($old['motDePasse']);

    if (empty($erreurs)) {
        $u->setNom($old['nom'])
          ->setPrenom($old['prenom'])
          ->setEmail($old['email'])
          ->setRole($old['role'])
          ->setStatut($old['statut'])
          ->setMotDePasse($old['motDePasse']);

        if ($ctrl->updateUtilisateur($u, $id, $changerMdp)) {
            if ((int) $sessionUser['id'] === $id) {
                $_SESSION['user']['nom'] = $u->getNom();
                $_SESSION['user']['prenom'] = $u->getPrenom();
                $_SESSION['user']['email'] = $u->getEmail();
                $_SESSION['user']['role'] = $u->getRole();
                $_SESSION['user']['statut'] = $u->getStatut();
            }
            $_SESSION['message'] = ['type' => 'success', 'texte' => 'Utilisateur modifie avec succes.'];
            header('Location: list_utilisateurs.php');
            exit;
        }

        $erreurs['global'] = "Erreur lors de la modification.";
    } else {
        $u->setNom($old['nom'])->setPrenom($old['prenom'])->setEmail($old['email'])->setRole($old['role'])->setStatut($old['statut']);
    }
}
require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card">
        <div style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;padding:16px 24px;border-radius:16px 16px 0 0;">
          <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2" style="color:#e63946"></i>Modifier utilisateur</h5>
        </div>
        <div class="card-body p-4">
          <?php if (isset($erreurs['global'])): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($erreurs['global']) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nom <span style="color:#e63946">*</span></label>
                <input type="text" name="nom" class="form-control <?= isset($erreurs['nom']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($u->getNom() ?? '') ?>">
                <?php if (isset($erreurs['nom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['nom']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Prenom <span style="color:#e63946">*</span></label>
                <input type="text" name="prenom" class="form-control <?= isset($erreurs['prenom']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($u->getPrenom() ?? '') ?>">
                <?php if (isset($erreurs['prenom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['prenom']) ?></div><?php endif; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Email <span style="color:#e63946">*</span></label>
              <input type="text" name="email" class="form-control <?= isset($erreurs['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($u->getEmail() ?? '') ?>">
              <?php if (isset($erreurs['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['email']) ?></div><?php endif; ?>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="motDePasse" class="form-control <?= isset($erreurs['motDePasse']) ? 'is-invalid' : '' ?>" placeholder="Laisser vide pour garder l'ancien">
                <?php if (isset($erreurs['motDePasse'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['motDePasse']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst($u->getRole())) ?>" readonly>
              </div>
            </div>

            <?php if ((int) $sessionUser['id'] === (int) $u->getIdUtilisateur()): ?>
              <div class="alert <?= $faceIdEnabled ? 'alert-success' : 'alert-warning' ?> mb-4">
                <i class="fas <?= $faceIdEnabled ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
                <?= $faceIdEnabled ? 'Face ID active pour ce compte.' : 'Face ID non active pour ce compte.' ?>
              </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
              <button type="submit" class="btn-hero" style="border-radius:10px;padding:10px 22px;">
                <i class="fas fa-save me-1"></i>Enregistrer
              </button>
              <?php if ((int) $sessionUser['id'] === (int) $u->getIdUtilisateur()): ?>
                <a href="face_register.php" class="btn btn-outline-primary" style="border-radius:10px;padding:10px 22px;">
                  <i class="fas fa-camera me-1"></i>Activer Face ID
                </a>
              <?php endif; ?>
              <a href="list_utilisateurs.php" class="btn btn-outline-secondary" style="border-radius:10px;padding:10px 22px;">Annuler</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
