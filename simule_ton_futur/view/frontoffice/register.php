<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';
require_once __DIR__ . '/../../controller/ProfilC.php';

$ctrlU = new UtilisateurC();
$ctrlP = new ProfilC();
$pageTitle = 'Inscription';
$erreurs = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'motDePasse' => $_POST['motDePasse'] ?? '',
        'role' => 'user',
        'bio' => trim($_POST['bio'] ?? ''),
        'ville' => trim($_POST['ville'] ?? ''),
        'pays' => trim($_POST['pays'] ?? ''),
        'langue' => $_POST['langue'] ?? '',
    ];

    $erreursU = $ctrlU->valider($old, 0, true);
    $erreursP = $ctrlP->valider(array_merge($old, ['idUtilisateur' => 1]));
    unset($erreursP['idUtilisateur']);
    $erreurs = array_merge($erreursU, $erreursP);

    if (empty($erreurs)) {
        $utilisateur = new Utilisateur(null, $old['nom'], $old['prenom'], $old['email'], $old['motDePasse'], 'user');
        $newId = $ctrlU->createUtilisateur($utilisateur);

        if ($newId !== null) {
            $profilRenseigne = $old['bio'] !== ''
                || $old['ville'] !== ''
                || $old['pays'] !== ''
                || $old['langue'] !== '';

            if ($profilRenseigne) {
                $profil = new Profil(null, $old['bio'], 'default.png', $old['ville'], $old['pays'], $old['langue'], $newId);
                $ctrlP->addProfil($profil);
            }

            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => "Bienvenue {$old['prenom']} ! Ton compte a ete cree avec succes.",
            ];
            header('Location: ../../index.php');
            exit;
        }

        $erreurs['global'] = "Erreur lors de la creation du compte.";
    }
}
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="text-center mb-4">
        <div style="font-size:2.8rem">STF</div>
        <h2 class="fw-bold" style="color:#1d2b4f">Creer mon compte</h2>
        <p class="text-muted" style="font-size:.88rem">Rejoins la communaute Simule Ton Futur</p>
      </div>

      <div class="card">
        <div style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;padding:16px 24px;border-radius:16px 16px 0 0;">
          <h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2" style="color:#e63946"></i>Informations du compte</h5>
        </div>
        <div class="card-body p-4">
          <?php if (isset($erreurs['global'])): ?>
            <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($erreurs['global']) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nom <span style="color:#e63946">*</span></label>
                <input type="text" name="nom" class="form-control <?= isset($erreurs['nom']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" placeholder="Votre nom">
                <?php if (isset($erreurs['nom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['nom']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Prenom <span style="color:#e63946">*</span></label>
                <input type="text" name="prenom" class="form-control <?= isset($erreurs['prenom']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" placeholder="Votre prenom">
                <?php if (isset($erreurs['prenom'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['prenom']) ?></div><?php endif; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Email <span style="color:#e63946">*</span></label>
              <input type="text" name="email" class="form-control <?= isset($erreurs['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="exemple@email.com">
              <?php if (isset($erreurs['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['email']) ?></div><?php endif; ?>
            </div>

            <div class="mb-4">
              <label class="form-label">Mot de passe <span style="color:#e63946">*</span></label>
              <input type="password" name="motDePasse" class="form-control <?= isset($erreurs['motDePasse']) ? 'is-invalid' : '' ?>" placeholder="Minimum 6 caracteres">
              <?php if (isset($erreurs['motDePasse'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['motDePasse']) ?></div><?php endif; ?>
            </div>

            <div class="mb-4">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" value="Utilisateur" readonly>
            </div>

            <div class="p-3 rounded mb-4" style="background:#f0f3fa;border-left:4px solid #e63946;">
              <div class="fw-semibold mb-3" style="font-size:.84rem;color:#1d2b4f"><i class="fas fa-id-card me-2" style="color:#e63946"></i>Informations du profil (optionnel)</div>

              <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea name="bio" rows="3" class="form-control <?= isset($erreurs['bio']) ? 'is-invalid' : '' ?>" placeholder="Parle de toi en quelques mots (max 500 caracteres)"><?= htmlspecialchars($old['bio'] ?? '') ?></textarea>
                <?php if (isset($erreurs['bio'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['bio']) ?></div><?php endif; ?>
              </div>

              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Ville</label>
                  <input type="text" name="ville" class="form-control <?= isset($erreurs['ville']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['ville'] ?? '') ?>" placeholder="Ex : Tunis">
                  <?php if (isset($erreurs['ville'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['ville']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Pays</label>
                  <input type="text" name="pays" class="form-control <?= isset($erreurs['pays']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['pays'] ?? '') ?>" placeholder="Ex : Tunisie">
                  <?php if (isset($erreurs['pays'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['pays']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Langue</label>
                  <select name="langue" class="form-select <?= isset($erreurs['langue']) ? 'is-invalid' : '' ?>">
                    <option value="">-- Choisir --</option>
                    <?php foreach (['Français', 'Arabe', 'Anglais', 'Espagnol', 'Allemand', 'Autre'] as $langue): ?>
                      <option value="<?= $langue ?>" <?= ($old['langue'] ?? '') === $langue ? 'selected' : '' ?>><?= $langue ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($erreurs['langue'])): ?><div class="invalid-feedback"><?= htmlspecialchars($erreurs['langue']) ?></div><?php endif; ?>
                </div>
              </div>
            </div>

            <button type="submit" class="btn-hero w-100" style="border-radius:10px;padding:13px;"><i class="fas fa-rocket me-2"></i>Creer mon compte</button>
            <div class="text-center mt-3"><a href="<?= $baseUrl ?>/index.php" style="color:#8899bb;font-size:.8rem;text-decoration:none;"><i class="fas fa-arrow-left me-1"></i>Retour a l'accueil</a></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
