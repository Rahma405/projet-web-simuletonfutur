<?php
session_start();
require_once __DIR__ . '/controller/CvC.php';
require_once __DIR__ . '/controller/CompetenceC.php';
require_once __DIR__ . '/controller/ExperienceC.php';

$ctrlCv   = new CvC();
$ctrlComp = new CompetenceC();
$ctrlExp  = new ExperienceC();

$cvs         = $ctrlCv->listCvs();
$totalCvs    = $ctrlCv->countCvs();
$totalComps  = $ctrlComp->countCompetences();
$totalExps   = $ctrlExp->countExperiences();
$competences = $ctrlComp->listCompetences();
$experiences = $ctrlExp->listExperiences();
$pageTitle   = 'Accueil';

$message = $_SESSION['message'] ?? null;
if (isset($_SESSION['message'])) unset($_SESSION['message']);

require_once __DIR__ . '/templates/frontoffice/header.php';
?>

<!-- HERO -->
<section style="background:linear-gradient(135deg,#1d2b4f 0%,#0f1a36 60%,#1d2b4f 100%);padding:80px 0 60px;">
  <div class="container text-center text-white">
    <div style="display:inline-block;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);border-radius:30px;padding:5px 18px;font-size:.78rem;font-weight:600;color:#e63946;margin-bottom:18px;">
      <i class="fas fa-briefcase me-1"></i> Gestion de Portfolio
    </div>
    <h1 style="font-size:2.8rem;font-weight:800;line-height:1.2;margin-bottom:14px;">
      Gérez votre <span style="color:#e63946;">Portfolio</span> Professionnel
    </h1>
    <p style="font-size:1rem;color:#8899bb;max-width:560px;margin:0 auto 32px;">
      Centralisez vos CV, compétences et expériences en un seul endroit.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="view/frontoffice/list_cvs.php" class="btn-hero">
        <i class="fas fa-eye me-2"></i>Voir les CV
      </a>
      <a href="view/backoffice/list_cvs.php" class="btn-outline-hero" style="border-color:#fff;color:#fff;">
        <i class="fas fa-shield-alt me-2"></i>Administration
      </a>
    </div>
  </div>
</section>

<!-- STATS -->
<section style="background:#1d2b4f;padding:44px 0;">
  <div class="container">
    <div class="row g-3 text-center text-white">
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#e63946"><?php echo $totalCvs; ?></div>
        <div style="font-size:.82rem;color:#8899bb">CV créés</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#f4a261"><?php echo $totalComps; ?></div>
        <div style="font-size:.82rem;color:#8899bb">Compétences</div>
      </div>
      <div class="col-4">
        <div style="font-size:2.2rem;font-weight:800;color:#2a9d8f"><?php echo $totalExps; ?></div>
        <div style="font-size:.82rem;color:#8899bb">Expériences</div>
      </div>
    </div>
  </div>
</section>

<?php if ($message): ?>
<div class="container mt-4">
  <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show mb-0">
    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message['texte']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<!-- CV RECENTS -->
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f"><i class="fas fa-id-card me-2" style="color:#e63946"></i>Portfolios récents</h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?php echo $totalCvs; ?> CV disponible(s)</p>
    </div>
    <a href="view/frontoffice/list_cvs.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;"><i class="fas fa-th me-1"></i>Voir tout</a>
  </div>
  <?php if (empty($cvs)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
      <p class="text-muted">Aucun CV pour le moment.</p>
      <a href="view/backoffice/add_cv.php" class="btn-hero d-inline-block mx-auto" style="width:fit-content">Ajouter un CV</a>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach (array_slice($cvs, 0, 6) as $cv): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar"><?php echo strtoupper(substr($cv['titre_poste'], 0, 2)); ?></div>
            <div>
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?php echo htmlspecialchars($cv['titre_poste']); ?></div>
              <div style="font-size:.75rem;color:#8899bb"><?php echo htmlspecialchars($cv['email']); ?></div>
            </div>
          </div>
          <?php if ($cv['description']): ?>
            <p style="font-size:.8rem;color:#666;margin:0 0 12px;"><?php echo htmlspecialchars(mb_substr($cv['description'], 0, 80)); ?>…</p>
          <?php endif; ?>
          <div class="d-flex gap-2 flex-wrap mb-3">
            <?php if ($cv['github']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-github me-1" style="color:#e63946"></i>GitHub</span><?php endif; ?>
            <?php if ($cv['linkedin']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fab fa-linkedin me-1" style="color:#457b9d"></i>LinkedIn</span><?php endif; ?>
            <?php if ($cv['telephone']): ?><span style="font-size:.75rem;color:#8899bb"><i class="fas fa-phone me-1" style="color:#2a9d8f"></i><?php echo htmlspecialchars($cv['telephone']); ?></span><?php endif; ?>
          </div>
          <a href="view/frontoffice/show_cv.php?id=<?php echo $cv['id']; ?>" class="btn-hero d-block text-center" style="padding:8px;font-size:.82rem;">
            <i class="fas fa-eye me-1"></i>Voir le profil
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- COMPETENCES -->
<section style="background:#f8f9fa;padding:50px 0;">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1" style="color:#1d2b4f"><i class="fas fa-tools me-2" style="color:#f4a261"></i>Compétences</h3>
        <p class="text-muted mb-0" style="font-size:.83rem"><?php echo $totalComps; ?> compétence(s)</p>
      </div>
      <a href="view/frontoffice/list_competences.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;background:#f4a261;border-color:#f4a261;"><i class="fas fa-th me-1"></i>Voir tout</a>
    </div>
    <?php if (empty($competences)): ?>
      <div class="card text-center p-5"><i class="fas fa-tools fa-3x mb-3 text-muted"></i><p class="text-muted">Aucune compétence.</p></div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach (array_slice($competences, 0, 6) as $comp):
          $b = match(strtolower($comp['niveau'] ?? '')) {
            'expert' => 'badge-expert', 'avancé' => 'badge-avance',
            'intermédiaire' => 'badge-intermediaire', default => 'badge-debutant'
          }; ?>
        <div class="col-md-6 col-lg-4">
          <div class="card p-3 d-flex flex-row align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:12px;background:rgba(244,162,97,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-code" style="color:#f4a261;font-size:1.1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?php echo htmlspecialchars($comp['nom_competence']); ?></div>
              <div style="font-size:.75rem;color:#8899bb"><?php echo htmlspecialchars($comp['categorie'] ?? ''); ?></div>
              <span class="<?php echo $b; ?>" style="display:inline-block;margin-top:4px;"><?php echo htmlspecialchars($comp['niveau'] ?? 'N/A'); ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- EXPERIENCES -->
<section class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1" style="color:#1d2b4f"><i class="fas fa-history me-2" style="color:#2a9d8f"></i>Expériences générales</h3>
      <p class="text-muted mb-0" style="font-size:.83rem"><?php echo $totalExps; ?> expérience(s)</p>
    </div>
    <a href="view/frontoffice/list_experiences.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;background:#2a9d8f;border-color:#2a9d8f;"><i class="fas fa-th me-1"></i>Voir tout</a>
  </div>
  <?php if (empty($experiences)): ?>
    <div class="card text-center p-5"><i class="fas fa-history fa-3x mb-3 text-muted"></i><p class="text-muted">Aucune expérience.</p></div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach (array_slice($experiences, 0, 6) as $exp): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card p-4">
          <div class="d-flex align-items-start gap-3 mb-2">
            <div style="width:46px;height:46px;border-radius:12px;background:rgba(42,157,143,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-briefcase" style="color:#2a9d8f;font-size:1.1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f"><?php echo htmlspecialchars($exp['titre']); ?></div>
              <div style="font-size:.78rem;color:#457b9d;font-weight:600;"><?php echo htmlspecialchars($exp['entreprise'] ?? ''); ?></div>
              <div style="font-size:.73rem;color:#8899bb;"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($exp['lieu'] ?? ''); ?></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span style="font-size:.72rem;color:#8899bb;">
              <i class="fas fa-calendar me-1"></i>
              <?php echo date('m/Y', strtotime($exp['date_debut'])); ?> —
              <?php echo $exp['en_cours'] ? '<span style="color:#2a9d8f;font-weight:600">En cours</span>' : date('m/Y', strtotime($exp['date_fin'])); ?>
            </span>
            <span style="font-size:.7rem;font-weight:600;padding:2px 10px;border-radius:20px;background:rgba(69,123,157,.1);color:#457b9d;">
              <?php echo htmlspecialchars($exp['type_contrat']); ?>
            </span>
          </div>
          <?php if ($exp['description']): ?>
            <p style="font-size:.78rem;color:#666;margin:0;"><?php echo htmlspecialchars(mb_substr($exp['description'], 0, 90)); ?>…</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- METIERS AVANCES AI -->
<section style="background:linear-gradient(135deg,#1d2b4f 0%,#0f1a36 100%);padding:60px 0;">
  <div class="container text-center text-white">
    <h3 class="fw-bold mb-2" style="font-size:1.6rem">Metiers Avances avec AI</h3>
    <p style="color:#8899bb;font-size:.9rem;margin-bottom:36px">Analyse de compatibilite, evolution de carriere, statistiques et alertes a partir de 70%.</p>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div style="background:rgba(230,57,70,.1);border:1px solid rgba(230,57,70,.25);border-radius:18px;padding:28px 24px;height:100%">
          <i class="fas fa-star" style="font-size:2rem;color:#e63946;margin-bottom:14px"></i>
          <h4 style="color:#e63946;font-weight:700;margin-bottom:10px">Recommandations</h4>
          <p style="color:#8899bb;font-size:.85rem">Calcule un pourcentage fiable de compatibilite entre CV et metiers.</p>
          <a href="view/frontoffice/recommandation_metiers.php" class="btn-hero" style="margin-top:10px">Analyser</a>
        </div>
      </div>
      <div class="col-md-4">
        <div style="background:rgba(42,157,143,.1);border:1px solid rgba(42,157,143,.25);border-radius:18px;padding:28px 24px;height:100%">
          <i class="fas fa-route" style="font-size:2rem;color:#2a9d8f;margin-bottom:14px"></i>
          <h4 style="color:#2a9d8f;font-weight:700;margin-bottom:10px">Evolution</h4>
          <p style="color:#8899bb;font-size:.85rem">Trace les etapes de carriere depuis le meilleur metier recommande.</p>
          <a href="view/frontoffice/evolution_carriere.php" class="btn-hero" style="margin-top:10px;background:linear-gradient(135deg,#2a9d8f,#264653)">Voir</a>
        </div>
      </div>
      <div class="col-md-4">
        <div style="background:rgba(244,162,97,.1);border:1px solid rgba(244,162,97,.25);border-radius:18px;padding:28px 24px;height:100%">
          <i class="fas fa-chart-pie" style="font-size:2rem;color:#f4a261;margin-bottom:14px"></i>
          <h4 style="color:#f4a261;font-weight:700;margin-bottom:10px">Statistiques</h4>
          <p style="color:#8899bb;font-size:.85rem">Affiche les niveaux et les secteurs avec CV compatibles.</p>
          <a href="view/frontoffice/statistiques.php" class="btn-hero" style="margin-top:10px;background:#f4a261">Stats</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/templates/frontoffice/footer.php'; ?>
