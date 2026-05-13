<?php
require_once __DIR__ . '/../../controller/MetierC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrlMetier = new MetierC();
$ctrlCv = new CvC();
$pageTitle = 'Recommandations Metiers';
$cvId = (int)($_GET['cv_id'] ?? 0);
$secteur = trim($_GET['secteur'] ?? '');

$cv = $cvId ? $ctrlCv->getCv($cvId) : false;
$allCvs = $ctrlCv->listCvs();
$secteurs = $ctrlMetier->getAllSecteurs();
$recommandations = $cv ? $ctrlMetier->getRecommandations($cvId, $secteur) : [];
$metiersCompatibles = array_values(array_filter($recommandations, fn(Metier $m) => $m->getScore() >= 70));

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold" style="color:#1d2b4f">Recommandation AI par Score</h2>
    <p class="text-muted" style="max-width:560px;margin:auto;font-size:.9rem">
      Le systeme compare les competences du CV avec chaque metier et calcule un vrai pourcentage de compatibilite.
    </p>
  </div>

  <div class="card p-4 mb-4">
    <form method="GET" id="form-analyse-metiers">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label">Choisir un CV</label>
          <select name="cv_id" class="form-select">
            <option value="">-- Selectionnez un CV --</option>
            <?php foreach ($allCvs as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === $cvId ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['titre_poste']) ?> - <?= htmlspecialchars($c['email']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Filtrer par secteur</label>
          <select name="secteur" class="form-select">
            <option value="">Tous les secteurs</option>
            <?php foreach ($secteurs as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $s === $secteur ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn-hero w-100" type="submit"><i class="fas fa-search me-2"></i>Analyser</button>
        </div>
      </div>
    </form>
  </div>

  <script>
  (function() {
    var form = document.getElementById('form-analyse-metiers');
    if (form) {
      form.addEventListener('submit', function() {
        try { localStorage.setItem('analyse_metiers_lancee', '1'); } catch(e) {}
      });
    }
  })();
  </script>

  <?php if ($cv): ?>
    <div class="card p-3 mb-4 d-flex flex-row align-items-center gap-3" style="border:1px solid rgba(230,57,70,.15)">
      <div class="avatar"><?= htmlspecialchars(strtoupper(substr($cv['titre_poste'], 0, 2))) ?></div>
      <div>
        <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($cv['titre_poste']) ?></div>
        <div style="font-size:.8rem;color:#8899bb"><?= htmlspecialchars($cv['email']) ?></div>
      </div>
      <div class="ms-auto text-end">
        <div style="font-size:.75rem;color:#8899bb">Metiers analyses</div>
        <div class="fw-bold" style="color:#e63946;font-size:1.2rem"><?= count($recommandations) ?></div>
      </div>
    </div>

    <?php if (!empty($metiersCompatibles)): ?>
      <script>
      (function() {
        function sonAlerte() {
          try {
            var Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            var ctx = new Ctx();
            [523, 659, 784].forEach(function(freq, i) {
              var osc = ctx.createOscillator();
              var gain = ctx.createGain();
              osc.connect(gain); gain.connect(ctx.destination);
              osc.frequency.value = freq;
              gain.gain.setValueAtTime(0.001, ctx.currentTime + i * 0.18);
              gain.gain.exponentialRampToValueAtTime(0.28, ctx.currentTime + i * 0.18 + 0.03);
              gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.18 + 0.25);
              osc.start(ctx.currentTime + i * 0.18);
              osc.stop(ctx.currentTime + i * 0.18 + 0.3);
            });
          } catch(e) {}
        }

        if (localStorage.getItem('analyse_metiers_lancee') === '1') {
          localStorage.removeItem('analyse_metiers_lancee');
          setTimeout(sonAlerte, 250);
        }

        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:24px;right:24px;z-index:99999;max-width:360px;width:calc(100% - 48px);background:#fff;border-left:5px solid #2a9d8f;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.18);padding:18px 20px;font-family:inherit';
        toast.innerHTML = '<div style="font-weight:800;color:#1d2b4f;margin-bottom:6px"><i class="fas fa-bell" style="color:#2a9d8f;margin-right:7px"></i><?= count($metiersCompatibles) ?> alerte(s) compatibilite</div>'
          + '<div style="font-size:.78rem;color:#2a9d8f;font-weight:600;margin-bottom:10px">Score >= 70%</div>'
          <?php foreach ($metiersCompatibles as $m): ?>
          + '<div style="display:flex;justify-content:space-between;gap:10px;background:rgba(42,157,143,.08);padding:8px 10px;border-radius:9px;margin-top:6px"><span style="font-size:.78rem;font-weight:700;color:#1d2b4f"><?= htmlspecialchars($m->getTitre(), ENT_QUOTES) ?></span><b style="color:#2a9d8f"><?= $m->getScore() ?>%</b></div>'
          <?php endforeach; ?>
          + '<button onclick="this.parentNode.remove()" style="margin-top:12px;border:none;background:#eef2f7;border-radius:20px;padding:5px 12px;font-size:.75rem">Fermer</button>';
        document.body.appendChild(toast);
        setTimeout(function(){ if (toast.parentNode) toast.remove(); }, 9000);
      })();
      </script>
    <?php endif; ?>

    <?php if (empty($recommandations)): ?>
      <div class="card p-5 text-center text-muted">Aucun metier trouve. Executez d'abord le script SQL des metiers avances.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($recommandations as $i => $metier):
          $score = $metier->getScore();
          $color = $score >= 70 ? '#2a9d8f' : ($score >= 40 ? '#f4a261' : '#e63946');
          $label = $score >= 70 ? 'Tres compatible' : ($score >= 40 ? 'Compatible' : 'Peu compatible');
        ?>
          <div class="col-md-6 col-lg-4">
            <div class="card p-4 h-100" style="border-left:4px solid <?= $color ?>">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span style="font-size:.75rem;color:#8899bb;font-weight:700">#<?= $i + 1 ?></span>
                <div class="text-end">
                  <div style="font-size:1.7rem;font-weight:800;color:<?= $color ?>;line-height:1"><?= $score ?>%</div>
                  <div style="font-size:.68rem;color:<?= $color ?>;font-weight:700"><?= $label ?></div>
                </div>
              </div>
              <h5 class="fw-bold" style="font-size:.96rem;color:#1d2b4f"><?= htmlspecialchars($metier->getTitre()) ?></h5>
              <div style="font-size:.74rem;color:#8899bb;margin-bottom:10px"><i class="fas fa-building me-1"></i><?= htmlspecialchars($metier->getSecteur()) ?></div>
              <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-bottom:12px"><div style="height:100%;width:<?= $score ?>%;background:<?= $color ?>"></div></div>
              <p style="font-size:.76rem;color:#64748b"><?= htmlspecialchars(mb_substr($metier->getDescription(), 0, 95)) ?></p>
              <?php if ($metier->getSalaireMin() > 0): ?>
                <div style="font-size:.75rem;color:#457b9d;font-weight:700"><?= number_format($metier->getSalaireMin(), 0, '', ' ') ?> - <?= number_format($metier->getSalaireMax(), 0, '', ' ') ?> DT/mois</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="card p-5 text-center">
      <i class="fas fa-user-tie fa-3x mb-3" style="color:#e63946"></i>
      <h5 class="fw-bold" style="color:#1d2b4f">Selectionnez un CV pour commencer</h5>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
