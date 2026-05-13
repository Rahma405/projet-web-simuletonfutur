<?php
require_once __DIR__ . '/../../controller/MetierC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrl = new MetierC();
$ctrlCv = new CvC();
$pageTitle = 'Dashboard Metiers';
$dashboard = $ctrl->statsDashboard();
$allCvs = $ctrlCv->listCvs();

$alertesParMetier = [];
foreach ($dashboard as $metier) {
    $alertes = [];
    foreach ($allCvs as $cv) {
        foreach ($ctrl->getRecommandations((int)$cv['id']) as $reco) {
            if ($reco->getId() === $metier->getId() && $reco->getScore() >= 70) {
                $alertes[] = ['titre' => $cv['titre_poste'], 'email' => $cv['email'], 'score' => $reco->getScore()];
                break;
            }
        }
    }
    $alertesParMetier[$metier->getId()] = $alertes;
}

require_once __DIR__ . '/../../templates/backoffice/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="fw-bold mb-1" style="color:#1d2b4f"><i class="fas fa-table me-2" style="color:#e63946"></i>Dashboard Metiers Avances</h3>
    <p class="text-muted mb-0" style="font-size:.83rem">Suivi des scores, recommandations et alertes CV >= 70%.</p>
  </div>
  <a href="../frontoffice/statistiques.php" class="btn btn-red"><i class="fas fa-chart-pie me-1"></i>Statistiques</a>
</div>

<?php if (empty($dashboard)): ?>
  <div class="card p-5 text-center text-muted">Aucun metier. Executez le fichier SQL des metiers avances.</div>
<?php else: ?>
  <div class="card">
    <div class="card-header">Metiers et compatibilite</div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Metier</th>
            <th>Secteur</th>
            <th>Salaire</th>
            <th class="text-center">CV >= 70%</th>
            <th class="text-center">Alerte</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dashboard as $m):
            $alertes = $alertesParMetier[$m->getId()] ?? [];
          ?>
            <tr>
              <td><strong style="color:#1d2b4f"><?= htmlspecialchars($m->getTitre()) ?></strong></td>
              <td><?= htmlspecialchars($m->getSecteur()) ?></td>
              <td><?= number_format($m->getSalaireMin(), 0, '', ' ') ?> - <?= number_format($m->getSalaireMax(), 0, '', ' ') ?> DT</td>
              <td class="text-center"><strong style="color:#2a9d8f;font-size:1.2rem"><?= $m->getNbCompatible() ?></strong></td>
              <td class="text-center">
                <?php $alertesB64 = base64_encode(json_encode($alertes, JSON_UNESCAPED_UNICODE)); ?>
                <button class="btn btn-sm btn-warning" onclick="afficherAlerteB64('<?= $alertesB64 ?>', '<?= htmlspecialchars($m->getTitre(), ENT_QUOTES) ?>')">
                  <i class="fas fa-bell"></i> Voir
                  <?php if ($alertes): ?><span class="badge bg-danger"><?= count($alertes) ?></span><?php endif; ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div id="alerteOverlay" onclick="fermerAlerte()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:10000"></div>
<div id="alertePopup" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10001;background:#fff;border-radius:16px;box-shadow:0 20px 70px rgba(0,0,0,.35);width:430px;max-width:calc(100vw - 36px);overflow:hidden">
  <div style="background:#f4a261;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center">
    <strong id="alerteTitre">Alerte</strong>
    <button onclick="fermerAlerte()" style="border:none;background:rgba(255,255,255,.25);color:#fff;border-radius:50%;width:30px;height:30px">&times;</button>
  </div>
  <div id="alerteBody" style="padding:18px 20px"></div>
</div>

<script>
function afficherAlerteB64(alertesB64, titre) {
  var alertes = [];
  try { alertes = JSON.parse(atob(alertesB64)); } catch(e) {}
  afficherAlerte(alertes, titre);
}
function afficherAlerte(alertes, titre) {
  document.getElementById('alerteTitre').textContent = 'Alerte - ' + titre;
  var body = document.getElementById('alerteBody');
  if (!alertes || alertes.length === 0) {
    body.innerHTML = '<div class="text-center text-muted py-4">Aucun CV n\\'atteint 70% pour ce metier.</div>';
  } else {
    body.innerHTML = alertes.map(function(a) {
      return '<div style="display:flex;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;background:#fff7ed;margin-bottom:8px">'
        + '<div><strong style="color:#1d2b4f">' + a.titre + '</strong><div style="font-size:.75rem;color:#8899bb">' + a.email + '</div></div>'
        + '<strong style="font-size:1.4rem;color:#2a9d8f">' + a.score + '%</strong></div>';
    }).join('');
  }
  document.getElementById('alerteOverlay').style.display = 'block';
  document.getElementById('alertePopup').style.display = 'block';
}
function fermerAlerte() {
  document.getElementById('alerteOverlay').style.display = 'none';
  document.getElementById('alertePopup').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../../templates/backoffice/footer.php'; ?>
