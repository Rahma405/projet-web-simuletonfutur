<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl    = new UtilisateurC();
$erreurs = [];
$id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: list_utilisateurs.php'); exit; }

$u = $ctrl->getById($id);
if (!$u)  { header('Location: list_utilisateurs.php'); exit; }

$pageTitle = 'Modifier : ' . $u->getPrenom() . ' ' . $u->getNom();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nom'        => trim($_POST['nom']        ?? ''),
        'prenom'     => trim($_POST['prenom']      ?? ''),
        'email'      => trim($_POST['email']       ?? ''),
        'motDePasse' => $_POST['motDePasse']       ?? '',
        'role'       => $_POST['role']             ?? '',
    ];

    $erreurs    = $ctrl->valider($old, $id, false);
    $changerMdp = !empty($old['motDePasse']);

    if (empty($erreurs)) {
        $u->setNom($old['nom'])->setPrenom($old['prenom'])
          ->setEmail($old['email'])->setRole($old['role'])
          ->setMotDePasse($old['motDePasse']);

        if ($ctrl->updateUtilisateur($u, $id, $changerMdp)) {
            $_SESSION['message'] = ['type'=>'success','texte'=>"✅ Utilisateur modifié avec succès."];
            header('Location: list_utilisateurs.php');
            exit;
        }
        $erreurs['global'] = "Erreur lors de la modification.";
    } else {
        $u->setNom($old['nom'])->setPrenom($old['prenom'])
          ->setEmail($old['email'])->setRole($old['role']);
    }
}

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><i class="fas fa-user-edit me-2"></i>Modifier : <?= htmlspecialchars($u->getPrenom().' '.$u->getNom()) ?></div>
      <div class="card-body p-4">

        <?php if (isset($erreurs['global'])): ?>
          <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreurs['global']) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nom <span style="color:#e63946">*</span></label>
              <input type="text" name="nom"
                class="form-control <?= isset($erreurs['nom'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($u->getNom() ?? '') ?>">
              <?php if (isset($erreurs['nom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['nom']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Prénom <span style="color:#e63946">*</span></label>
              <input type="text" name="prenom"
                class="form-control <?= isset($erreurs['prenom'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($u->getPrenom() ?? '') ?>">
              <?php if (isset($erreurs['prenom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['prenom']) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email <span style="color:#e63946">*</span></label>
            <input type="text" name="email"
              class="form-control <?= isset($erreurs['email'])?'is-invalid':'' ?>"
              value="<?= htmlspecialchars($u->getEmail() ?? '') ?>">
            <?php if (isset($erreurs['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['email']) ?></div><?php endif; ?>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide = inchangé)</small></label>
              <input type="password" name="motDePasse"
                class="form-control <?= isset($erreurs['motDePasse'])?'is-invalid':'' ?>"
                placeholder="Minimum 6 caractères">
              <?php if (isset($erreurs['motDePasse'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['motDePasse']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Rôle <span style="color:#e63946">*</span></label>
              <select name="role" class="form-select <?= isset($erreurs['role'])?'is-invalid':'' ?>">
                <option value="user"  <?= $u->getRole()==='user' ?'selected':'' ?>>Utilisateur</option>
                <option value="admin" <?= $u->getRole()==='admin'?'selected':'' ?>>Administrateur</option>
              </select>
              <?php if (isset($erreurs['role'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['role']) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-red px-4"><i class="fas fa-save me-2"></i>Enregistrer</button>
            <a href="list_utilisateurs.php" class="btn btn-outline-secondary px-4"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
