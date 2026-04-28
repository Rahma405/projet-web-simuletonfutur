<?php
session_start();
require_once __DIR__ . '/../../controller/OffreC.php';
require_once __DIR__ . '/../../controller/CandidatureC.php';

$sessionUser = $_SESSION['user'] ?? null;

// Rediriger si non connecté
if (!$sessionUser) {
    header('Location: login.php');
    exit;
}

$ctrlO     = new OffreC();
$ctrlC     = new CandidatureC();
$pageTitle = 'Matching Offres';
$idU       = (int) $sessionUser['id'];

$candidature = $ctrlC->getByIdUtilisateur($idU);
$results     = $ctrlO->getMatchedOffres($idU);

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">
        <i class="fas fa-star me-2" style="color:#f4a261"></i>Offres recommandées
      </h2>
      <p class="text-muted mb-0" style="font-size:.83rem">Offres triées par compatibilité avec ton profil</p>
    </div>
    <div class="d-flex gap-2">
      <a href="add_candidature.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
        <i class="fas fa-edit me-1"></i>Modifier ma candidature
      </a>
      <a href="list_offres.php" class="btn btn-outline-secondary" style="padding:9px 20px;font-size:.85rem;">
        <i class="fas fa-briefcase me-1"></i>Toutes les offres
      </a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show mb-4">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message['texte']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!$candidature): ?>
    <div class="card text-center p-5">
      <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-2">Tu n'as pas encore déposé de candidature.</p>
      <a href="add_candidature.php" class="btn-hero" style="padding:10px 28px;font-size:.9rem;display:inline-block;">
        <i class="fas fa-plus me-2"></i>Déposer ma candidature
      </a>
    </div>

  <?php elseif (empty($results)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-0">Aucune offre disponible pour le matching.</p>
    </div>

  <?php else: ?>

    <?php
      // Préparer les skills du candidat pour l'affichage
      $mesSkills = array_map('trim', explode(',', $candidature->getSkills()));
    ?>

    <div class="card p-3 mb-4" style="border-left:4px solid #2a9d8f;">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="fw-semibold" style="font-size:.85rem;color:#1d2b4f">
          <i class="fas fa-user me-1" style="color:#2a9d8f"></i>Mes compétences :
        </span>
        <?php foreach ($mesSkills as $sk): ?>
          <span class="badge" style="background:#2a9d8f20;color:#2a9d8f;font-size:.75rem;font-weight:600;padding:4px 10px;border-radius:20px">
            <?= htmlspecialchars($sk) ?>
          </span>
        <?php endforeach; ?>
        <span class="ms-auto" style="font-size:.78rem;color:#8899bb">
          <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($candidature->getLocalisation()) ?>
        </span>
      </div>
    </div>

    <div class="row g-3">
      <?php foreach ($results as $r):
        $offre    = $r['offre'];
        $score    = $r['score'];
        $matched  = $r['matched'];
        $locMatch = $r['locMatch'];

        // Couleur selon le score
        if ($score >= 70)     { $barColor = '#2a9d8f'; $label = 'Excellent'; }
        elseif ($score >= 40) { $barColor = '#f4a261'; $label = 'Moyen';     }
        else                  { $barColor = '#e63946'; $label = 'Faible';    }

        $competences = array_map('trim', explode(',', $offre['competences']));
      ?>
        <div class="col-md-6">
          <div class="card h-100 p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="fw-bold mb-1" style="color:#1d2b4f;font-size:.95rem"><?= htmlspecialchars($offre['titre']) ?></h6>
                <span style="font-size:.78rem;color:#8899bb">
                  <i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i><?= htmlspecialchars($offre['localisation']) ?>
                  <?php if ($locMatch): ?>
                    <span class="badge ms-1" style="background:#2a9d8f20;color:#2a9d8f;font-size:.68rem">
                      <i class="fas fa-check me-1"></i>Même ville
                    </span>
                  <?php endif; ?>
                </span>
              </div>
              <div style="text-align:center;min-width:60px;">
                <div style="font-size:1.4rem;font-weight:800;color:<?= $barColor ?>"><?= $score ?>%</div>
                <div style="font-size:.65rem;color:<?= $barColor ?>;font-weight:600"><?= $label ?></div>
              </div>
            </div>

            <div style="background:#e9ecef;border-radius:10px;height:8px;margin-bottom:14px;overflow:hidden;">
              <div style="width:<?= $score ?>%;height:100%;background:<?= $barColor ?>;border-radius:10px;transition:width .6s ease;"></div>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-2">
              <?php foreach ($competences as $comp):
                $isMatched = in_array(strtolower(trim($comp)), $matched);
              ?>
                <span class="badge" style="background:<?= $isMatched ? $barColor.'30' : '#f0f0f0' ?>;color:<?= $isMatched ? $barColor : '#999' ?>;font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;<?= $isMatched ? 'border:1px solid '.$barColor.'50' : '' ?>">
                  <?php if ($isMatched): ?><i class="fas fa-check me-1" style="font-size:.6rem"></i><?php endif; ?>
                  <?= htmlspecialchars($comp) ?>
                </span>
              <?php endforeach; ?>
            </div>

            <div class="mt-auto" style="font-size:.75rem;color:#8899bb">
              <?= count($matched) ?> / <?= $r['total'] ?> compétences matchées
              <?php if ($locMatch): ?> • Bonus localisation +10<?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>