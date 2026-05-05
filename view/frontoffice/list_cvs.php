<?php
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Tous les CV';

$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'default';

if ($search !== '') {
    $cvs = $ctrl->searchCvsByTitre($search);
} elseif ($sort === 'date_asc') {
    $cvs = $ctrl->listCvsSortedByDate('ASC');
} elseif ($sort === 'date_desc') {
    $cvs = $ctrl->listCvsSortedByDate('DESC');
} else {
    $cvs = $ctrl->listCvs();
}

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">Portfolios</h2>
      <p class="text-muted mb-0" style="font-size:.85rem"><?= count($cvs) ?> CV disponible(s)</p>
    </div>
    <a href="../../index.php" class="btn-outline-hero"><i class="fas fa-arrow-left me-1"></i>Accueil</a>
  </div>

  <!-- Barre de recherche + tri + export PDF -->
  <div class="card p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label mb-1" style="font-size:.78rem;color:#8899bb;font-weight:600">RECHERCHER PAR TITRE</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text" style="background:#f7f8fb;border-color:#e2e8f0;"><i class="fas fa-search" style="color:#8899bb;"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Ex: Développeur web…"
                 value="<?= htmlspecialchars($search) ?>" style="border-color:#e2e8f0;font-size:.84rem;">
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label mb-1" style="font-size:.78rem;color:#8899bb;font-weight:600">TRIER PAR</label>
        <select name="sort" class="form-select form-select-sm" style="border-color:#e2e8f0;font-size:.84rem;">
          <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Par défaut</option>
          <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date de naissance (↓)</option>
          <option value="date_asc"  <?= $sort === 'date_asc'  ? 'selected' : '' ?>>Date de naissance (↑)</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-hero flex-fill" style="font-size:.82rem;">
          <i class="fas fa-filter me-1"></i>Filtrer
        </button>
        <?php if ($search || $sort !== 'default'): ?>
          <a href="list_cvs.php" class="btn btn-sm btn-outline-secondary" style="font-size:.82rem;" title="Réinitialiser">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if(empty($cvs)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucun CV trouvé<?= $search ? ' pour "'.htmlspecialchars($search).'"' : '' ?>.</p>
    </div>
  <?php else: ?>
  <!-- Bouton export PDF -->
  <div class="text-end mb-3">
    <button onclick="exportPDF()" class="btn btn-sm" style="background:#e63946;color:#fff;font-size:.82rem;border:none;padding:7px 16px;border-radius:8px;">
      <i class="fas fa-file-pdf me-1"></i>Exporter en PDF
    </button>
  </div>

  <div class="row g-3" id="cv-list">
    <?php foreach($cvs as $cv): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-4 cv-card"
           data-titre="<?= htmlspecialchars($cv->getTitrePoste()) ?>"
           data-email="<?= htmlspecialchars($cv->getEmail()) ?>"
           data-tel="<?= htmlspecialchars($cv->getTelephone()) ?>"
           data-desc="<?= htmlspecialchars(mb_substr($cv->getDescription(), 0, 90)) ?>">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar"><?= strtoupper(substr($cv->getTitrePoste(), 0, 2)) ?></div>
          <div>
            <div class="fw-bold" style="font-size:.9rem;color:#1d2b4f"><?= htmlspecialchars($cv->getTitrePoste()) ?></div>
            <div style="font-size:.75rem;color:#8899bb"><?= htmlspecialchars($cv->getEmail()) ?></div>
          </div>
        </div>
        <?php if($cv->getDescription()): ?>
          <p style="font-size:.8rem;color:#666;margin:0 0 14px;"><?= htmlspecialchars(mb_substr($cv->getDescription(), 0, 90)) ?>…</p>
        <?php endif; ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <?php if($cv->getGithub()): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-github me-1" style="color:#e63946"></i>GitHub</span><?php endif; ?>
          <?php if($cv->getLinkedin()): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-linkedin me-1" style="color:#457b9d"></i>LinkedIn</span><?php endif; ?>
          <?php if($cv->getTelephone()): ?><span style="font-size:.75rem;color:#8899bb"><i class="fas fa-phone me-1" style="color:#2a9d8f"></i><?= htmlspecialchars($cv->getTelephone()) ?></span><?php endif; ?>
        </div>
        <div class="d-flex gap-2">
          <a href="show_cv.php?id=<?= $cv->getId() ?>" class="btn-hero flex-fill text-center" style="padding:8px;font-size:.78rem;"><i class="fas fa-eye me-1"></i>Profil</a>
          <a href="export_pdf.php?id=<?= $cv->getId() ?>" target="_blank" class="btn-hero" style="padding:8px 12px;font-size:.78rem;background:linear-gradient(135deg,#e63946,#c1121f)" title="PDF"><i class="fas fa-file-pdf"></i></a>
          <a href="recommandation_metiers.php?cv_id=<?= $cv->getId() ?>" class="btn-hero" style="padding:8px 12px;font-size:.78rem;background:linear-gradient(135deg,#f4a261,#e07b30)" title="Recommandations"><i class="fas fa-star"></i></a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- jsPDF via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

  const cards = document.querySelectorAll('.cv-card');
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();
  let y = 20;

  // En-tête
  doc.setFillColor(230, 57, 70);
  doc.rect(0, 0, pageW, 14, 'F');
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(13);
  doc.setTextColor(255, 255, 255);
  doc.text('Liste des CV — Portfolio', pageW / 2, 9, { align: 'center' });

  doc.setTextColor(136, 153, 187);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  const now = new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric' });
  doc.text('Exporté le ' + now + ' — ' + cards.length + ' CV', pageW / 2, 19, { align: 'center' });

  y = 28;

  cards.forEach((card, i) => {
    const titre  = card.dataset.titre || '—';
    const email  = card.dataset.email || '';
    const tel    = card.dataset.tel   || '';
    const desc   = card.dataset.desc  || '';

    const blockH = 30;
    if (y + blockH > pageH - 10) {
      doc.addPage();
      y = 15;
    }

    // Fond alterné
    doc.setFillColor(i % 2 === 0 ? 247 : 255, i % 2 === 0 ? 248 : 255, i % 2 === 0 ? 251 : 255);
    doc.roundedRect(10, y, pageW - 20, blockH, 3, 3, 'F');
    doc.setDrawColor(226, 232, 240);
    doc.roundedRect(10, y, pageW - 20, blockH, 3, 3, 'S');

    // Avatar cercle
    doc.setFillColor(230, 57, 70);
    doc.circle(20, y + 10, 5, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(7);
    doc.setTextColor(255, 255, 255);
    doc.text(titre.substring(0, 2).toUpperCase(), 20, y + 12.5, { align: 'center' });

    // Titre poste
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(29, 43, 79);
    doc.text(titre, 28, y + 8);

    // Email + tél
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(136, 153, 187);
    if (email) doc.text('✉ ' + email, 28, y + 14);
    if (tel)   doc.text('☎ ' + tel, 28, y + 19);

    // Description
    if (desc) {
      doc.setFontSize(7.5);
      doc.setTextColor(100, 100, 100);
      const lines = doc.splitTextToSize(desc + '…', pageW - 42);
      doc.text(lines.slice(0, 2), 28, y + 24);
    }

    y += blockH + 4;
  });

  // Pied de page
  const totalPages = doc.internal.getNumberOfPages();
  for (let p = 1; p <= totalPages; p++) {
    doc.setPage(p);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7);
    doc.setTextColor(180, 180, 180);
    doc.text('Page ' + p + ' / ' + totalPages, pageW / 2, pageH - 5, { align: 'center' });
  }

  doc.save('liste_cv_' + new Date().toISOString().slice(0,10) + '.pdf');
}
</script>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
