<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$pageTitle = 'Gestion des Utilisateurs';
$terme = trim($_GET['search'] ?? '');
$tri = $_GET['tri'] ?? 'recent';
$liste = $terme !== '' ? $ctrl->search($terme, $tri) : $ctrl->listUtilisateurs($tri);
$stats = $ctrl->getStats();

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
    stf_stream_simple_pdf('utilisateurs-backoffice.pdf', 'Liste des utilisateurs', $lines);
}

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}
require_once __DIR__ . '/layouts/header.php';
?>

<!-- STATS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card red"><div><div class="val"><?= $stats['total'] ?></div><div class="lbl">Total utilisateurs</div></div><i class="fas fa-users"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card blue"><div><div class="val"><?= $stats['admins'] ?></div><div class="lbl">Administrateurs</div></div><i class="fas fa-shield-alt"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card green"><div><div class="val"><?= $stats['users'] ?></div><div class="lbl">Utilisateurs</div></div><i class="fas fa-user"></i></div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fas fa-users me-2"></i>Liste des Utilisateurs</span>
    <a href="add_utilisateur.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">

    <!-- Recherche -->
    <form method="GET" class="mb-4">
      <div class="d-flex flex-wrap gap-2" style="max-width:760px">
        <div class="input-group" style="max-width:400px">
          <input type="text" name="search" class="form-control"
            placeholder="Rechercher par email"
            value="<?= htmlspecialchars($terme) ?>">
          <button class="btn btn-red" type="submit"><i class="fas fa-search"></i></button>
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
        <?php if ($terme || $tri !== 'recent'): ?><a href="list_utilisateurs.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
      </div>
    </form>

    <?php if (empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-user-slash fa-3x mb-3 text-muted"></i><p class="text-muted">Aucun utilisateur trouvé.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Statut</th><th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($liste as $u): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $u->getIdUtilisateur() ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar"><?= strtoupper(substr($u->getPrenom(),0,1).substr($u->getNom(),0,1)) ?></div>
                  <span class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($u->getPrenom().' '.$u->getNom()) ?></span>
                </div>
              </td>
              <td style="font-size:.83rem"><?= htmlspecialchars($u->getEmail()) ?></td>
              <td><span class="badge-<?= $u->getRole() ?>"><?= ucfirst($u->getRole()) ?></span></td>
              <td>
                <span class="badge-statut badge-statut-<?= htmlspecialchars(str_replace('_', '-', $u->getStatut())) ?>">
                  <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $u->getStatut()))) ?>
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <a href="edit_utilisateur.php?id=<?= $u->getIdUtilisateur() ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                  <a href="delete_utilisateur.php?id=<?= $u->getIdUtilisateur() ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($liste) ?> résultat(s)</small>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
