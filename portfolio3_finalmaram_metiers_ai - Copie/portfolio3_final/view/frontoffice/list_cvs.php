<?php
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Tous les CV';

$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort']  ?? 'id';
$order  = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$toggle = $order === 'ASC' ? 'DESC' : 'ASC';

if ($search !== '') {
    $cvs = $ctrl->searchCvs($search, $sort, $order);
} else {
    $cvs = $ctrl->listCvsSorted($sort, $order);
}

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>
<style>
.search-bar input { border-radius: 10px 0 0 10px; border-right: 0; }
.search-bar .btn  { border-radius: 0 10px 10px 0; }
.sort-pill { display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:600;text-decoration:none;color:#8899bb;background:#eef0f5;transition:all .2s; }
.sort-pill:hover,.sort-pill.active { background:#1d2b4f;color:#fff; }
</style>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">Portfolios</h2>
      <p class="text-muted mb-0" style="font-size:.85rem"><?= count($cvs) ?> CV disponible(s)<?= $search ? ' pour «&nbsp;'.htmlspecialchars($search).'&nbsp;»' : '' ?></p>
    </div>
    <a href="../../index.php" class="btn-outline-hero"><i class="fas fa-arrow-left me-1"></i>Accueil</a>
  </div>

  <!-- Recherche + Tri -->
  <div class="d-flex flex-wrap gap-3 align-items-end mb-4">
    <form method="GET" class="d-flex search-bar flex-grow-1" style="max-width:400px">
      <input type="hidden" name="sort"  value="<?= htmlspecialchars($sort) ?>">
      <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
      <input type="text" name="search" class="form-control" placeholder="Rechercher par titre de poste…"
             value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-hero" type="submit" style="border-radius:0 10px 10px 0;padding:10px 18px"><i class="fas fa-search"></i></button>
      <?php if($search): ?>
        <a href="list_cvs.php" class="btn btn-outline-secondary ms-1" style="border-radius:10px" title="Effacer"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>

    <div class="d-flex gap-2 flex-wrap">
      <?php
      $buildUrl = fn($s) => 'list_cvs.php?sort='.$s.'&order='.($sort===$s?$toggle:'DESC').'&search='.urlencode($search);
      ?>
      <a href="<?= $buildUrl('id') ?>" class="sort-pill <?= $sort==='id'?'active':'' ?>">
        <i class="fas fa-hashtag"></i> Plus récent
        <?= $sort==='id' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
      </a>
      <a href="<?= $buildUrl('date_naissance') ?>" class="sort-pill <?= $sort==='date_naissance'?'active':'' ?>">
        <i class="fas fa-calendar"></i> Date
        <?= $sort==='date_naissance' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
      </a>
      <a href="<?= $buildUrl('titre_poste') ?>" class="sort-pill <?= $sort==='titre_poste'?'active':'' ?>">
        <i class="fas fa-sort-alpha-down"></i> Titre
        <?= $sort==='titre_poste' ? '<i class="fas fa-sort-'.($order==='ASC'?'up':'down').'"></i>' : '' ?>
      </a>
    </div>
  </div>

  <?php if(empty($cvs)): ?>
    <div class="card text-center p-5"><i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
      <p class="text-muted"><?= $search ? 'Aucun résultat pour «&nbsp;'.htmlspecialchars($search).'&nbsp;».' : 'Aucun CV disponible.' ?></p>
      <?php if($search): ?><a href="list_cvs.php" class="btn-hero mt-2">Voir tous les CV</a><?php endif; ?>
    </div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach($cvs as $cv): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
          <div>
            <div class="fw-bold" style="font-size:.9rem;color:#1d2b4f"><?= htmlspecialchars($cv['titre_poste']) ?></div>
            <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($cv['email']) ?></div>
          </div>
        </div>
        <?php if($cv['description']): ?>
          <p style="font-size:.8rem;color:#666;margin:0 0 14px;"><?= htmlspecialchars(mb_substr($cv['description'],0,90)) ?>…</p>
        <?php endif; ?>
        <?php if($cv['date_naissance']): ?>
          <p style="font-size:.76rem;color:#aab;margin:0 0 10px;"><i class="fas fa-calendar me-1" style="color:#e63946"></i><?= date('d/m/Y', strtotime($cv['date_naissance'])) ?></p>
        <?php endif; ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <?php if($cv['github']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-github me-1" style="color:#e63946"></i>GitHub</span><?php endif; ?>
          <?php if($cv['linkedin']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-linkedin me-1" style="color:#457b9d"></i>LinkedIn</span><?php endif; ?>
          <?php if($cv['telephone']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fas fa-phone me-1" style="color:#2a9d8f"></i><?= htmlspecialchars($cv['telephone']) ?></span><?php endif; ?>
        </div>
        <a href="show_cv.php?id=<?= $cv['id'] ?>" class="btn-hero d-block text-center" style="padding:8px;font-size:.82rem;">
          <i class="fas fa-eye me-1"></i>Voir le profil
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
