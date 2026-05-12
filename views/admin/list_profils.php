<?php
session_start();
require_once __DIR__ . '/../../controllers/ProfilC.php';

$ctrl = new ProfilC();
$pageTitle = 'Gestion des Profils';
$terme = trim($_GET['search'] ?? '');
$tri = $_GET['tri'] ?? 'recent';
$liste = $terme !== '' ? $ctrl->search($terme, $tri) : $ctrl->listProfils($tri);
$profilStats = $ctrl->getCompletionStats();

if (($_GET['export'] ?? '') === 'pdf') {
    $lines = [];
    foreach ($liste as $p) {
        $lines[] = sprintf(
            '%s %s | %s | %s | %s | %s | %s%%',
            $p['prenom'],
            $p['nom'],
            $p['email'],
            $p['ville'] ?: '-',
            $p['pays'] ?: '-',
            $p['langue'] ?: '-',
            $p['completion'] ?? 0
        );
    }
    stf_stream_simple_pdf('profils-backoffice.pdf', 'Liste des profils', $lines);
}

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}

?>

<?php if ($message): ?>
  <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show mb-4">
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

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card red h-100">
          <div>
            <div class="val"><?= $profilStats['average'] ?>%</div>
            <div class="lbl">Completion moyenne</div>
          </div>
          <i class="fas fa-gauge-high"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card blue h-100">
          <div>
            <div class="val"><?= $profilStats['fullCount'] ?></div>
            <div class="lbl">Profils complets</div>
          </div>
          <i class="fas fa-trophy"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card orange h-100">
          <div>
            <div class="val" style="font-size:1.25rem"><?= htmlspecialchars($profilStats['topCity']) ?></div>
            <div class="lbl">Ville dominante</div>
          </div>
          <i class="fas fa-city"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card green h-100">
          <div>
            <div class="val" style="font-size:1rem"><?= htmlspecialchars($profilStats['topLabel']) ?></div>
            <div class="lbl">Profil champion</div>
          </div>
          <i class="fas fa-star"></i>
        </div>
      </div>
    </div>

    <form method="GET" class="mb-4" data-sort-form>
      <div class="d-flex flex-wrap gap-2" style="max-width:760px">
        <div class="input-group" style="max-width:400px">
          <input type="text" name="search" class="form-control" placeholder="Rechercher par email" value="<?= htmlspecialchars($terme) ?>">
          <button class="btn btn-red" type="submit"><i class="fas fa-search"></i></button>
        </div>
        <select name="tri" class="form-select" style="max-width:180px" data-sort-select>
          <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus recent</option>
          <option value="ville_asc" <?= $tri === 'ville_asc' ? 'selected' : '' ?>>Ville A-Z</option>
          <option value="ville_desc" <?= $tri === 'ville_desc' ? 'selected' : '' ?>>Ville Z-A</option>
        </select>
        <button class="btn btn-outline-primary" type="submit">Trier</button>
        <a href="?search=<?= urlencode($terme) ?>&tri=<?= urlencode($tri) ?>&export=pdf" class="btn btn-outline-danger">
          <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <?php if ($terme !== '' || $tri !== 'recent'): ?>
          <a href="list_profils.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        <?php endif; ?>
      </div>
    </form>

    <div data-sort-target>
    <?php if (empty($liste)): ?>
      <div class="text-center py-5">
        <i class="fas fa-id-card fa-3x mb-3 text-muted"></i>
        <p class="text-muted">Aucun profil trouve.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Utilisateur</th>
              <th>Bio</th>
              <th>Ville</th>
              <th>Pays</th>
              <th>Langue</th>
              <th>Completion</th>
              <th>Role</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($liste as $p): ?>
            <?php
              $photoName = trim((string) ($p['photoProfil'] ?? ''));
              $photoUrl = ($photoName !== '' && $photoName !== 'default.png')
                ? $baseUrl . '/public/img/profiles/' . rawurlencode($photoName)
                : '';
            ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= (int) $p['idProfil'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if ($photoUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo de <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>" class="avatar avatar-photo">
                  <?php else: ?>
                    <div class="avatar"><?= strtoupper(substr($p['prenom'], 0, 1) . substr($p['nom'], 0, 1)) ?></div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                    <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($p['email']) ?></div>
                  </div>
                </div>
              </td>
              <td style="font-size:.8rem;max-width:180px">
                <?= $p['bio'] ? htmlspecialchars(mb_substr($p['bio'], 0, 60)) . '...' : '<span class="text-muted">-</span>' ?>
              </td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['ville'] ?: '-') ?></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['pays'] ?: '-') ?></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($p['langue'] ?: '-') ?></td>
              <td style="min-width:170px">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="completion-badge completion-badge-<?= (int) $p['completion'] >= 70 ? 'high' : ((int) $p['completion'] >= 40 ? 'mid' : 'low') ?>">
                    <?= htmlspecialchars($p['completionLabel']) ?>
                  </span>
                  <span style="font-size:.78rem;font-weight:700;color:#1d2b4f"><?= (int) $p['completion'] ?>%</span>
                </div>
                <div class="completion-track">
                  <div class="completion-fill" style="width:<?= (int) $p['completion'] ?>%"></div>
                </div>
              </td>
              <td><span class="badge-<?= htmlspecialchars($p['role']) ?>"><?= htmlspecialchars(ucfirst($p['role'])) ?></span></td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <a href="edit_profil.php?id=<?= (int) $p['idProfil'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                  <a href="delete_profil.php?id=<?= (int) $p['idProfil'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($liste) ?> resultat(s)</small>
    <?php endif; ?>
    </div>
  </div>
</div>

<?php  ?>
