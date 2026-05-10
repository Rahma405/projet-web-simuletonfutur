<?php
$pageTitle = htmlspecialchars($quiz['type']);
require __DIR__ . '/header.php';
?>

<section class="container py-5">
  <div class="mb-4">
    <a href="index.php?route=/front" class="text-muted" style="font-size:.85rem;text-decoration:none"><i class="fas fa-arrow-left me-1"></i>All Quizzes</a>
    <h2 class="fw-bold mt-2 mb-1" style="color:#1d2b4f"><?= htmlspecialchars($quiz['type']) ?></h2>
    <p class="text-muted mb-0" style="font-size:.9rem"><?= htmlspecialchars($quiz['description']) ?> &mdash; <strong><?= count($questions) ?></strong> question<?= count($questions) !== 1 ? 's' : '' ?></p>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?php foreach ($errors as $e): ?><p class="mb-0"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="POST" action="index.php?route=/front/submit" id="quizForm" novalidate>
    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

    <!-- Progress bar (injected by front.js) -->
    <div id="progressWrap"></div>

    <?php foreach ($questions as $i => $q): ?>
      <div class="card mb-3" id="qcard-<?= $q['id'] ?>">
        <div class="card-body p-4">
          <p class="fw-semibold mb-1" style="font-size:.75rem;color:#e63946;text-transform:uppercase;letter-spacing:.06em">Question <?= $i+1 ?></p>
          <p class="fw-bold mb-3" style="color:#1d2b4f"><?= htmlspecialchars($q['question_text']) ?></p>

          <div class="d-flex flex-column gap-2">
            <?php foreach ($q['answers'] as $ans): ?>
              <label class="d-flex align-items-center gap-3 p-3 rounded answer-option <?= isset($submitted[$q['id']]) && (int)$submitted[$q['id']] === $ans['id'] ? 'selected' : '' ?>"
                     style="border:1.5px solid #dde3f0;cursor:pointer;transition:all .2s">
                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $ans['id'] ?>"
                       <?= isset($submitted[$q['id']]) && (int)$submitted[$q['id']] === $ans['id'] ? 'checked' : '' ?>
                       style="accent-color:#e63946">
                <span style="font-size:.9rem"><?= htmlspecialchars($ans['answer_text']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="invalid-feedback d-block mt-2" id="err-<?= $q['id'] ?>"></div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-end">
      <button type="submit" class="btn-hero"><i class="fas fa-paper-plane me-1"></i>Submit Answers</button>
    </div>
  </form>
</section>

<style>
.answer-option:hover  { border-color:#e63946 !important; background:#fde8ea; }
.answer-option.selected { border-color:#e63946 !important; background:#fde8ea; }
.card.border-danger { border-color:#e63946 !important; }
</style>

<script>
// Highlight on select
document.querySelectorAll('.answer-option input[type="radio"]').forEach(function(r) {
  r.addEventListener('change', function() {
    const name = this.getAttribute('name');
    document.querySelectorAll('input[name="' + name + '"]').forEach(function(x) {
      x.closest('.answer-option').classList.remove('selected');
    });
    this.closest('.answer-option').classList.add('selected');
    const match = name.match(/\d+/);
    if (match) {
      document.getElementById('err-' + match[0]).textContent = '';
      document.getElementById('qcard-' + match[0]).classList.remove('border', 'border-danger');
    }
  });
});

// JS Validation — no HTML5 required
document.getElementById('quizForm').addEventListener('submit', function(e) {
  let valid = true;
  <?php foreach ($questions as $q): ?>
  (function() {
    const qId    = <?= $q['id'] ?>;
    const radios = document.querySelectorAll('input[name="answers[' + qId + ']"]');
    const errEl  = document.getElementById('err-' + qId);
    let answered = false;
    radios.forEach(function(r) { if (r.checked) answered = true; });
    if (!answered) {
      errEl.textContent = 'Please select an answer.';
      errEl.style.color = '#e63946';
      valid = false;
    }
  })();
  <?php endforeach; ?>
  if (!valid) {
    e.preventDefault();
    document.querySelector('[style*="invalid-feedback"]:not(:empty), .invalid-feedback:not(:empty)')
      ?.closest('.card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});
</script>

<?php require __DIR__ . '/footer.php'; ?>
