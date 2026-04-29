<?php
session_start();
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl      = new ExperienceC();
$pageTitle = 'Expériences Générales';

$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'default';

if ($search !== '') {
    $liste = $ctrl->searchExperiencesByTitre($search);
} elseif ($sort === 'date_asc') {
    $liste = $ctrl->listExperiencesSortedByDate('ASC');
} elseif ($sort === 'date_desc') {
    $liste = $ctrl->listExperiencesSortedByDate('DESC');
} else {
    $liste = $ctrl->listExperiences();
}

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f">
        <i class="fas fa-history me-2" style="color:#2a9d8f"></i>Toutes les Expériences
      </h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?= count($liste) ?> expérience(s)</p>
    </div>
    <a href="../../index.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;background:#2a9d8f;border-color:#2a9d8f;">
      <i class="fas fa-home me-1"></i>Accueil
    </a>
  </div>

  <!-- Barre de recherche + tri + export PDF -->
  <div class="card p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label mb-1" style="font-size:.78rem;color:#8899bb;font-weight:600">RECHERCHER PAR TITRE</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text" style="background:#f7f8fb;border-color:#e2e8f0;"><i class="fas fa-search" style="color:#8899bb;"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Ex: Développeur, Stage…"
                 value="<?= htmlspecialchars($search) ?>" style="border-color:#e2e8f0;font-size:.84rem;">
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label mb-1" style="font-size:.78rem;color:#8899bb;font-weight:600">TRIER PAR DATE DE DÉBUT</label>
        <select name="sort" class="form-select form-select-sm" style="border-color:#e2e8f0;font-size:.84rem;">
          <option value="default"   <?= $sort === 'default'   ? 'selected' : '' ?>>Par défaut</option>
          <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Plus récent en premier</option>
          <option value="date_asc"  <?= $sort === 'date_asc'  ? 'selected' : '' ?>>Plus ancien en premier</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm flex-fill" style="background:#2a9d8f;color:#fff;border:none;font-size:.82rem;">
          <i class="fas fa-filter me-1"></i>Filtrer
        </button>
        <?php if ($search || $sort !== 'default'): ?>
          <a href="list_experiences.php" class="btn btn-sm btn-outline-secondary" style="font-size:.82rem;" title="Réinitialiser">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if (empty($liste)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucune expérience trouvée<?= $search ? ' pour "'.htmlspecialchars($search).'"' : '' ?>.</p>
    </div>
  <?php else: ?>
  <!-- Bouton export PDF -->
  <div class="text-end mb-3">
    <button onclick="exportPDF()" class="btn btn-sm" style="background:#2a9d8f;color:#fff;font-size:.82rem;border:none;padding:7px 16px;border-radius:8px;">
      <i class="fas fa-file-pdf me-1"></i>Exporter en PDF
    </button>
  </div>

    <div class="row g-3" id="exp-list">
      <?php foreach ($liste as $exp): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4 exp-card"
             data-titre="<?= htmlspecialchars($exp->getTitre()) ?>"
             data-entreprise="<?= htmlspecialchars($exp->getEntreprise()) ?>"
             data-lieu="<?= htmlspecialchars($exp->getLieu()) ?>"
             data-debut="<?= date('m/Y', strtotime($exp->getDateDebut())) ?>"
             data-fin="<?= $exp->isEnCours() ? 'En cours' : ($exp->getDateFin() ? date('m/Y', strtotime($exp->getDateFin())) : '') ?>"
             data-contrat="<?= htmlspecialchars($exp->getTypeContrat()) ?>"
             data-desc="<?= htmlspecialchars(mb_substr($exp->getDescription(), 0, 100)) ?>">
          <div class="d-flex align-items-start gap-3 mb-2">
            <div style="width:46px;height:46px;border-radius:12px;background:rgba(42,157,143,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-briefcase" style="color:#2a9d8f;font-size:1.1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?= htmlspecialchars($exp->getTitre()) ?></div>
              <div style="font-size:.78rem;color:#457b9d;font-weight:600;"><?= htmlspecialchars($exp->getEntreprise()) ?></div>
              <div style="font-size:.73rem;color:#8899bb;"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($exp->getLieu()) ?></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span style="font-size:.72rem;color:#8899bb;">
              <i class="fas fa-calendar me-1"></i>
              <?= date('m/Y', strtotime($exp->getDateDebut())) ?> —
              <?= $exp->isEnCours() ? '<span style="color:#2a9d8f;font-weight:600">En cours</span>' : date('m/Y', strtotime($exp->getDateFin())) ?>
            </span>
            <span style="font-size:.7rem;font-weight:600;padding:2px 10px;border-radius:20px;background:rgba(69,123,157,.1);color:#457b9d;">
              <?= htmlspecialchars($exp->getTypeContrat()) ?>
            </span>
          </div>
          <?php if ($exp->getDescription()): ?>
            <p style="font-size:.78rem;color:#666;margin:0;"><?= htmlspecialchars(mb_substr($exp->getDescription(), 0, 100)) ?>…</p>
          <?php endif; ?>
          <?php if ($exp->getTitrePoste()): ?>
            <div class="mt-2" style="font-size:.72rem;color:#8899bb;border-top:1px solid #eee;padding-top:8px;">
              <i class="fas fa-id-card me-1" style="color:#e63946"></i>CV : <?= htmlspecialchars($exp->getTitrePoste()) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- jsPDF via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

  const cards = document.querySelectorAll('.exp-card');
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();

  // En-tête
  doc.setFillColor(42, 157, 143);
  doc.rect(0, 0, pageW, 14, 'F');
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(13);
  doc.setTextColor(255, 255, 255);
  doc.text('Liste des Expériences — Portfolio', pageW / 2, 9, { align: 'center' });

  doc.setTextColor(200, 235, 230);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  const now = new Date().toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric' });
  doc.text('Exporté le ' + now + ' — ' + cards.length + ' expérience(s)', pageW / 2, 19, { align: 'center' });

  let y = 28;

  cards.forEach((card, i) => {
    const titre     = card.dataset.titre     || '—';
    const entreprise= card.dataset.entreprise|| '';
    const lieu      = card.dataset.lieu      || '';
    const debut     = card.dataset.debut     || '';
    const fin       = card.dataset.fin       || '';
    const contrat   = card.dataset.contrat   || '';
    const desc      = card.dataset.desc      || '';

    const blockH = 34;
    if (y + blockH > pageH - 10) {
      doc.addPage();
      y = 15;
    }

    doc.setFillColor(i % 2 === 0 ? 247 : 255, i % 2 === 0 ? 248 : 255, i % 2 === 0 ? 251 : 255);
    doc.roundedRect(10, y, pageW - 20, blockH, 3, 3, 'F');
    doc.setDrawColor(226, 232, 240);
    doc.roundedRect(10, y, pageW - 20, blockH, 3, 3, 'S');

    // Icône briefcase simulée
    doc.setFillColor(42, 157, 143, 0.15);
    doc.setFillColor(220, 246, 242);
    doc.roundedRect(13, y + 4, 10, 10, 2, 2, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8);
    doc.setTextColor(42, 157, 143);
    doc.text('EXP', 18, y + 10.5, { align: 'center' });

    // Titre
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(29, 43, 79);
    doc.text(titre, 27, y + 8);

    // Entreprise + lieu
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(69, 123, 157);
    doc.text(entreprise + (lieu ? ' — ' + lieu : ''), 27, y + 14);

    // Dates + contrat
    doc.setTextColor(136, 153, 187);
    doc.setFontSize(7.5);
    doc.text(debut + ' → ' + fin + (contrat ? '  |  ' + contrat : ''), 27, y + 19);

    // Description
    if (desc) {
      doc.setFontSize(7.5);
      doc.setTextColor(100, 100, 100);
      const lines = doc.splitTextToSize(desc + '…', pageW - 42);
      doc.text(lines.slice(0, 2), 27, y + 25);
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

  doc.save('liste_experiences_' + new Date().toISOString().slice(0,10) + '.pdf');
}
</script>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
