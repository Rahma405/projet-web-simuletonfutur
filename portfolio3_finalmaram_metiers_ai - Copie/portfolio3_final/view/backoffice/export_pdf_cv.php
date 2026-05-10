<?php
session_start();
require_once __DIR__ . '/../../controller/CvC.php';

$ctrl = new CvC();
$id   = (int)($_GET['id'] ?? 0);
$data = $ctrl->getCvWithAll($id);

if (!$data) { header('Location: list_cvs.php'); exit; }

$cv          = $data['cv'];
$competences = $data['competences'];
$experiences = $data['experiences'] ?? [];

// Initiales
$initiales = '';
foreach (array_slice(explode(' ', $cv['titre_poste']), 0, 2) as $m)
    $initiales .= strtoupper(mb_substr($m, 0, 1));
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Aperçu du CV — <?= htmlspecialchars($cv['titre_poste']) ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
:root{--dark:#1a2744;--red:#e63946;--light:#f4f6fb;}

/* ── NAVBAR ─────────────────────────────────── */
.navbar{
  background:var(--dark);
  padding:14px 32px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:sticky;top:0;z-index:999;
}
.navbar-title{
  color:#fff;font-size:1.05rem;font-weight:600;
  display:flex;align-items:center;gap:10px;
}
.navbar-title i{color:var(--red);font-size:1.1rem;}
.btn-pdf{
  background:var(--red);color:#fff;border:none;border-radius:8px;
  padding:10px 22px;font-size:.9rem;font-weight:600;cursor:pointer;
  display:flex;align-items:center;gap:8px;transition:opacity .2s;
  text-decoration:none;
}
.btn-pdf:hover{opacity:.85;color:#fff;}
.btn-back{
  background:transparent;color:rgba(255,255,255,.6);border:none;
  font-size:.85rem;cursor:pointer;display:flex;align-items:center;gap:6px;
  text-decoration:none;margin-right:16px;transition:color .2s;
}
.btn-back:hover{color:#fff;}

/* ── PAGE WRAPPER ───────────────────────────── */
body{background:var(--light);}
.page-wrap{
  max-width:1000px;
  margin:30px auto 40px;
  display:flex;
  min-height:800px;
  box-shadow:0 4px 40px rgba(0,0,0,.13);
  border-radius:6px;
  overflow:hidden;
}

/* ── SIDEBAR ────────────────────────────────── */
.sidebar{
  width:300px;
  flex-shrink:0;
  background:var(--dark);
  color:#fff;
  padding:36px 24px;
  display:flex;
  flex-direction:column;
  gap:28px;
}

/* Avatar */
.avatar-wrap{text-align:center;margin-bottom:4px;}
.avatar{
  width:90px;height:90px;border-radius:50%;
  background:linear-gradient(135deg,#e63946,#f4a261);
  display:flex;align-items:center;justify-content:center;
  font-size:2rem;font-weight:700;color:#fff;
  margin:0 auto 14px;
  box-shadow:0 4px 20px rgba(230,57,70,.4);
}
.avatar-email{
  font-size:.8rem;color:rgba(255,255,255,.75);
  word-break:break-all;margin-bottom:4px;
}
.avatar-poste{
  font-size:.78rem;font-weight:700;letter-spacing:.08em;
  text-transform:uppercase;color:var(--red);
}

/* Sections sidebar */
.s-block{}
.s-label{
  display:flex;align-items:center;gap:8px;
  font-size:.7rem;font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;color:var(--red);
  padding-bottom:7px;
  border-bottom:1px solid rgba(230,57,70,.25);
  margin-bottom:12px;
}
.s-label i{font-size:.75rem;}

/* Contact items */
.contact-item{
  display:flex;align-items:flex-start;gap:10px;
  font-size:.83rem;color:rgba(255,255,255,.75);
  margin-bottom:9px;line-height:1.4;
}
.contact-item i{color:var(--red);width:14px;flex-shrink:0;margin-top:2px;}
.contact-item a{color:rgba(255,255,255,.65);text-decoration:none;word-break:break-all;}
.contact-item a:hover{color:#fff;}

/* Compétences sidebar */
.skill-item{margin-bottom:12px;}
.skill-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;}
.skill-name{font-size:.82rem;font-weight:500;color:#fff;}
.skill-level{font-size:.72rem;color:rgba(255,255,255,.4);}
.skill-bar{height:3px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden;}
.skill-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--red),#f4a261);}

/* ── MAIN ───────────────────────────────────── */
.main{
  flex:1;
  background:#fff;
  padding:40px 44px;
  display:flex;
  flex-direction:column;
  gap:28px;
}

/* En-tête principal */
.main-head{}
.main-head h1{
  font-size:1.7rem;font-weight:700;
  color:var(--dark);margin-bottom:4px;
}
.main-head .poste{
  font-size:1rem;font-weight:600;color:var(--red);margin-bottom:12px;
}
.main-head .desc{
  font-size:.88rem;color:#5a6577;line-height:1.65;
  border-top:1px solid #eef0f5;padding-top:12px;
}

/* Sections main */
.m-block{}
.m-title{
  display:flex;align-items:center;gap:10px;
  font-size:.72rem;font-weight:700;letter-spacing:.12em;
  text-transform:uppercase;color:var(--dark);
  margin-bottom:16px;
}
.m-title i{color:var(--red);}
.m-title::after{content:'';flex:1;height:1px;background:#eef0f5;}

/* Liens badges */
.links-wrap{display:flex;flex-wrap:wrap;gap:10px;}
.link-badge{
  display:inline-flex;align-items:center;gap:7px;
  padding:7px 16px;border-radius:20px;
  border:1px solid #e0e4ef;
  font-size:.82rem;color:#3a4567;text-decoration:none;
  transition:all .2s;
}
.link-badge:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.link-badge i{font-size:.85rem;}
.link-badge.gh i{color:#333;}
.link-badge.li i{color:#0077b5;}
.link-badge.web i{color:#2a9d8f;}

/* Compétences main (par catégorie) */
.comp-cats{display:flex;flex-direction:column;gap:16px;}
.comp-cat-title{
  font-size:.72rem;font-weight:700;letter-spacing:.08em;
  text-transform:uppercase;color:#aab;margin-bottom:8px;
}
.comp-grid{display:flex;flex-wrap:wrap;gap:8px;}
.comp-tag{
  display:inline-flex;align-items:center;gap:6px;
  padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;
}
.comp-tag.expert      {background:#d1fae5;color:#065f46;}
.comp-tag.avance      {background:#dbeafe;color:#1e40af;}
.comp-tag.intermediaire{background:#fef3c7;color:#92400e;}
.comp-tag.debutant    {background:#fee2e2;color:#991b1b;}
.comp-tag .dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.6;}

/* Expériences */
.exp-list{display:flex;flex-direction:column;gap:18px;}
.exp-item{
  display:grid;grid-template-columns:110px 1fr;gap:0 18px;
  padding-bottom:18px;border-bottom:1px dashed #eef0f5;
}
.exp-item:last-child{border-bottom:none;padding-bottom:0;}
.exp-date{text-align:right;padding-top:2px;}
.exp-period{font-size:.8rem;font-weight:600;color:var(--red);line-height:1.5;}
.exp-lieu{font-size:.72rem;color:#aab;margin-top:3px;}
.exp-titre{font-size:.95rem;font-weight:700;color:var(--dark);margin-bottom:2px;}
.exp-ent{font-size:.83rem;color:#5a6577;margin-bottom:5px;display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.badge-ct{
  font-size:.68rem;background:#f0f3fa;color:#457b9d;
  padding:2px 9px;border-radius:10px;font-weight:600;
}
.badge-ec{
  font-size:.68rem;background:#eaf7f0;color:#1a7a4a;
  padding:2px 9px;border-radius:10px;font-weight:600;
}
.exp-desc{font-size:.82rem;color:#7a8599;line-height:1.6;}

/* ── PRINT ──────────────────────────────────── */
@media print{
  .navbar{display:none!important;}
  body{background:#fff;}
  .page-wrap{
    margin:0;max-width:100%;
    box-shadow:none;border-radius:0;
  }
  .sidebar{width:260px;}
  *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div style="display:flex;align-items:center;gap:6px">
    <a href="list_cvs.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
    <div class="navbar-title">
      <i class="fas fa-file-pdf"></i>
      Aperçu du CV — <?= htmlspecialchars($cv['titre_poste']) ?>
    </div>
  </div>
  <button class="btn-pdf" onclick="window.print()">
    <i class="fas fa-download"></i> Télécharger PDF
  </button>
</div>

<!-- PAGE -->
<div class="page-wrap">

  <!-- ══ SIDEBAR ══ -->
  <div class="sidebar">

    <!-- Avatar -->
    <div class="avatar-wrap">
      <div class="avatar"><?= $initiales ?></div>
      <div class="avatar-email"><?= htmlspecialchars($cv['email']) ?></div>
      <div class="avatar-poste"><?= htmlspecialchars($cv['titre_poste']) ?></div>
    </div>

    <!-- Contact -->
    <div class="s-block">
      <div class="s-label"><i class="fas fa-address-card"></i> Contact</div>
      <?php if($cv['telephone']): ?>
        <div class="contact-item"><i class="fas fa-phone"></i><?= htmlspecialchars($cv['telephone']) ?></div>
      <?php endif; ?>
      <?php if($cv['adresse']): ?>
        <div class="contact-item"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($cv['adresse']) ?></div>
      <?php endif; ?>
      <?php if($cv['date_naissance']): ?>
        <div class="contact-item"><i class="fas fa-calendar"></i><?= date('d/m/Y', strtotime($cv['date_naissance'])) ?></div>
      <?php endif; ?>
      <?php if($cv['github']): ?>
        <div class="contact-item"><i class="fab fa-github"></i><a href="<?= htmlspecialchars($cv['github']) ?>"><?= htmlspecialchars($cv['github']) ?></a></div>
      <?php endif; ?>
      <?php if($cv['linkedin']): ?>
        <div class="contact-item"><i class="fab fa-linkedin"></i><a href="<?= htmlspecialchars($cv['linkedin']) ?>"><?= htmlspecialchars($cv['linkedin']) ?></a></div>
      <?php endif; ?>
      <?php if($cv['site_web']): ?>
        <div class="contact-item"><i class="fas fa-globe"></i><a href="<?= htmlspecialchars($cv['site_web']) ?>"><?= htmlspecialchars($cv['site_web']) ?></a></div>
      <?php endif; ?>
    </div>

    <!-- Compétences dans sidebar -->
    <?php if(!empty($competences)): ?>
    <div class="s-block">
      <div class="s-label"><i class="fas fa-bolt"></i> Compétences</div>
      <?php foreach($competences as $c):
        $pct = match(strtolower($c['niveau'])){
          'expert'=>100,'avancé'=>75,'intermédiaire'=>50,default=>25
        };
      ?>
      <div class="skill-item">
        <div class="skill-head">
          <span class="skill-name"><?= htmlspecialchars($c['nom_competence']) ?></span>
          <span class="skill-level"><?= htmlspecialchars($c['niveau']) ?></span>
        </div>
        <div class="skill-bar"><div class="skill-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <!-- ══ MAIN ══ -->
  <div class="main">

    <!-- En-tête -->
    <div class="main-head">
      <h1><?= htmlspecialchars($cv['email']) ?></h1>
      <div class="poste"><?= htmlspecialchars($cv['titre_poste']) ?></div>
      <?php if($cv['description']): ?>
        <div class="desc"><?= nl2br(htmlspecialchars($cv['description'])) ?></div>
      <?php endif; ?>
    </div>

    <!-- Liens -->
    <?php if($cv['github'] || $cv['linkedin'] || $cv['site_web']): ?>
    <div class="m-block">
      <div class="m-title"><i class="fas fa-link"></i> Liens</div>
      <div class="links-wrap">
        <?php if($cv['github']): ?>
          <a href="<?= htmlspecialchars($cv['github']) ?>" target="_blank" class="link-badge gh">
            <i class="fab fa-github"></i> GitHub
          </a>
        <?php endif; ?>
        <?php if($cv['linkedin']): ?>
          <a href="<?= htmlspecialchars($cv['linkedin']) ?>" target="_blank" class="link-badge li">
            <i class="fab fa-linkedin"></i> LinkedIn
          </a>
        <?php endif; ?>
        <?php if($cv['site_web']): ?>
          <a href="<?= htmlspecialchars($cv['site_web']) ?>" target="_blank" class="link-badge web">
            <i class="fas fa-globe"></i> Site web
          </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Expériences -->
    <?php if(!empty($experiences)): ?>
    <div class="m-block">
      <div class="m-title"><i class="fas fa-briefcase"></i> Expériences professionnelles</div>
      <div class="exp-list">
        <?php foreach($experiences as $exp):
          $debut = date('m/Y', strtotime($exp['date_debut']));
          $fin   = $exp['en_cours'] ? 'Présent' : ($exp['date_fin'] ? date('m/Y', strtotime($exp['date_fin'])) : '—');
        ?>
        <div class="exp-item">
          <div class="exp-date">
            <div class="exp-period"><?= $debut ?><br>→ <?= $fin ?></div>
            <?php if($exp['lieu']): ?>
              <div class="exp-lieu"><i class="fas fa-map-pin" style="font-size:.65rem;margin-right:2px"></i><?= htmlspecialchars($exp['lieu']) ?></div>
            <?php endif; ?>
          </div>
          <div>
            <div class="exp-titre"><?= htmlspecialchars($exp['titre']) ?></div>
            <div class="exp-ent">
              <?php if($exp['entreprise']): ?>
                <i class="fas fa-building" style="font-size:.75rem;color:#aab"></i>
                <?= htmlspecialchars($exp['entreprise']) ?>
              <?php endif; ?>
              <span class="badge-ct"><?= htmlspecialchars($exp['type_contrat']) ?></span>
              <?php if($exp['en_cours']): ?>
                <span class="badge-ec"><i class="fas fa-circle" style="font-size:.45rem;margin-right:3px"></i>En cours</span>
              <?php endif; ?>
            </div>
            <?php if($exp['description']): ?>
              <div class="exp-desc"><?= nl2br(htmlspecialchars($exp['description'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(empty($experiences) && !$cv['description']): ?>
      <div style="text-align:center;padding:60px 0;color:#aab">
        <i class="fas fa-file-alt" style="font-size:2.5rem;display:block;margin-bottom:12px"></i>
        Aucune information complémentaire renseignée.
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
