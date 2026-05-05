<?php
require_once __DIR__ . '/../../controller/MetierC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrlMetier = new MetierC();
$ctrlCv     = new CvC();

$pageTitle  = 'Recommandations Métiers';
$cvId       = (int)($_GET['cv_id'] ?? 0);
$secteur    = trim($_GET['secteur'] ?? '');

// Récupérer le CV
$cv = $cvId ? $ctrlCv->getCv($cvId) : null;

// Recommandations
$recommandations = $cv ? $ctrlMetier->getRecommandations($cvId, $secteur) : [];

// Liste des CV pour le sélecteur
$allCvs   = $ctrlCv->listCvs();
$secteurs = $ctrlMetier->getAllSecteurs();

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>

<div class="container py-5">

  <!-- Titre page -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="../../index.php" style="font-size:.85rem;color:#8899bb;text-decoration:none;display:flex;align-items:center;gap:6px">
      <i class="fas fa-arrow-left"></i> Accueil
    </a>
    <span style="color:#dde3f0">/</span>
    <span style="font-size:.85rem;color:#e63946;font-weight:600"><i class="fas fa-star me-1"></i>Recommandations</span>
  </div>

  <div class="text-center mb-5">
    <div style="display:inline-block;background:rgba(230,57,70,.1);border:1px solid rgba(230,57,70,.25);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#e63946;margin-bottom:14px">
      <i class="fas fa-magic me-1"></i> Métier Avancé #1
    </div>
    <h2 class="fw-bold" style="color:#1d2b4f;font-size:1.8rem">Recommandation par Score</h2>
    <p class="text-muted" style="max-width:520px;margin:8px auto 0;font-size:.88rem">
      Le système analyse les compétences du CV et calcule un score de compatibilité avec chaque métier disponible.
    </p>
  </div>

  <!-- Formulaire sélection CV + filtre secteur -->
  <div class="card p-4 mb-5">
    <form method="GET" action="">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label"><i class="fas fa-id-card me-1" style="color:#e63946"></i> Choisir un CV</label>
          <select name="cv_id" class="form-select" onchange="this.form.submit()">
            <option value="">— Sélectionnez un CV —</option>
            <?php foreach ($allCvs as $c): ?>
            <option value="<?= $c->getId() ?>" <?= $c->getId() == $cvId ? 'selected' : '' ?>>
              <?= htmlspecialchars($c->getTitrePoste()) ?> — <?= htmlspecialchars($c->getEmail()) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label"><i class="fas fa-filter me-1" style="color:#f4a261"></i> Filtrer par secteur</label>
          <select name="secteur" class="form-select">
            <option value="">Tous les secteurs</option>
            <?php foreach ($secteurs as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= $s == $secteur ? 'selected' : '' ?>>
              <?= htmlspecialchars($s) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn-hero w-100" style="padding:11px">
            <i class="fas fa-search me-2"></i>Analyser
          </button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($cv): ?>

  <!-- Info CV analysé -->
  <div class="card p-3 mb-4 d-flex flex-row align-items-center gap-3" style="background:linear-gradient(135deg,rgba(29,43,79,.05),rgba(230,57,70,.05));border:1px solid rgba(230,57,70,.15);">
    <div class="avatar" style="width:50px;height:50px;font-size:1.1rem;flex-shrink:0">
      <?= strtoupper(substr($cv->getTitrePoste(), 0, 2)) ?>
    </div>
    <div>
      <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($cv->getTitrePoste()) ?></div>
      <div style="font-size:.8rem;color:#8899bb"><?= htmlspecialchars($cv->getEmail()) ?></div>
    </div>
    <div class="ms-auto text-end">
      <div style="font-size:.75rem;color:#8899bb">Métiers analysés</div>
      <div class="fw-bold" style="color:#e63946;font-size:1.2rem"><?= count($recommandations) ?></div>
    </div>
  </div>

  <?php if (!empty($recommandations)): ?>

  <!-- Légende scores -->
  <div class="d-flex gap-3 flex-wrap mb-3" style="font-size:.75rem">
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#2a9d8f;margin-right:5px"></span>≥ 70% Très compatible</span>
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#f4a261;margin-right:5px"></span>40–69% Compatible</span>
    <span><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#e63946;margin-right:5px"></span>< 40% Peu compatible</span>
  </div>

  <!-- Grille des recommandations -->
  <div class="row g-3">
    <?php foreach ($recommandations as $i => $metier):
      $score = $metier->getScore();
      $color = $score >= 70 ? '#2a9d8f' : ($score >= 40 ? '#f4a261' : '#e63946');
      $bgC   = $score >= 70 ? 'rgba(42,157,143,.08)' : ($score >= 40 ? 'rgba(244,162,97,.08)' : 'rgba(230,57,70,.05)');
      $label = $score >= 70 ? 'Très compatible' : ($score >= 40 ? 'Compatible' : 'Peu compatible');
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-4" style="border-left:4px solid <?= $color ?>;background:<?= $bgC ?>">
        <!-- Rang + score -->
        <div class="d-flex justify-content-between align-items-start mb-3">
          <span style="font-size:.7rem;font-weight:700;color:#8899bb">#<?= $i+1 ?></span>
          <div class="text-end">
            <div style="font-size:1.6rem;font-weight:800;color:<?= $color ?>;line-height:1"><?= $score ?>%</div>
            <div style="font-size:.65rem;color:<?= $color ?>;font-weight:600"><?= $label ?></div>
          </div>
        </div>

        <!-- Titre -->
        <h5 class="fw-bold mb-1" style="color:#1d2b4f;font-size:.92rem"><?= htmlspecialchars($metier->getTitre()) ?></h5>
        <div style="font-size:.72rem;color:#8899bb;margin-bottom:10px">
          <i class="fas fa-building me-1" style="color:<?= $color ?>"></i><?= htmlspecialchars($metier->getSecteur()) ?>
        </div>

        <!-- Barre de progression -->
        <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-bottom:12px">
          <div style="height:100%;width:<?= $score ?>%;background:<?= $color ?>;border-radius:4px;transition:width .5s"></div>
        </div>

        <!-- Description -->
        <?php if ($metier->getDescription()): ?>
        <p style="font-size:.75rem;color:#64748b;margin-bottom:12px;line-height:1.5">
          <?= htmlspecialchars(mb_substr($metier->getDescription(), 0, 80)) ?>…
        </p>
        <?php endif; ?>

        <!-- Salaire -->
        <?php if ($metier->getSalaireMin() > 0): ?>
        <div style="font-size:.72rem;font-weight:600;color:#457b9d;margin-bottom:12px">
          <i class="fas fa-money-bill-wave me-1"></i>
          <?= number_format($metier->getSalaireMin(), 0, '', ' ') ?> — <?= number_format($metier->getSalaireMax(), 0, '', ' ') ?> DT/mois
        </div>
        <?php endif; ?>

        <!-- Compétences requises -->
        <?php $reqComps = $ctrlMetier->getCompetencesRequises($metier->getId()); ?>
        <?php if (!empty($reqComps)): ?>
        <div style="font-size:.68rem;color:#8899bb;margin-bottom:6px;font-weight:600">Compétences requises :</div>
        <div class="d-flex flex-wrap gap-1">
          <?php foreach ($reqComps as $rc): ?>
          <span style="font-size:.62rem;padding:2px 8px;border-radius:20px;background:rgba(29,43,79,.08);color:#1d2b4f;font-weight:500">
            <?= htmlspecialchars($rc['nom_competence']) ?> (<?= htmlspecialchars($rc['niveau_minimum']) ?>)
          </span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="card text-center p-5">
    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
    <p class="text-muted mb-0">Aucun métier trouvé pour ce filtre.</p>
  </div>
  <?php endif; ?>

  <?php else: ?>

  <!-- État vide -->
  <div class="card text-center p-5">
    <i class="fas fa-user-tie fa-3x mb-4" style="color:#e63946"></i>
    <h5 class="fw-bold" style="color:#1d2b4f">Sélectionnez un CV pour commencer</h5>
    <p class="text-muted" style="font-size:.88rem;max-width:400px;margin:8px auto 0">
      Choisissez un CV dans le menu ci-dessus. Le système analysera automatiquement ses compétences
      et calculera un score de compatibilité avec chaque métier.
    </p>
  </div>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
