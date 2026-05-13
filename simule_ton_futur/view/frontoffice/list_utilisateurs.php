<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$pageTitle = 'Utilisateurs';
$terme = trim($_GET['search'] ?? '');
$tri = $_GET['tri'] ?? 'recent';
$liste = $terme !== '' ? $ctrl->search($terme, $tri) : $ctrl->listUtilisateurs($tri);

if (($_GET['export'] ?? '') === 'pdf') {
    $lines = [];
    foreach ($liste as $u) {
        $lines[] = sprintf(
            '%s %s | %s | %s | %s',
            $u->getPrenom(),
            $u->getNom(),
            $u->getEmail(),
            ucfirst($u->getRole()),
            ucfirst(str_replace('_', ' ', $u->getStatut()))
        );
    }
    stf_stream_simple_pdf('utilisateurs-frontoffice.pdf', 'Liste des utilisateurs', $lines);
}

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}
require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">Utilisateurs inscrits</h2>
      <p class="text-muted mb-0" style="font-size:.9rem">CRUD FrontOffice de l'entite utilisateur</p>
    </div>
    <a href="register.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
      <i class="fas fa-user-plus me-1"></i>Ajouter
    </a>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show mb-4">
      <?= htmlspecialchars($message['texte']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="GET" class="mb-4">
    <div class="d-flex flex-wrap gap-2" style="max-width:720px">
      <div class="input-group" style="max-width:420px">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par email" value="<?= htmlspecialchars($terme) ?>">
        <button class="btn btn-danger" type="submit"><i class="fas fa-search"></i></button>
      </div>
      <select name="tri" class="form-select" style="max-width:180px">
        <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus recent</option>
        <option value="nom_asc" <?= $tri === 'nom_asc' ? 'selected' : '' ?>>Nom A-Z</option>
        <option value="nom_desc" <?= $tri === 'nom_desc' ? 'selected' : '' ?>>Nom Z-A</option>
      </select>
      <button class="btn btn-outline-primary" type="submit">Trier</button>
      <a href="?search=<?= urlencode($terme) ?>&tri=<?= urlencode($tri) ?>&export=pdf" class="btn btn-outline-danger">
        <i class="fas fa-file-pdf me-1"></i>PDF
      </a>
      <?php if ($terme !== '' || $tri !== 'recent'): ?>
        <a href="list_utilisateurs.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </div>
  </form>

  <?php if (empty($liste)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-user-slash fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-0">Aucun utilisateur trouve.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($liste as $u): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar"><?= htmlspecialchars(strtoupper(substr($u->getPrenom(), 0, 1) . substr($u->getNom(), 0, 1))) ?></div>
              <div>
                <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($u->getPrenom() . ' ' . $u->getNom()) ?></div>
                <div class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($u->getEmail()) ?></div>
              </div>
            </div>
            <div class="mb-3">
              <span class="badge-<?= htmlspecialchars($u->getRole()) ?>"><?= htmlspecialchars(ucfirst($u->getRole())) ?></span>
              <span class="badge-statut badge-statut-<?= htmlspecialchars(str_replace('_', '-', $u->getStatut())) ?> ms-2">
                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $u->getStatut()))) ?>
              </span>
            </div>
            <div class="d-flex gap-2">
              <a href="edit_utilisateur.php?id=<?= $u->getIdUtilisateur() ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit me-1"></i>Modifier
              </a>
              <a href="delete_utilisateur.php?id=<?= $u->getIdUtilisateur() ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?')">
                <i class="fas fa-trash me-1"></i>Supprimer
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
