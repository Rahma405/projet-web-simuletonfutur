<?php
session_start();
require_once __DIR__ . '/../../controller/OffreC.php';

$ctrl = new OffreC();
$pageTitle = 'Offres disponibles';
$offres = $ctrl->listOffres();
$sessionUser = $_SESSION['user'] ?? null;

require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1d2b4f">
                <i class="fas fa-briefcase me-2" style="color:#e63946"></i>Offres disponibles
            </h2>
            <p class="text-muted mb-0" style="font-size:.83rem">
                <?= count($offres) ?> offre(s) publiee(s)
            </p>
        </div>
        <?php if ($sessionUser): ?>
            <div class="d-flex gap-2">
                <a href="add_candidature.php" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
                    <i class="fas fa-file-alt me-1"></i>Ma Candidature
                </a>
                <a href="match_offres.php" class="btn-hero"
                    style="padding:9px 20px;font-size:.85rem;background:linear-gradient(135deg,#2a9d8f,#457b9d);">
                    <i class="fas fa-star me-1"></i>Matching
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($offres)): ?>
        <div class="card text-center p-5">
            <i class="fas fa-briefcase fa-3x mb-3 text-muted"></i>
            <p class="text-muted mb-0">Aucune offre disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($offres as $o): ?>
                <?php
                $competences = array_map('trim', explode(',', $o['competences']));
                $colors = ['#e63946', '#f4a261', '#457b9d', '#2a9d8f', '#9b59b6', '#e67e22', '#3498db', '#1abc9c'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 p-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div
                                style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#e63946,#c0392b);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0;">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color:#1d2b4f;font-size:.95rem">
                                    <?= htmlspecialchars($o['titre']) ?>
                                </h6>
                                <span style="font-size:.78rem;color:#8899bb">
                                    <i class="fas fa-map-marker-alt me-1" style="color:#e63946"></i>
                                    <?= htmlspecialchars($o['localisation']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-auto">
                            <?php foreach ($competences as $i => $comp): ?>
                                <span class="badge"
                                    style="background:<?= $colors[$i % count($colors)] ?>20;color:<?= $colors[$i % count($colors)] ?>;font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;">
                                    <?= htmlspecialchars($comp) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>