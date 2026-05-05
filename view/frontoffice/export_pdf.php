<?php
require_once __DIR__ . '/../../controller/PdfC.php';

$ctrl  = new PdfC();
$id    = (int)($_GET['id'] ?? 0);
$data  = $ctrl->buildCvData($id);

if (!$data) {
    header('Location: list_cvs.php');
    exit;
}

$cv          = $data['cv'];
$competences = $data['competences'];
$experiences = $data['experiences'];

// Regrouper compétences par catégorie
$compParCategorie = [];
foreach ($competences as $c) {
    $cat = $c->getCategorie() ?: 'Autres';
    $compParCategorie[$cat][] = $c;
}

// Calculer durée des expériences
function dureeExp($debut, $fin, $enCours): string {
    $d = new DateTime($debut);
    $f = $enCours ? new DateTime() : ($fin ? new DateTime($fin) : new DateTime());
    $diff = $d->diff($f);
    $parts = [];
    if ($diff->y > 0) $parts[] = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) $parts[] = $diff->m . ' mois';
    return implode(' ', $parts) ?: '< 1 mois';
}

function niveauWidth(string $n): int {
    return match(strtolower($n)) {
        'expert' => 100, 'avancé' => 75, 'intermédiaire' => 50, default => 25
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>CV — <?= htmlspecialchars($cv->getTitrePoste()) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
  body { background:#f0f2f8; }

  /* ── Bouton imprimer (caché à l'impression) ── */
  .print-bar {
    background:#1d2b4f;
    padding:14px 40px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    position:sticky;
    top:0;
    z-index:100;
  }
  .print-bar h2 { color:#fff; font-size:1rem; font-weight:600; }
  .btn-print {
    background:linear-gradient(135deg,#e63946,#c1121f);
    color:#fff;
    border:none;
    border-radius:30px;
    padding:10px 28px;
    font-size:.9rem;
    font-weight:600;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
    box-shadow:0 4px 16px rgba(230,57,70,.35);
  }
  .btn-back {
    color:#8899bb;
    text-decoration:none;
    font-size:.85rem;
    display:flex;
    align-items:center;
    gap:6px;
  }
  .btn-back:hover { color:#fff; }

  /* ── Wrapper page CV ── */
  .cv-page {
    width: 210mm;
    min-height: 297mm;
    margin: 30px auto;
    background: #fff;
    box-shadow: 0 8px 40px rgba(0,0,0,.15);
    display: flex;
    overflow: hidden;
    border-radius: 4px;
  }

  /* ── Colonne gauche ── */
  .cv-left {
    width: 72mm;
    background: linear-gradient(180deg, #1d2b4f 0%, #0f1a36 100%);
    padding: 36px 20px;
    color: #fff;
    flex-shrink: 0;
  }

  .cv-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e63946, #f4a261);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 800;
    color: #fff;
    margin: 0 auto 18px;
    border: 3px solid rgba(255,255,255,.2);
  }

  .cv-left h1 {
    font-size: 1.05rem;
    font-weight: 700;
    text-align: center;
    color: #fff;
    margin-bottom: 4px;
    line-height: 1.3;
  }

  .cv-left .poste {
    text-align: center;
    font-size: .72rem;
    color: #e63946;
    font-weight: 600;
    margin-bottom: 24px;
    text-transform: uppercase;
    letter-spacing: .5px;
  }

  .section-left {
    margin-bottom: 22px;
  }

  .section-left h3 {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #e63946;
    border-bottom: 1px solid rgba(230,57,70,.4);
    padding-bottom: 5px;
    margin-bottom: 12px;
  }

  .contact-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
    font-size: .72rem;
    color: #c8d6e5;
    line-height: 1.4;
  }

  .contact-item i {
    color: #e63946;
    font-size: .75rem;
    margin-top: 2px;
    flex-shrink: 0;
    width: 14px;
  }

  .skill-item { margin-bottom: 10px; }
  .skill-name {
    font-size: .7rem;
    color: #c8d6e5;
    margin-bottom: 4px;
    display: flex;
    justify-content: space-between;
  }
  .skill-cat {
    font-size: .6rem;
    color: #8899bb;
  }
  .skill-bar {
    height: 5px;
    background: rgba(255,255,255,.1);
    border-radius: 3px;
    overflow: hidden;
  }
  .skill-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #e63946, #f4a261);
  }

  /* ── Colonne droite ── */
  .cv-right {
    flex: 1;
    padding: 36px 28px;
    overflow: hidden;
  }

  .cv-right .cv-name {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1d2b4f;
    margin-bottom: 2px;
    line-height: 1.2;
  }

  .cv-right .cv-poste-right {
    font-size: .85rem;
    color: #e63946;
    font-weight: 600;
    margin-bottom: 14px;
  }

  .cv-right .cv-desc {
    font-size: .78rem;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f2f8;
  }

  .section-right { margin-bottom: 22px; }

  .section-right h3 {
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #1d2b4f;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
  }

  .section-right h3::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #e63946, transparent);
    border-radius: 1px;
  }

  .section-right h3 i {
    color: #e63946;
    font-size: .85rem;
  }

  /* Expériences */
  .exp-item {
    display: flex;
    gap: 12px;
    margin-bottom: 14px;
    position: relative;
  }

  .exp-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 28px;
    bottom: -14px;
    width: 1px;
    background: #e2e8f0;
  }

  .exp-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e63946, #f4a261);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 1;
  }

  .exp-dot i { color: #fff; font-size: .65rem; }

  .exp-content { flex: 1; min-width: 0; }

  .exp-titre {
    font-size: .8rem;
    font-weight: 700;
    color: #1d2b4f;
    margin-bottom: 1px;
  }

  .exp-entreprise {
    font-size: .72rem;
    color: #457b9d;
    font-weight: 600;
    margin-bottom: 3px;
  }

  .exp-meta {
    font-size: .67rem;
    color: #8899bb;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 5px;
  }

  .exp-desc {
    font-size: .7rem;
    color: #64748b;
    line-height: 1.5;
  }

  .badge-contrat {
    font-size: .62rem;
    padding: 2px 8px;
    border-radius: 20px;
    background: rgba(69,123,157,.1);
    color: #457b9d;
    font-weight: 600;
  }

  .badge-encours {
    background: rgba(42,157,143,.12);
    color: #2a9d8f;
  }

  /* Liens en bas */
  .cv-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 8px;
  }

  .cv-link {
    font-size: .7rem;
    padding: 4px 12px;
    border-radius: 20px;
    background: #f0f2f8;
    color: #1d2b4f;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
  }

  .cv-link i { color: #e63946; }

  /* ── Impression ── */
  @media print {
    body { background: #fff; }
    .print-bar { display: none !important; }
    .cv-page {
      margin: 0;
      box-shadow: none;
      border-radius: 0;
      width: 100%;
      min-height: 100%;
    }
  }

  @page {
    size: A4;
    margin: 0;
  }
</style>
</head>
<body>

<!-- Barre d'impression -->
<div class="print-bar">
  <a href="list_cvs.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux CV</a>
  <h2><i class="fas fa-file-pdf" style="color:#e63946;margin-right:8px"></i>Aperçu du CV — <?= htmlspecialchars($cv->getTitrePoste()) ?></h2>
  <button class="btn-print" onclick="window.print()">
    <i class="fas fa-download"></i> Télécharger PDF
  </button>
</div>

<!-- Page CV -->
<div class="cv-page">

  <!-- Colonne gauche -->
  <div class="cv-left">

    <!-- Avatar -->
    <div class="cv-avatar">
      <?= strtoupper(substr($cv->getTitrePoste(), 0, 2)) ?>
    </div>
    <h1><?= htmlspecialchars($cv->getEmail()) ?></h1>
    <div class="poste"><?= htmlspecialchars($cv->getTitrePoste()) ?></div>

    <!-- Contact -->
    <div class="section-left">
      <h3><i class="fas fa-address-card"></i> Contact</h3>

      <?php if ($cv->getTelephone()): ?>
      <div class="contact-item">
        <i class="fas fa-phone"></i>
        <span><?= htmlspecialchars($cv->getTelephone()) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($cv->getAdresse()): ?>
      <div class="contact-item">
        <i class="fas fa-map-marker-alt"></i>
        <span><?= htmlspecialchars($cv->getAdresse()) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($cv->getDateNaissance()): ?>
      <div class="contact-item">
        <i class="fas fa-birthday-cake"></i>
        <span><?= date('d/m/Y', strtotime($cv->getDateNaissance())) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($cv->getGithub()): ?>
      <div class="contact-item">
        <i class="fab fa-github"></i>
        <span style="word-break:break-all"><?= htmlspecialchars($cv->getGithub()) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($cv->getLinkedin()): ?>
      <div class="contact-item">
        <i class="fab fa-linkedin"></i>
        <span style="word-break:break-all"><?= htmlspecialchars($cv->getLinkedin()) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($cv->getSiteWeb()): ?>
      <div class="contact-item">
        <i class="fas fa-globe"></i>
        <span style="word-break:break-all"><?= htmlspecialchars($cv->getSiteWeb()) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Compétences par catégorie -->
    <?php foreach ($compParCategorie as $cat => $comps): ?>
    <div class="section-left">
      <h3><?= htmlspecialchars($cat) ?></h3>
      <?php foreach ($comps as $c): ?>
      <div class="skill-item">
        <div class="skill-name">
          <span><?= htmlspecialchars($c->getNomCompetence()) ?></span>
          <span class="skill-cat"><?= htmlspecialchars($c->getNiveau()) ?></span>
        </div>
        <div class="skill-bar">
          <div class="skill-fill" style="width:<?= niveauWidth($c->getNiveau()) ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

  </div><!-- /cv-left -->

  <!-- Colonne droite -->
  <div class="cv-right">

    <div class="cv-name"><?= htmlspecialchars($cv->getEmail()) ?></div>
    <div class="cv-poste-right"><?= htmlspecialchars($cv->getTitrePoste()) ?></div>

    <?php if ($cv->getDescription()): ?>
    <div class="cv-desc"><?= nl2br(htmlspecialchars($cv->getDescription())) ?></div>
    <?php endif; ?>

    <!-- Expériences -->
    <?php if (!empty($experiences)): ?>
    <div class="section-right">
      <h3><i class="fas fa-briefcase"></i> Expériences professionnelles</h3>
      <?php foreach ($experiences as $exp): ?>
      <div class="exp-item">
        <div class="exp-dot"><i class="fas fa-building"></i></div>
        <div class="exp-content">
          <div class="exp-titre"><?= htmlspecialchars($exp->getTitre()) ?></div>
          <div class="exp-entreprise"><?= htmlspecialchars($exp->getEntreprise()) ?></div>
          <div class="exp-meta">
            <?php if ($exp->getLieu()): ?>
            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($exp->getLieu()) ?></span>
            <?php endif; ?>
            <span>
              <i class="fas fa-calendar"></i>
              <?= date('m/Y', strtotime($exp->getDateDebut())) ?> —
              <?= $exp->isEnCours() ? '<span class="badge-contrat badge-encours">En cours</span>' : date('m/Y', strtotime($exp->getDateFin())) ?>
            </span>
            <span class="badge-contrat"><?= htmlspecialchars($exp->getTypeContrat()) ?></span>
            <span style="color:#2a9d8f;font-weight:500">
              <?= dureeExp($exp->getDateDebut(), $exp->getDateFin(), $exp->isEnCours()) ?>
            </span>
          </div>
          <?php if ($exp->getDescription()): ?>
          <div class="exp-desc"><?= nl2br(htmlspecialchars($exp->getDescription())) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Compétences résumé (droite) -->
    <?php if (!empty($competences)): ?>
    <div class="section-right">
      <h3><i class="fas fa-tools"></i> Résumé compétences</h3>
      <div style="display:flex;flex-wrap:wrap;gap:7px;">
        <?php foreach ($competences as $c):
          $col = match(strtolower($c->getNiveau())) {
            'expert'         => '#155724;background:#d1fadf',
            'avancé'         => '#1a56db;background:#e8f0fe',
            'intermédiaire'  => '#856404;background:#fff3cd',
            default          => '#c1121f;background:#fde8ea'
          };
        ?>
        <span style="font-size:.67rem;padding:3px 10px;border-radius:20px;font-weight:600;color:<?= $col ?>">
          <?= htmlspecialchars($c->getNomCompetence()) ?> — <?= htmlspecialchars($c->getNiveau()) ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Liens -->
    <?php if ($cv->getGithub() || $cv->getLinkedin() || $cv->getSiteWeb()): ?>
    <div class="section-right">
      <h3><i class="fas fa-link"></i> Liens</h3>
      <div class="cv-links">
        <?php if ($cv->getGithub()): ?><a href="<?= htmlspecialchars($cv->getGithub()) ?>" class="cv-link" target="_blank"><i class="fab fa-github"></i> GitHub</a><?php endif; ?>
        <?php if ($cv->getLinkedin()): ?><a href="<?= htmlspecialchars($cv->getLinkedin()) ?>" class="cv-link" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a><?php endif; ?>
        <?php if ($cv->getSiteWeb()): ?><a href="<?= htmlspecialchars($cv->getSiteWeb()) ?>" class="cv-link" target="_blank"><i class="fas fa-globe"></i> Site web</a><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /cv-right -->

</div><!-- /cv-page -->
</body>
</html>
