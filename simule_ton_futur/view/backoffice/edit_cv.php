<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Modifier le CV';
$id        = (int)($_GET['id'] ?? 0);
$cv        = $ctrl->getCv($id);
$errors    = [];

if (!$cv) { header('Location: list_cvs.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_map('trim', $_POST);
    $errors = $ctrl->edit($id, $data);
    if (empty($errors)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>'CV modifié avec succès !'];
        header('Location: list_cvs.php'); exit;
    }
    $cv = array_merge($cv, $data);
}
require_once __DIR__ . '/layouts/header.php';
?>
<div class="card">
  <div class="card-header"><i class="fas fa-edit me-2"></i>Modifier le CV #<?= $id ?></div>
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Email *</label>
          <input type="text" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($cv['email']) ?>">
          <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Titre du poste *</label>
          <input type="text" name="titre_poste" class="form-control <?= isset($errors['titre_poste'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($cv['titre_poste']) ?>">
          <?php if(isset($errors['titre_poste'])): ?><div class="invalid-feedback"><?= $errors['titre_poste'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control <?= isset($errors['telephone'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($cv['telephone']) ?>">
          <?php if(isset($errors['telephone'])): ?><div class="invalid-feedback"><?= $errors['telephone'] ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Date de naissance</label>
          <input type="text" name="date_naissance" class="form-control" value="<?= htmlspecialchars($cv['date_naissance']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Adresse</label>
          <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($cv['adresse']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">GitHub</label>
          <input type="text" name="github" class="form-control" value="<?= htmlspecialchars($cv['github']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">LinkedIn</label>
          <input type="text" name="linkedin" class="form-control" value="<?= htmlspecialchars($cv['linkedin']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Site web</label>
          <input type="text" name="site_web" class="form-control" value="<?= htmlspecialchars($cv['site_web']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Compétences <small class="text-muted">(pour la génération IA)</small></label>
          <input type="text" id="competences_ia" class="form-control" placeholder="Ex: PHP, JavaScript, MySQL, Bootstrap...">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <div class="d-flex gap-2 mb-2">
            <button type="button" id="btn-generer-ia" class="btn btn-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:20px;padding:6px 18px;font-size:.82rem;font-weight:600;">
              <i class="fas fa-magic me-1"></i>Générer avec IA
            </button>
            <span id="ia-loading" style="display:none;font-size:.82rem;color:#8899bb;align-self:center;">
              <i class="fas fa-spinner fa-spin me-1"></i>Génération en cours...
            </span>
          </div>
          <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($cv['description']) ?></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-red"><i class="fas fa-save me-1"></i>Enregistrer</button>
        <a href="list_cvs.php" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('btn-generer-ia').addEventListener('click', function() {
    const titrePoste  = document.querySelector('input[name="titre_poste"]').value.trim();
    const competences = document.getElementById('competences_ia').value.trim();
    const btn         = this;
    const loading     = document.getElementById('ia-loading');
    const desc        = document.getElementById('description');

    if (!titrePoste) { alert('Veuillez remplir le titre du poste d\'abord.'); return; }

    btn.disabled = true;
    loading.style.display = 'inline';

    const formData = new FormData();
    formData.append('titre_poste', titrePoste);
    formData.append('competences', competences);

    fetch('<?= $baseUrl ?>/controller/generate_description.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                desc.value = data.description;
                desc.style.borderColor = '#6366f1';
                setTimeout(() => desc.style.borderColor = '', 2000);
            } else {
                alert('Erreur : ' + data.error);
            }
        })
        .catch(() => alert('Erreur de connexion au serveur.'))
        .finally(() => { btn.disabled = false; loading.style.display = 'none'; });
});
</script>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
