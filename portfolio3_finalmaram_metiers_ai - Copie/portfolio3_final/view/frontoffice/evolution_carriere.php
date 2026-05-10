<?php
require_once __DIR__ . '/../../controller/EvolutionC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrlEvo = new EvolutionC();
$ctrlCv = new CvC();
$pageTitle = 'Evolution de Carriere';
$cvId = (int)($_GET['cv_id'] ?? 0);
$allCvs = $ctrlCv->listCvs();
$result = $cvId ? $ctrlEvo->getEvolutionPourCv($cvId) : null;

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold" style="color:#1d2b4f">Evolution de Carriere AI</h2>
    <p class="text-muted" style="max-width:560px;margin:auto;font-size:.9rem">
      Le systeme part du meilleur metier recommande, puis affiche les prochaines etapes et les competences manquantes du CV.
    </p>
  </div>

  <div class="card p-4 mb-5">
    <form method="GET">
      <div class="row g-3 align-items-end">
        <div class="col-md-8">
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
          <button class="btn-hero w-100" type="submit" style="background:linear-gradient(135deg,#2a9d8f,#264653)">
            <i class="fas fa-route me-2"></i>Voir l'evolution
          </button>
        </div>
      </div>
    </form>
  </div>

  <?php if (!$result): ?>
    <div class="card p-5 text-center">
      <i class="fas fa-road fa-3x mb-3" style="color:#2a9d8f"></i>
      <h5 class="fw-bold" style="color:#1d2b4f">Selectionnez un CV</h5>
    </div>
  <?php elseif (!$result['found']): ?>
    <div class="card p-5 text-center">
      <i class="fas fa-exclamation-circle fa-3x mb-3" style="color:#f4a261"></i>
      <h5 class="fw-bold" style="color:#1d2b4f">Aucune evolution trouvee</h5>
      <p class="text-muted">Ajoutez des metiers et des competences requises pour calculer une evolution fiable.</p>
    </div>
  <?php else:
    $metier = $result['metier_depart'];
    $chain = $result['chain'];
    $anneesTotal = 0;
  ?>
    <div class="card p-3 mb-4 d-flex flex-row align-items-center gap-3" style="border:1px solid rgba(42,157,143,.2)">
      <div class="avatar" style="background:linear-gradient(135deg,#2a9d8f,#264653)"><?= htmlspecialchars(strtoupper(substr($result['titre_cv'], 0, 2))) ?></div>
      <div>
        <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($result['titre_cv']) ?></div>
        <div style="font-size:.8rem;color:#2a9d8f;font-weight:700">
          Depart detecte: <?= htmlspecialchars($metier->getTitre()) ?> - <?= (int)$result['score'] ?>%
        </div>
        <div style="font-size:.76rem;color:#64748b;margin-top:3px">
          Ce score vient de la comparaison entre le titre du CV, le secteur et les competences saisies.
        </div>
      </div>
    </div>

    <?php if (!empty($chain)): ?>
      <div class="card p-4 mb-4" style="background:linear-gradient(135deg,rgba(42,157,143,.06),rgba(69,123,157,.06));border:1px solid rgba(42,157,143,.18)">
        <div style="font-size:.72rem;color:#2a9d8f;font-weight:800;text-transform:uppercase;margin-bottom:6px">
          Chemin d'evolution propose
        </div>
        <div style="font-size:.9rem;color:#1d2b4f;font-weight:700">
          <?= htmlspecialchars($metier->getTitre()) ?>
          <?php foreach ($chain as $stepPreview): ?>
            <span style="color:#8899bb;margin:0 8px">→</span><?= htmlspecialchars($stepPreview->getTitreSuivant()) ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div style="position:relative;padding-left:18px">
      <div class="d-flex gap-3 mb-4">
        <div style="width:42px;height:42px;border-radius:50%;background:#2a9d8f;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-map-marker-alt"></i></div>
        <div class="card p-4 flex-fill" style="border-left:4px solid #2a9d8f">
          <div style="font-size:.7rem;color:#2a9d8f;font-weight:800;text-transform:uppercase">Poste recommande actuel</div>
          <h4 style="font-size:1.05rem;color:#1d2b4f" class="fw-bold mb-1"><?= htmlspecialchars($metier->getTitre()) ?></h4>
          <div style="font-size:.78rem;color:#8899bb"><?= htmlspecialchars($metier->getSecteur()) ?></div>
        </div>
      </div>

      <?php foreach ($chain as $idx => $step):
        $anneesTotal += $step->getAnneesRequises();
        $gaps = $step->getCompetencesGapArray();
        $color = ['#457b9d', '#f4a261', '#e63946', '#9b5de5'][$idx % 4];
      ?>
        <div class="d-flex gap-3 mb-4">
          <div style="width:42px;height:42px;border-radius:50%;background:<?= $color ?>;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-arrow-up"></i></div>
          <div class="card p-4 flex-fill" style="border-left:4px solid <?= $color ?>">
            <div style="font-size:.7rem;color:<?= $color ?>;font-weight:800;text-transform:uppercase">Dans <?= $anneesTotal ?> an<?= $anneesTotal > 1 ? 's' : '' ?></div>
            <h4 style="font-size:1.05rem;color:#1d2b4f" class="fw-bold mb-1"><?= htmlspecialchars($step->getTitreSuivant()) ?></h4>
            <div style="font-size:.78rem;color:#8899bb;margin-bottom:10px"><?= htmlspecialchars($step->getSecteurSuivant()) ?></div>
            <?php if ($step->getSalaireSuivantMin() > 0): ?>
              <div style="font-size:.76rem;color:#457b9d;font-weight:700;margin-bottom:10px">
                Salaire estime: <?= number_format($step->getSalaireSuivantMin(), 0, '', ' ') ?> - <?= number_format($step->getSalaireSuivantMax(), 0, '', ' ') ?> DT/mois
              </div>
            <?php endif; ?>
            <?php if ($gaps): ?>
              <div style="font-size:.72rem;color:#8899bb;font-weight:800;margin-bottom:7px">
                Competences manquantes dans ce CV pour atteindre cette etape:
              </div>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($gaps as $gap): ?>
                  <span style="font-size:.72rem;padding:4px 10px;border-radius:20px;background:rgba(230,57,70,.08);color:#e63946;font-weight:700"><?= htmlspecialchars($gap) ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="font-size:.78rem;color:#2a9d8f;font-weight:700">
                Aucune competence critique manquante detectee pour cette etape.
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (!$chain): ?>
        <div class="card p-4 text-center text-muted">Ce metier n'a pas encore de chemin d'evolution enregistre.</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
