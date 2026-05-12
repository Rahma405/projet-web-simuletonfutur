<?php
session_start();
require_once __DIR__ . '/../../controller/OffreC.php';

$ctrl      = new OffreC();
$pageTitle = 'Gestion des Offres';

// Get search and sort parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'idOffre DESC';

$offres    = $ctrl->listOffres($search, $sort);

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/layouts/header.php';
?>

<!-- STATS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card red">
      <div><div class="val"><?= count($offres) ?></div><div class="lbl">Offres publiées</div></div>
      <i class="fas fa-briefcase"></i>
    </div>
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
    <span><i class="fas fa-briefcase me-2"></i>Liste des Offres</span>
    <a href="add_offre.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter une offre</a>
  </div>
  <div class="card-body p-4">

    <!-- Search and Sort Form -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-6">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par titre, compétences ou localisation..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="col-md-4">
        <select name="sort" class="form-select">
          <option value="idOffre DESC" <?= $sort == 'idOffre DESC' ? 'selected' : '' ?>>Trier par ID (descendant)</option>
          <option value="idOffre ASC" <?= $sort == 'idOffre ASC' ? 'selected' : '' ?>>Trier par ID (ascendant)</option>
          <option value="titre ASC" <?= $sort == 'titre ASC' ? 'selected' : '' ?>>Trier par titre (A-Z)</option>
          <option value="titre DESC" <?= $sort == 'titre DESC' ? 'selected' : '' ?>>Trier par titre (Z-A)</option>
          <option value="localisation ASC" <?= $sort == 'localisation ASC' ? 'selected' : '' ?>>Trier par localisation (A-Z)</option>
          <option value="localisation DESC" <?= $sort == 'localisation DESC' ? 'selected' : '' ?>>Trier par localisation (Z-A)</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
      </div>
    </form>

    <?php if (empty($offres)): ?>
      <div class="text-center py-5">
        <i class="fas fa-briefcase fa-3x mb-3 text-muted"></i>
        <p class="text-muted">Aucune offre disponible.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Titre</th>
              <th>Compétences requises</th>
              <th>Localisation</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $colors = ['#e63946','#f4a261','#457b9d','#2a9d8f','#9b59b6','#e67e22','#3498db','#1abc9c'];
            foreach ($offres as $o):
                $comps = array_map('trim', explode(',', $o['competences']));
            ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $o['idOffre'] ?></td>
              <td class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($o['titre']) ?></td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  <?php foreach ($comps as $i => $c): ?>
                    <span class="badge" style="background:<?= $colors[$i % count($colors)] ?>20;color:<?= $colors[$i % count($colors)] ?>;font-size:.7rem;font-weight:600;padding:3px 8px;border-radius:20px">
                      <?= htmlspecialchars($c) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </td>
              <td style="font-size:.83rem">
                <i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i>
                <?= htmlspecialchars($o['localisation']) ?>
              </td>
              <td class="text-center">
                <a href="edit_offre.php?id=<?= $o['idOffre'] ?>"
                   class="btn btn-sm btn-outline-primary me-1"
                   title="Modifier">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="delete_offre.php?id=<?= $o['idOffre'] ?>"
                   class="btn btn-sm btn-outline-danger btn-delete"
                   title="Supprimer">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($offres) ?> offre(s)</small>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
