<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

$ctrl = new ProfilC();
$pageTitle = 'Gestion des Profils';
$terme = trim($_GET['search'] ?? '');
$liste = $terme !== '' ? $ctrl->search($terme) : $ctrl->listProfils();

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}
require_once __DIR__ . '/layouts/header.php';
?>

<?php if ($message): ?>
  <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fas fa-id-card me-2"></i>Liste des Profils</span>
    <a href="add_profil.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
  </div>
  <div class="card-body p-4">

    <!-- Recherche -->
    <form method="GET" class="mb-4">
      <div class="input-group" style="max-width:400px">
        <input type="text" name="search" class="form-control"
          placeholder="Rechercher nom, ville, pays…"
          value="<?= htmlspecialchars($terme) ?>">
        <button class="btn btn-red" type="submit"><i class="fas fa-search"></i></button>
        <?php if ($terme): ?><a href="list_profils.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a><?php endif; ?>
      </div>
    </form>

    <?php if (empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-id-card fa-3x mb-3 text-muted"></i><p class="text-muted">Aucun profil trouvé.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr><th>#</th><th>Utilisateur</th><th>Bio</th><th>Ville</th><th>Pays</th><th>Langue</th><th>Rôle</th><th class="text-center">Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($liste as $p): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $p['idProfil'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar"><?= strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1)) ?></div>
                  <div>
                    <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></div>
                    <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($p['email']) ?></div>
                  </div>
                </div>
              </td>
              <td style="font-size:.8rem;max-width:180px">
                <?= $p['bio'] ? htmlspecialchars(mb_substr($p['bio'],0,60)).'…' : '<span class="text-muted">—</span>' ?>
              </td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['ville'] ?? '—') ?></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['pays'] ?? '—') ?></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['langue'] ?? '—') ?></td>
              <td><span class="badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span></td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <a href="edit_profil.php?id=<?= $p['idProfil'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                  <a href="delete_profil.php?id=<?= $p['idProfil'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
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
