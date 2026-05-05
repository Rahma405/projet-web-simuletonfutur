<?php
require_once __DIR__ . '/../../controller/EvolutionC.php';
require_once __DIR__ . '/../../controller/CvC.php';

$ctrlEvo = new EvolutionC();
$ctrlCv  = new CvC();

$pageTitle = 'Évolution de Carrière';
$cvId      = (int)($_GET['cv_id'] ?? 0);

$cv      = $cvId ? $ctrlCv->getCv($cvId) : null;
$result  = $cvId ? $ctrlEvo->getEvolutionPourCv($cvId) : null;
$allCvs  = $ctrlCv->listCvs();

require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>

<div class="container py-5">

  <!-- Breadcrumb -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="../../index.php" style="font-size:.85rem;color:#8899bb;text-decoration:none;display:flex;align-items:center;gap:6px">
      <i class="fas fa-arrow-left"></i> Accueil
    </a>
    <span style="color:#dde3f0">/</span>
    <span style="font-size:.85rem;color:#2a9d8f;font-weight:600"><i class="fas fa-road me-1"></i>Évolution</span>
  </div>

  <!-- En-tête -->
  <div class="text-center mb-5">
    <div style="display:inline-block;background:rgba(42,157,143,.1);border:1px solid rgba(42,157,143,.25);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#2a9d8f;margin-bottom:14px">
      <i class="fas fa-route me-1"></i> Métier Avancé #2
    </div>
    <h2 class="fw-bold" style="color:#1d2b4f;font-size:1.8rem">Évolution de Carrière</h2>
    <p class="text-muted" style="max-width:540px;margin:8px auto 0;font-size:.88rem">
      Le système détecte le poste actuel du CV et trace le chemin d'évolution vers les postes supérieurs,
      avec les compétences à acquérir à chaque étape.
    </p>
  </div>

  <!-- Sélecteur CV -->
  <div class="card p-4 mb-5">
    <form method="GET" action="">
      <div class="row g-3 align-items-end">
        <div class="col-md-8">
          <label class="form-label"><i class="fas fa-id-card me-1" style="color:#2a9d8f"></i> Choisir un CV</label>
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
          <button type="submit" class="btn-hero w-100" style="padding:11px;background:linear-gradient(135deg,#2a9d8f,#264653);">
            <i class="fas fa-route me-2"></i>Voir l'évolution
          </button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($result): ?>

    <?php if (!$result['found']): ?>
    <!-- Aucun métier trouvé -->
    <div class="card text-center p-5">
      <i class="fas fa-exclamation-circle fa-3x mb-3" style="color:#f4a261"></i>
      <h5 class="fw-bold" style="color:#1d2b4f">Aucune correspondance trouvée</h5>
      <p class="text-muted" style="font-size:.88rem;max-width:440px;margin:8px auto 0">
        Le poste <strong><?= htmlspecialchars($result['titre_cv']) ?></strong> n'a pas de correspondance
        dans notre base de métiers. Essayez avec un autre CV.
      </p>
    </div>

    <?php else:
      $metierDepart = $result['metier_depart'];
      $chain        = $result['chain'];
    ?>

    <!-- Info CV analysé -->
    <div class="card p-3 mb-4 d-flex flex-row align-items-center gap-3" style="background:rgba(42,157,143,.05);border:1px solid rgba(42,157,143,.2);">
      <div class="avatar" style="width:50px;height:50px;font-size:1.1rem;background:linear-gradient(135deg,#2a9d8f,#264653);flex-shrink:0">
        <?= strtoupper(substr($result['titre_cv'], 0, 2)) ?>
      </div>
      <div>
        <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($result['titre_cv']) ?></div>
        <div style="font-size:.8rem;color:#2a9d8f;font-weight:600">
          Correspondance détectée : <?= htmlspecialchars($metierDepart['titre']) ?>
        </div>
      </div>
      <?php if (!empty($chain)): ?>
      <div class="ms-auto text-end">
        <div style="font-size:.75rem;color:#8899bb">Étapes d'évolution</div>
        <div class="fw-bold" style="color:#2a9d8f;font-size:1.2rem"><?= count($chain) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Timeline d'évolution -->
    <div style="position:relative;padding-left:20px">

      <!-- ── Poste actuel ── -->
      <div style="display:flex;align-items:flex-start;gap:20px;margin-bottom:32px;position:relative;">
        <!-- Ligne verticale -->
        <?php if (!empty($chain)): ?>
        <div style="position:absolute;left:22px;top:46px;bottom:-32px;width:2px;background:linear-gradient(180deg,#2a9d8f,#e2e8f0);z-index:0"></div>
        <?php endif; ?>

        <!-- Cercle actuel -->
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#2a9d8f,#264653);display:flex;align-items:center;justify-content:center;flex-shrink:0;z-index:1;box-shadow:0 4px 14px rgba(42,157,143,.3)">
          <i class="fas fa-map-marker-alt" style="color:#fff;font-size:1rem"></i>
        </div>

        <!-- Contenu -->
        <div class="card p-4" style="flex:1;border-left:4px solid #2a9d8f;margin-bottom:0">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
            <div>
              <div style="font-size:.68rem;font-weight:700;color:#2a9d8f;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">
                <i class="fas fa-user-check me-1"></i> Poste actuel
              </div>
              <h4 class="fw-bold" style="color:#1d2b4f;font-size:1.05rem;margin-bottom:4px">
                <?= htmlspecialchars($metierDepart['titre']) ?>
              </h4>
              <div style="font-size:.78rem;color:#8899bb">
                <i class="fas fa-building me-1"></i><?= htmlspecialchars($metierDepart['secteur']) ?>
              </div>
            </div>
            <?php if ((int)$metierDepart['salaire_min'] > 0): ?>
            <div style="text-align:right">
              <div style="font-size:.68rem;color:#8899bb">Salaire estimé</div>
              <div style="font-size:.88rem;font-weight:700;color:#2a9d8f">
                <?= number_format($metierDepart['salaire_min'], 0, '', ' ') ?> —
                <?= number_format($metierDepart['salaire_max'], 0, '', ' ') ?> DT/mois
              </div>
            </div>
            <?php endif; ?>
          </div>
          <?php if ($metierDepart['description']): ?>
          <p style="font-size:.78rem;color:#64748b;margin-top:10px;margin-bottom:0;line-height:1.5">
            <?= htmlspecialchars($metierDepart['description']) ?>
          </p>
          <?php endif; ?>
        </div>
      </div>

      <?php
      $anneesTotal = 0;
      $totalEtapes = count($chain);
      foreach ($chain as $idx => $etape):
        $anneesTotal += $etape->getAnneesRequises();
        $isLast       = ($idx === $totalEtapes - 1);
        $couleurs     = ['#457b9d','#f4a261','#e63946','#9b5de5'];
        $couleur      = $couleurs[$idx % count($couleurs)];
        $gapArray     = $etape->getCompetencesGapArray();
      ?>

      <!-- ── Étape suivante ── -->
      <div style="display:flex;align-items:flex-start;gap:20px;margin-bottom:<?= $isLast ? '0' : '32px' ?>;position:relative;">

        <!-- Ligne verticale vers la suivante -->
        <?php if (!$isLast): ?>
        <div style="position:absolute;left:22px;top:46px;bottom:-32px;width:2px;background:linear-gradient(180deg,<?= $couleur ?>,#e2e8f0);z-index:0"></div>
        <?php endif; ?>

        <!-- Cercle étape -->
        <div style="width:44px;height:44px;border-radius:50%;background:<?= $couleur ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;z-index:1;box-shadow:0 4px 14px rgba(0,0,0,.15)">
          <i class="fas fa-arrow-up" style="color:#fff;font-size:.9rem"></i>
        </div>

        <!-- Contenu étape -->
        <div class="card p-4" style="flex:1;border-left:4px solid <?= $couleur ?>;margin-bottom:0">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
            <div>
              <div style="font-size:.68rem;font-weight:700;color:<?= $couleur ?>;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">
                <i class="fas fa-clock me-1"></i>
                Dans <?= $anneesTotal ?> an<?= $anneesTotal > 1 ? 's' : '' ?>
                · Étape <?= $idx + 1 ?>
              </div>
              <h4 class="fw-bold" style="color:#1d2b4f;font-size:1.05rem;margin-bottom:4px">
                <?= htmlspecialchars($etape->getTitreSuivant()) ?>
              </h4>
              <div style="font-size:.78rem;color:#8899bb">
                <i class="fas fa-building me-1"></i><?= htmlspecialchars($etape->getSecteurSuivant()) ?>
              </div>
            </div>
            <?php if ($etape->getSalaireSuivantMin() > 0): ?>
            <div style="text-align:right">
              <div style="font-size:.68rem;color:#8899bb">Salaire estimé</div>
              <div style="font-size:.88rem;font-weight:700;color:<?= $couleur ?>">
                <?= number_format($etape->getSalaireSuivantMin(), 0, '', ' ') ?> —
                <?= number_format($etape->getSalaireSuivantMax(), 0, '', ' ') ?> DT/mois
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Compétences gap -->
          <?php if (!empty($gapArray)): ?>
          <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f0f2f8">
            <div style="font-size:.72rem;font-weight:700;color:#8899bb;margin-bottom:8px">
              <i class="fas fa-graduation-cap me-1" style="color:<?= $couleur ?>"></i>
              Compétences à acquérir pour cette étape :
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:7px">
              <?php foreach ($gapArray as $gap): ?>
              <span style="font-size:.7rem;padding:4px 12px;border-radius:20px;background:rgba(230,57,70,.08);color:#e63946;font-weight:600;border:1px solid rgba(230,57,70,.2)">
                <i class="fas fa-plus-circle me-1"></i><?= htmlspecialchars($gap) ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>
      <?php endforeach; ?>

      <?php if (empty($chain)): ?>
      <!-- Aucune évolution enregistrée -->
      <div class="card text-center p-4" style="border:2px dashed #dde3f0">
        <i class="fas fa-flag-checkered fa-2x mb-2" style="color:#8899bb"></i>
        <p class="text-muted mb-0" style="font-size:.85rem">
          Ce poste est déjà au sommet de son parcours ou aucune évolution n'est encore définie.
        </p>
      </div>
      <?php else: ?>
      <!-- Destination finale -->
      <div class="card text-center p-4 mt-4" style="background:linear-gradient(135deg,rgba(29,43,79,.05),rgba(230,57,70,.05));border:2px dashed #e63946">
        <i class="fas fa-trophy fa-2x mb-2" style="color:#f4a261"></i>
        <div class="fw-bold" style="color:#1d2b4f;font-size:.92rem">Objectif final atteint dans <?= $anneesTotal ?> ans</div>
        <div style="font-size:.8rem;color:#8899bb;margin-top:4px">Continuez à développer vos compétences à chaque étape !</div>
      </div>
      <?php endif; ?>

    </div><!-- /timeline -->

    <?php endif; ?>

  <?php else: ?>

  <!-- État vide -->
  <div class="card text-center p-5">
    <i class="fas fa-road fa-3x mb-4" style="color:#2a9d8f"></i>
    <h5 class="fw-bold" style="color:#1d2b4f">Sélectionnez un CV pour voir son évolution</h5>
    <p class="text-muted" style="font-size:.88rem;max-width:440px;margin:8px auto 0">
      Le système détectera automatiquement le poste actuel et tracera le parcours d'évolution
      avec les compétences à acquérir à chaque étape.
    </p>
  </div>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
