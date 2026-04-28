<?php
session_start();
require_once __DIR__ . '/../../controller/CandidatureC.php';

$sessionUser = $_SESSION['user'] ?? null;

// ── Rediriger si non connecté ────────────────────────────
if (!$sessionUser) {
    header('Location: login.php');
    exit;
}

$ctrl = new CandidatureC();
$pageTitle = 'Ma Candidature';
$idU = (int) $sessionUser['id'];
$existing = $ctrl->getByIdUtilisateur($idU);
$errors = [];
$success = false;

// ── Traitement du formulaire ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $ctrl->valider($_POST);

    if (empty($errors)) {
        $c = new Candidature();
        $c->setIdUtilisateur($idU);
        $c->setSkills(trim($_POST['skills']));
        $c->setCv(trim($_POST['cv'] ?? ''));
        $c->setLocalisation(trim($_POST['localisation']));

        if ($existing) {
            // Mise à jour
            $ok = $ctrl->updateCandidature($c, $existing->getIdCandidature());
        } else {
            // Création
            $ok = $ctrl->addCandidature($c);
        }

        if ($ok) {
            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => $existing ? 'Candidature mise à jour !' : 'Candidature enregistrée !',
            ];
            header('Location: match_offres.php');
            exit;
        } else {
            $errors['general'] = "Erreur lors de l'enregistrement.";
        }
    }
}

// ── Pré-remplir si candidature existante ─────────────────
$formSkills = $_POST['skills'] ?? ($existing ? $existing->getSkills() : '');
$formCv = $_POST['cv'] ?? ($existing ? $existing->getCv() : '');
$formLoc = $_POST['localisation'] ?? ($existing ? $existing->getLocalisation() : '');

require_once __DIR__ . '/layouts/header.php';
?>

<section class="container py-5" style="max-width:680px">
    <div class="mb-4">
        <h2 class="fw-bold" style="color:#1d2b4f">
            <i class="fas fa-file-alt me-2" style="color:#e63946"></i>
            <?= $existing ? 'Modifier ma candidature' : 'Déposer ma candidature' ?>
        </h2>
        <p class="text-muted" style="font-size:.85rem">
            Renseigne tes compétences pour recevoir des recommandations d'offres personnalisées.
        </p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" novalidate>

            <!-- Skills -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#1d2b4f">
                    <i class="fas fa-code me-1" style="color:#e63946"></i>Compétences
                </label>
                <input type="text" name="skills"
                    class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($formSkills) ?>" placeholder="PHP, MySQL, HTML, CSS, JavaScript">
                <div class="form-text">Sépare tes compétences par des virgules.</div>
                <?php if (isset($errors['skills'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['skills']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- CV -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#1d2b4f">
                    <i class="fas fa-paperclip me-1" style="color:#f4a261"></i>CV (nom du fichier)
                </label>
                <input type="text" name="cv" class="form-control" value="<?= htmlspecialchars($formCv) ?>"
                    placeholder="mon_cv.pdf">
                <div class="form-text">Indique le nom de ton fichier CV.</div>
            </div>

            <!-- Localisation -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#1d2b4f">
                    <i class="fas fa-map-marker-alt me-1" style="color:#2a9d8f"></i>Localisation
                </label>
                <input type="text" name="localisation"
                    class="form-control <?= isset($errors['localisation']) ? 'is-invalid' : '' ?>"
                    value="<?= htmlspecialchars($formLoc) ?>" placeholder="Tunis">
                <?php if (isset($errors['localisation'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['localisation']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn-hero" style="padding:10px 28px;font-size:.9rem;">
                    <i class="fas fa-check me-2"></i>
                    <?= $existing ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="list_offres.php" class="btn btn-outline-secondary" style="padding:10px 20px;font-size:.9rem;">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>

        </form>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>