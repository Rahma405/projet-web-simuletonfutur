<?php
/**
 * export_all_pdf.php — Liste complète de tous les CV en PDF
 */
session_start();
require_once __DIR__ . '/../../controller/CvC.php';
require_once __DIR__ . '/../../controller/CompetenceC.php';

$ctrl     = new CvC();
$ctrlComp = new CompetenceC();

$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort']  ?? 'id';
$order  = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

if ($search !== '') {
    $cvs = $ctrl->searchCvs($search, $sort, $order);
} else {
    $cvs = $ctrl->listCvsSorted($sort, $order);
}

$date = date('d/m/Y');
$totalComps = $ctrlComp->countCompetences();
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste complète des CV — <?= $date ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Arial,sans-serif; }
body { background:#f0f2f8; }

.print-btn-bar {
  max-width: 900px;
  margin: 18px auto 10px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}
.btn-print {
  background: linear-gradient(135deg,#e63946,#c1121f);
  color: #fff; border: none; border-radius: 10px;
  padding: 10px 22px; font-size: .88rem; font-weight: 600;
  cursor: pointer; display: flex; align-items: center; gap: 8px;
}
.btn-back {
  background: #fff; color: #1d2b4f;
  border: 2px solid #1d2b4f; border-radius: 10px;
  padding: 10px 22px; font-size: .88rem; font-weight: 600;
  cursor: pointer; text-decoration: none;
  display: flex; align-items: center; gap: 8px;
}

.doc {
  max-width: 900px;
  margin: 0 auto 30px;
  background: #fff;
  box-shadow: 0 0 40px rgba(0,0,0,.12);
}

/* En-tête document */
.doc-header {
  background: linear-gradient(135deg, #1d2b4f, #0f1a36);
  padding: 30px 40px;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.doc-header h1 { font-size: 1.4rem; font-weight: 700; }
.doc-header .subtitle { color: #8899bb; font-size: .82rem; margin-top: 4px; }
.doc-header .stats { text-align: right; }
.doc-header .stat-val { font-size: 2rem; font-weight: 700; color: #e63946; line-height: 1; }
.doc-header .stat-lbl { font-size: .72rem; color: #8899bb; }
.accent-bar { height: 4px; background: linear-gradient(90deg, #e63946, #f4a261, #2a9d8f); }

/* Filtres affichés */
.doc-filter-info {
  background: #f8f9fb;
  padding: 10px 40px;
  font-size: .78rem;
  color: #8899bb;
  border-bottom: 1px solid #eef0f5;
}

/* Tableau */
.doc-body { padding: 30px 40px; }
table { width: 100%; border-collapse: collapse; }
thead tr { background: linear-gradient(135deg, #1d2b4f, #2a3f6f); color: #fff; }
thead th { padding: 12px 14px; font-size: .78rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
tbody tr { border-bottom: 1px solid #eef0f5; }
tbody tr:hover { background: #f8f9fb; }
tbody td { padding: 14px 14px; font-size: .82rem; color: #3a4567; vertical-align: middle; }
.badge { display: inline-block; font-size: .68rem; padding: 3px 10px; border-radius: 12px; font-weight: 600; }
.badge-id { background: #eef0f5; color: #8899bb; }
.badge-pdf { background: #fde8ea; color: #c1121f; }

.avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg,#e63946,#f4a261);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: .72rem;
  flex-shrink: 0;
}
.name-cell { display: flex; align-items: center; gap: 10px; }

/* Pied de page document */
.doc-footer {
  background: #f8f9fb;
  border-top: 2px solid #eef0f5;
  padding: 14px 40px;
  display: flex;
  justify-content: space-between;
  font-size: .72rem;
  color: #aab;
}

@media print {
  body { background: #fff; }
  .print-btn-bar { display: none !important; }
  .doc { margin: 0; box-shadow: none; max-width: 100%; }
  * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  a[href]::after { content: none !important; }
}
</style>
</head>
<body>

<div class="print-btn-bar">
  <a href="list_cvs.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
  <button class="btn-print" onclick="window.print()"><i class="fas fa-download"></i> Télécharger PDF</button>
</div>

<div class="doc">
  <div class="doc-header">
    <div>
      <h1><i class="fas fa-file-alt" style="color:#e63946;margin-right:10px"></i>Liste des CV</h1>
      <div class="subtitle">Portfolio Manager — Simule Ton Futur · Généré le <?= $date ?></div>
    </div>
    <div class="stats">
      <div class="stat-val"><?= count($cvs) ?></div>
      <div class="stat-lbl">CV listés</div>
    </div>
  </div>
  <div class="accent-bar"></div>

  <?php if($search): ?>
  <div class="doc-filter-info">
    <i class="fas fa-search me-1"></i>Filtre actif : « <?= htmlspecialchars($search) ?> »
  </div>
  <?php endif; ?>

  <div class="doc-body">
    <?php if(empty($cvs)): ?>
      <p style="text-align:center;color:#aab;padding:40px 0">Aucun CV trouvé.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Titre du poste</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Date naissance</th>
          <th>Adresse</th>
          <th>Liens</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($cvs as $i => $cv): ?>
      <tr>
        <td><span class="badge badge-id"><?= $cv['id'] ?></span></td>
        <td>
          <div class="name-cell">
            <div class="avatar"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
            <div>
              <div style="font-weight:600;color:#1d2b4f"><?= htmlspecialchars($cv['titre_poste']) ?></div>
              <?php if($cv['description']): ?>
                <div style="font-size:.72rem;color:#8899bb;margin-top:2px"><?= htmlspecialchars(mb_substr($cv['description'],0,60)) ?>…</div>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td><?= htmlspecialchars($cv['email']) ?></td>
        <td><?= htmlspecialchars($cv['telephone'] ?: '—') ?></td>
        <td><?= $cv['date_naissance'] ? date('d/m/Y', strtotime($cv['date_naissance'])) : '—' ?></td>
        <td><?= htmlspecialchars($cv['adresse'] ?: '—') ?></td>
        <td>
          <?php if($cv['github']): ?><span style="font-size:.72rem;color:#555"><i class="fab fa-github" style="color:#e63946"></i> GitHub</span><br><?php endif; ?>
          <?php if($cv['linkedin']): ?><span style="font-size:.72rem;color:#555"><i class="fab fa-linkedin" style="color:#457b9d"></i> LinkedIn</span><?php endif; ?>
          <?php if(!$cv['github'] && !$cv['linkedin']): ?>—<?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="doc-footer">
    <span><i class="fas fa-lock" style="margin-right:4px"></i>Document confidentiel — Administration</span>
    <span>Page 1 — <?= count($cvs) ?> CV — <?= $date ?></span>
  </div>
</div>

<script>
if (window.location.search.includes('autoprint=1')) {
  window.addEventListener('load', () => { setTimeout(() => window.print(), 500); });
}
</script>
</body>
</html>
