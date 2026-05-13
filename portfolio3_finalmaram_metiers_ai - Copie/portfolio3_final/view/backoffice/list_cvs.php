<?php
session_start();
require_once __DIR__ . '/../../controller/CvC.php';
require_once __DIR__ . '/../../controller/CompetenceC.php';

$ctrl      = new CvC();
$ctrlComp  = new CompetenceC();
$pageTitle = 'Gestion des CV';

// Paramètres tri & recherche
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort']  ?? 'id';
$order  = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$toggle = $order === 'ASC' ? 'DESC' : 'ASC';

if ($search !== '') {
    $liste = $ctrl->searchCvs($search, $sort, $order);
} else {
    $liste = $ctrl->listCvsSorted($sort, $order);
}

$totalCvs  = $ctrl->countCvs();
$totalComps= $ctrlComp->countCompetences();

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<style>
.search-bar input { border-radius: 10px 0 0 10px; border-right: 0; }
.search-bar .btn  { border-radius: 0 10px 10px 0; }
.sort-btn { font-size:.78rem; color:#8899bb; text-decoration:none; white-space:nowrap; }
.sort-btn:hover { color:#e63946; }
.sort-btn i { font-size:.7rem; }
.btn-export-pdf { background:linear-gradient(135deg,#e63946,#c1121f);color:#fff;border:none;border-radius:10px;padding:7px 16px;font-size:.82rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:opacity .2s; }
.btn-export-pdf:hover { opacity:.88; color:#fff; }
.btn-export-single { background:linear-gradient(135deg,#2a9d8f,#264653);color:#fff;border:none;border-radius:8px;padding:4px 10px;font-size:.75rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:opacity .2s; }
.btn-export-single:hover { opacity:.85; color:#fff; }
</style>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card red"><div><div class="val"><?= $totalCvs ?></div><div class="lbl">Total CV</div></div><i class="fas fa-file-alt"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card blue"><div><div class="val"><?= $totalComps ?></div><div class="lbl">Compétences</div></div><i class="fas fa-star"></i></div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card green"><div><div class="val"><?= $totalCvs > 0 ? round($totalComps/$totalCvs,1) : 0 ?></div><div class="lbl">Comp. / CV</div></div><i class="fas fa-chart-bar"></i></div>
  </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4">
  <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span><i class="fas fa-file-alt me-2"></i>Liste des CV</span>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <!-- Export liste complète PDF -->
      <a href="export_all_pdf.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="btn-export-pdf" target="_blank">
        <i class="fas fa-file-pdf"></i> Exporter tout en PDF
      </a>
      <a href="add_cv.php" class="btn btn-sm btn-red"><i class="fas fa-plus me-1"></i>Ajouter</a>
    </div>
  </div>

  <div class="card-body p-4">

    <!-- Barre recherche + tri -->
    <div class="d-flex flex-wrap gap-3 align-items-end mb-4">
      <form method="GET" class="d-flex search-bar flex-grow-1" style="max-width:420px">
        <input type="hidden" name="sort"  value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par titre de poste…"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-red" type="submit"><i class="fas fa-search"></i></button>
        <?php if($search): ?>
          <a href="list_cvs.php" class="btn btn-outline-secondary ms-1" title="Effacer"><i class="fas fa-times"></i></a>
        <?php endif; ?>
      </form>

      <div class="d-flex gap-2 align-items-center">
        <span style="font-size:.8rem;color:#8899bb">Trier par :</span>
        <?php
        $buildUrl = fn($s) => 'list_cvs.php?sort='.$s.'&order='.($sort===$s?$toggle:'DESC').'&search='.urlencode($search);
        ?>
        <a href="<?= $buildUrl('id') ?>" class="sort-btn <?= $sort==='id'?'fw-bold':''; ?>">
          <i class="fas fa-hashtag me-1"></i>ID <?= $sort==='id' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
        </a>
        <a href="<?= $buildUrl('date_naissance') ?>" class="sort-btn <?= $sort==='date_naissance'?'fw-bold':''; ?>">
          <i class="fas fa-calendar me-1"></i>Date <?= $sort==='date_naissance' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
        </a>
        <a href="<?= $buildUrl('titre_poste') ?>" class="sort-btn <?= $sort==='titre_poste'?'fw-bold':''; ?>">
          <i class="fas fa-sort-alpha-down me-1"></i>Titre <?= $sort==='titre_poste' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
        </a>
      </div>
    </div>

    <?php if (empty($liste)): ?>
      <div class="text-center py-5"><i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
        <p class="text-muted"><?= $search ? 'Aucun CV trouvé pour « '.htmlspecialchars($search).' ».' : 'Aucun CV trouvé.' ?></p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Titre du poste</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Date naissance</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($liste as $cv): ?>
        <tr>
          <td style="color:#8899bb;font-size:.78rem"><?= $cv['id'] ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
              <span class="fw-semibold" style="font-size:.86rem"><?= htmlspecialchars($cv['titre_poste']) ?></span>
            </div>
          </td>
          <td style="font-size:.83rem"><?= htmlspecialchars($cv['email']) ?></td>
          <td style="font-size:.83rem"><?= htmlspecialchars($cv['telephone']) ?></td>
          <td style="font-size:.83rem"><?= $cv['date_naissance'] ? date('d/m/Y', strtotime($cv['date_naissance'])) : '—' ?></td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center flex-wrap">
              <a href="show_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Voir"><i class="fas fa-eye"></i></a>
              <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
              <a href="export_pdf_cv.php?id=<?= $cv['id'] ?>" class="btn-export-single" title="Exporter PDF" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
              <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <small class="text-muted"><?= count($liste) ?> CV trouvé(s)<?= $search ? ' pour «&nbsp;'.htmlspecialchars($search).'&nbsp;»' : '' ?></small>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
