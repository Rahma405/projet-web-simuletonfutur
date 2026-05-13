<?php
require_once __DIR__ . '/../../controller/MetierC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrl = new MetierC();
$ctrlCv = new CvC();
$pageTitle = 'Statistiques Metiers';
$statsNiveaux = $ctrl->statsNiveaux();
$statsSecteurs = $ctrl->statsSecteurs();
$totalCvs = $ctrlCv->countCvs();
$totalMetiers = $ctrl->countMetiers();
$totalSecteurs = count($ctrl->getAllSecteurs());

$niveauxLabels = array_column($statsNiveaux, 'niveau');
$niveauxData = array_map('intval', array_column($statsNiveaux, 'total'));
$secteursLabels = array_column($statsSecteurs, 'secteur');
$secteursData = array_map('intval', array_column($statsSecteurs, 'total'));

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold" style="color:#1d2b4f">Statistiques AI</h2>
    <p class="text-muted" style="max-width:560px;margin:auto;font-size:.9rem">Suivi des niveaux de competences et des CV compatibles par secteur.</p>
  </div>

  <div class="row g-3 mb-5">
    <div class="col-md-4"><div class="card p-4 text-center"><div style="font-size:2rem;font-weight:800;color:#e63946"><?= $totalCvs ?></div><div class="text-muted">CV total</div></div></div>
    <div class="col-md-4"><div class="card p-4 text-center"><div style="font-size:2rem;font-weight:800;color:#2a9d8f"><?= $totalMetiers ?></div><div class="text-muted">Metiers avances</div></div></div>
    <div class="col-md-4"><div class="card p-4 text-center"><div style="font-size:2rem;font-weight:800;color:#f4a261"><?= $totalSecteurs ?></div><div class="text-muted">Secteurs</div></div></div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card p-4 h-100">
        <h5 class="fw-bold" style="color:#1d2b4f">Repartition par niveau</h5>
        <div style="height:260px"><canvas id="donutChart"></canvas></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-4 h-100">
        <h5 class="fw-bold" style="color:#1d2b4f">CV compatibles par secteur</h5>
        <div style="height:260px"><canvas id="barChart"></canvas></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('donutChart'), {
  type: 'doughnut',
  data: { labels: <?= json_encode($niveauxLabels) ?>, datasets: [{ data: <?= json_encode($niveauxData) ?>, backgroundColor: ['#e63946','#f4a261','#2a9d8f','#1d2b4f'] }] },
  options: { responsive:true, maintainAspectRatio:false }
});
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: { labels: <?= json_encode($secteursLabels) ?>, datasets: [{ label: 'CV compatibles >= 70%', data: <?= json_encode($secteursData) ?>, backgroundColor: ['#e63946','#2a9d8f','#f4a261','#457b9d','#9b5de5'] }] },
  options: { responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});
</script>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
