<?php
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl      = new CvC();
$pageTitle = 'Profil';
$id        = (int)($_GET['id'] ?? 0);
$result    = $ctrl->getCvWithCompetences($id);
if (!$result) { header('Location: list_cvs.php'); exit; }
$cv          = $result['cv'];
$competences = $result['competences'];
$pageTitle   = htmlspecialchars($cv['titre_poste']);
require_once __DIR__ . '/../../templates/frontoffice/header.php';
?>
<div class="container py-5">
  <a href="list_cvs.php" style="font-size:.85rem;color:#8899bb;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:24px;">
    <i class="fas fa-arrow-left"></i> Retour aux CV
  </a>

  <!-- CV Card -->
  <div class="card p-4 mb-4">
    <div class="d-flex align-items-center gap-4 mb-4">
      <div class="avatar" style="width:60px;height:60px;font-size:1.2rem"><?= strtoupper(substr($cv['titre_poste'],0,2)) ?></div>
      <div>
        <h2 class="fw-bold mb-1" style="color:#1d2b4f;font-size:1.4rem"><?= htmlspecialchars($cv['titre_poste']) ?></h2>
        <p class="mb-0" style="color:#8899bb;font-size:.88rem"><?= htmlspecialchars($cv['email']) ?></p>
      </div>
    </div>
    <?php if($cv['description']): ?>
      <p style="color:#475569;font-size:.9rem;margin-bottom:20px"><?= htmlspecialchars($cv['description']) ?></p>
    <?php endif; ?>
    <div class="row g-3 mb-3">
      <?php if($cv['telephone']): ?><div class="col-md-4"><i class="fas fa-phone me-2" style="color:#e63946"></i><span style="font-size:.86rem"><?= htmlspecialchars($cv['telephone']) ?></span></div><?php endif; ?>
      <?php if($cv['adresse']): ?><div class="col-md-4"><i class="fas fa-map-marker-alt me-2" style="color:#457b9d"></i><span style="font-size:.86rem"><?= htmlspecialchars($cv['adresse']) ?></span></div><?php endif; ?>
      <?php if($cv['date_naissance']): ?><div class="col-md-4"><i class="fas fa-birthday-cake me-2" style="color:#2a9d8f"></i><span style="font-size:.86rem"><?= htmlspecialchars($cv['date_naissance']) ?></span></div><?php endif; ?>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if($cv['github']): ?><a href="<?= htmlspecialchars($cv['github']) ?>" target="_blank" class="btn-hero" style="padding:7px 16px;font-size:.8rem;border-radius:20px"><i class="fab fa-github me-1"></i>GitHub</a><?php endif; ?>
      <?php if($cv['linkedin']): ?><a href="<?= htmlspecialchars($cv['linkedin']) ?>" target="_blank" class="btn-hero" style="padding:7px 16px;font-size:.8rem;border-radius:20px;background:linear-gradient(135deg,#457b9d,#1d3557)"><i class="fab fa-linkedin me-1"></i>LinkedIn</a><?php endif; ?>
      <?php if($cv['site_web']): ?><a href="<?= htmlspecialchars($cv['site_web']) ?>" target="_blank" class="btn-hero" style="padding:7px 16px;font-size:.8rem;border-radius:20px;background:linear-gradient(135deg,#2a9d8f,#264653)"><i class="fas fa-globe me-1"></i>Site web</a><?php endif; ?>
    </div>
  </div>

  <!-- Competences -->
  <?php if(!empty($competences)): ?>
  <h3 class="fw-bold mb-3" style="color:#1d2b4f">Compétences</h3>
  <div class="row g-3">
    <?php foreach($competences as $c):
      $pct=match(strtolower($c['niveau'])){'expert'=>100,'avancé'=>75,'intermédiaire'=>50,default=>25};
      $b  =match(strtolower($c['niveau'])){'expert'=>'badge-expert','avancé'=>'badge-avance','intermédiaire'=>'badge-intermediaire',default=>'badge-debutant'}; ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h6 class="fw-bold mb-0" style="color:#1d2b4f;font-size:.88rem"><?= htmlspecialchars($c['nom_competence']) ?></h6>
          <span class="<?= $b ?>"><?= htmlspecialchars($c['niveau']) ?></span>
        </div>
        <p style="font-size:.75rem;color:#8899bb;margin-bottom:8px"><i class="fas fa-tag me-1" style="color:#e63946"></i><?= htmlspecialchars($c['categorie']) ?></p>
        <div class="comp-bar"><div class="comp-bar-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../templates/frontoffice/footer.php'; ?>
