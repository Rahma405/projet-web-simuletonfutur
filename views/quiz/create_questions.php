<?php
$pageTitle = 'Build Questions';
$activeNav = 'create';
require __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-header"><i class="fas fa-question-circle me-2"></i>Step 2 — <?= (int)$nb ?> Question<?= $nb > 1 ? 's' : '' ?> for <em><?= htmlspecialchars($type) ?></em></div>
  <div class="card-body p-4">
    <form method="POST" action="index.php?route=/quizzes/store" novalidate>
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
      <input type="hidden" name="des"  value="<?= htmlspecialchars($des) ?>">
      <input type="hidden" name="nb"   value="<?= (int)$nb ?>">

      <?php for ($i = 0; $i < (int)$nb; $i++): ?>
        <div class="card mb-3" style="border:1px solid #dde3f0;box-shadow:none">
          <div class="card-body p-3">
            <p class="fw-semibold mb-2" style="color:#e63946;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Question <?= $i+1 ?></p>
            <div class="mb-3">
              <input type="text" name="questions[<?= $i ?>][text]" class="form-control"
                     placeholder="Enter question text…"
                     value="<?= htmlspecialchars($questions[$i]['text'] ?? '') ?>">
            </div>
            <div class="row g-2">
              <?php for ($j = 0; $j < 4; $j++): ?>
                <div class="col-md-6">
                  <div class="input-group">
                    <div class="input-group-text" style="background:#eef3fb;border-color:#dde3f0">
                      <input type="radio" name="questions[<?= $i ?>][correct]" value="<?= $j ?>"
                             <?= isset($questions[$i]['correct']) && (int)$questions[$i]['correct'] === $j ? 'checked' : '' ?>>
                    </div>
                    <input type="text" name="questions[<?= $i ?>][answers][<?= $j ?>][text]"
                           class="form-control" placeholder="Answer <?= $j+1 ?>…"
                           value="<?= htmlspecialchars($questions[$i]['answers'][$j]['text'] ?? '') ?>">
                  </div>
                </div>
              <?php endfor; ?>
            </div>
            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Select the radio button next to the correct answer.</small>
          </div>
        </div>
      <?php endfor; ?>

      <div class="d-flex gap-2 justify-content-end mt-2">
        <a href="index.php?route=/quizzes/create" class="btn btn-outline-secondary">← Back</a>
        <button type="submit" class="btn-red" style="border-radius:9px;padding:10px 24px"><i class="fas fa-save me-1"></i>Save Quiz</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
