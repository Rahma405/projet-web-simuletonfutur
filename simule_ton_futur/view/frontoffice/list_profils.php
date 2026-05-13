<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

$ctrl = new ProfilC();
$pageTitle = 'Profils';
$terme = trim($_GET['search'] ?? '');
$tri = $_GET['tri'] ?? 'recent';
$liste = $terme !== '' ? $ctrl->search($terme, $tri) : $ctrl->listProfils($tri);
$profilStats = $ctrl->getCompletionStats();
$sessionUser = $_SESSION['user'] ?? null;
$profilUtilisateur = $sessionUser ? $ctrl->getByIdUtilisateur((int) $sessionUser['id']) : null;

if (($_GET['export'] ?? '') === 'pdf') {
    $lines = [];
    foreach ($liste as $p) {
        $lines[] = sprintf(
            '%s %s | %s | %s | %s | %s',
            $p['prenom'],
            $p['nom'],
            $p['email'],
            $p['ville'] ?: '-',
            $p['pays'] ?: '-',
            $p['langue'] ?: '-'
        );
    }
    stf_stream_simple_pdf('profils-frontoffice.pdf', 'Liste des profils', $lines);
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
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">Profils de la communaute</h2>
    </div>
    <?php if ($sessionUser): ?>
      <?php if ($profilUtilisateur): ?>
        <a href="edit_profil.php?id=<?= $profilUtilisateur->getIdProfil() ?>" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
          <i class="fas fa-id-card me-1"></i>Mon profil
        </a>
      <?php else: ?>
        <a href="add_profil.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
          <i class="fas fa-plus me-1"></i>Ajouter mon profil
        </a>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show mb-4">
      <?= htmlspecialchars($message['texte']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card p-4 h-100">
        <div class="completion-stat-kicker">Moyenne</div>
        <div class="completion-stat-value"><?= $profilStats['average'] ?>%</div>
        <div class="completion-stat-text">des profils sont completes en moyenne</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-4 h-100">
        <div class="completion-stat-kicker">Ville top</div>
        <div class="completion-stat-value completion-stat-value-sm"><?= htmlspecialchars($profilStats['topCity']) ?></div>
        <div class="completion-stat-text">est la ville la plus representee</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-4 h-100">
        <div class="completion-stat-kicker">Profils complets</div>
        <div class="completion-stat-value"><?= $profilStats['fullCount'] ?></div>
        <div class="completion-stat-text">profils sont remplis a 100%</div>
      </div>
    </div>
  </div>

  <form method="GET" class="mb-4" data-sort-form>
    <div class="d-flex flex-wrap gap-2" style="max-width:720px">
      <div class="input-group" style="max-width:420px">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par email" value="<?= htmlspecialchars($terme) ?>">
        <button class="btn btn-danger" type="submit"><i class="fas fa-search"></i></button>
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
    <div class="card text-center p-5">
      <i class="fas fa-id-card fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-0">Aucun profil trouve.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($liste as $p): ?>
        <?php
          $estProprietaire = $sessionUser && (int) $sessionUser['id'] === (int) $p['idUtilisateur'];
          $estAdmin = $sessionUser && ($sessionUser['role'] ?? '') === 'admin';
          $photoName = trim((string) ($p['photoProfil'] ?? ''));
          $photoUrl = ($photoName !== '' && $photoName !== 'default.png')
            ? $baseUrl . '/view/assets/img/profiles/' . rawurlencode($photoName)
            : '';
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <?php if ($photoUrl !== ''): ?>
                <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo de <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>" class="avatar avatar-photo">
              <?php else: ?>
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($p['prenom'], 0, 1) . substr($p['nom'], 0, 1))) ?></div>
              <?php endif; ?>
              <div>
                <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                <div class="text-muted" style="font-size:.8rem"><?= htmlspecialchars($p['email']) ?></div>
              </div>
            </div>
            <p class="mb-3 text-muted" style="font-size:.86rem;min-height:48px;">
              <?= $p['bio'] ? htmlspecialchars(mb_substr($p['bio'], 0, 90)) . (mb_strlen($p['bio']) > 90 ? '...' : '') : 'Aucune bio renseignee.' ?>
            </p>
            <div class="d-flex flex-wrap gap-2 mb-3">
              <?php if (!empty($p['ville'])): ?><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['ville']) ?></span><?php endif; ?>
              <?php if (!empty($p['pays'])): ?><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['pays']) ?></span><?php endif; ?>
              <?php if (!empty($p['langue'])): ?><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['langue']) ?></span><?php endif; ?>
            </div>
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="completion-badge completion-badge-<?= (int) $p['completion'] >= 70 ? 'high' : ((int) $p['completion'] >= 40 ? 'mid' : 'low') ?>">
                  <?= htmlspecialchars($p['completionLabel']) ?>
                </span>
                <span class="fw-semibold" style="font-size:.82rem;color:#1d2b4f"><?= (int) $p['completion'] ?>%</span>
              </div>
              <div class="completion-track">
                <div class="completion-fill" style="width:<?= (int) $p['completion'] ?>%"></div>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-auto">
              <span class="badge-<?= htmlspecialchars($p['role']) ?>"><?= htmlspecialchars(ucfirst($p['role'])) ?></span>
              <?php if ($estProprietaire || $estAdmin): ?>
                <div class="d-flex gap-2">
                  <a href="edit_profil.php?id=<?= (int) $p['idProfil'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Modifier
                  </a>
                  <a href="delete_profil.php?id=<?= (int) $p['idProfil'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                    <i class="fas fa-trash me-1"></i>Supprimer
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
