<?php
$pageTitle   = 'Results';
$sessionUser = $_SESSION['user'] ?? null;
require __DIR__ . '/header.php';

$percent = $total > 0 ? round(($score / $total) * 100) : 0;
if ($percent === 100)   { $grade = 'Perfect!';       $color = '#16a34a'; $bg = '#d1fadf'; }
elseif ($percent >= 70) { $grade = 'Well done!';     $color = '#1a6fa8'; $bg = '#e8f4fd'; }
elseif ($percent >= 40) { $grade = 'Keep trying!';   $color = '#d97706'; $bg = '#fef3c7'; }
else                    { $grade = 'Keep studying!'; $color = '#e63946'; $bg = '#fde8ea'; }
?>

<section class="container py-5">

  <!-- ── Score ring ────────────────────────────────────────────────────────── -->
  <div class="card mb-4 text-center p-5">
    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
         style="width:110px;height:110px;background:<?= $bg ?>;border:5px solid <?= $color ?>">
      <div>
        <div style="font-size:1.7rem;font-weight:800;color:<?= $color ?>;line-height:1"><?= $percent ?>%</div>
        <div style="font-size:.78rem;color:<?= $color ?>;margin-top:2px"><?= $score ?>/<?= $total ?></div>
      </div>
    </div>
    <h3 class="fw-bold mb-1" style="color:#1d2b4f"><?= $grade ?></h3>
    <p class="text-muted mb-0">
      You scored <strong><?= $score ?></strong> out of <strong><?= $total ?></strong>
      on <em><?= htmlspecialchars($quiz['type']) ?></em>
    </p>

    <!-- Result saved badge -->
    <?php if ($userId !== null): ?>
      <div class="mt-3">
        <span class="badge" style="background:#d1fadf;color:#16a34a;font-size:.82rem;padding:6px 14px;border-radius:20px">
          <i class="fas fa-check-circle me-1"></i>
          Result saved to your history
          — <a href="index.php?route=/front/my-results" style="color:#16a34a;font-weight:700;text-decoration:underline">View My Results</a>
        </span>
      </div>
    <?php else: ?>
      <div class="mt-3">
        <span class="badge" style="background:#fef3c7;color:#d97706;font-size:.82rem;padding:6px 14px;border-radius:20px">
          <i class="fas fa-exclamation-triangle me-1"></i>
          <a href="index.php?route=/login" style="color:#d97706;font-weight:700">Log in</a>
          to save your results to your history
        </span>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── Answer breakdown ──────────────────────────────────────────────────── -->
  <?php if (!empty($results)): ?>
  <div class="card mb-4">
    <div class="card-header"><i class="fas fa-list-check me-2"></i>Answer Breakdown</div>
    <div class="card-body p-0">
      <?php foreach ($results as $i => $r): ?>
        <div class="p-4 border-bottom"
             style="border-left:4px solid <?= $r['is_correct'] ? '#16a34a' : '#e63946' ?>">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span style="font-size:.75rem;color:#8899bb;font-weight:600;text-transform:uppercase;letter-spacing:.06em">
              Q<?= $i + 1 ?>
            </span>
            <?php if ($r['is_correct']): ?>
              <span class="badge" style="background:#d1fadf;color:#16a34a">
                <i class="fas fa-check me-1"></i>Correct
              </span>
            <?php else: ?>
              <span class="badge" style="background:#fde8ea;color:#e63946">
                <i class="fas fa-times me-1"></i>Wrong
              </span>
            <?php endif; ?>
          </div>
          <p class="fw-bold mb-2" style="color:#1d2b4f;font-size:.9rem">
            <?= htmlspecialchars($r['question_text']) ?>
          </p>
          <div style="font-size:.84rem">
            <span class="text-muted">Your answer: </span>
            <strong style="color:<?= $r['is_correct'] ? '#16a34a' : '#e63946' ?>">
              <?= $r['skipped'] ? '<em>Not answered</em>' : htmlspecialchars($r['chosen_answer_text']) ?>
            </strong>
            <?php if (!$r['is_correct']): ?>
              <span class="ms-3 text-muted">Correct: </span>
              <strong style="color:#16a34a"><?= htmlspecialchars($r['correct_answer_text']) ?></strong>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Feedback form ─────────────────────────────────────────────────────── -->
  <div class="card mb-4">
    <div class="card-header"
         style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;border-radius:14px 14px 0 0;padding:15px 22px;font-weight:600">
      <i class="fas fa-comment-dots me-2"></i>Leave Feedback
    </div>
    <div class="card-body p-4">

      <?php if ($userId !== null): ?>

        <!-- Logged-in: show user chip, no ID field needed -->
        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded"
             style="background:#f0f3fa;border:1px solid #dde3f0">
          <div class="avatar" style="width:36px;height:36px;font-size:.78rem;flex-shrink:0">
            <?= strtoupper(
              substr($sessionUser['prenom'] ?? 'U', 0, 1) .
              substr($sessionUser['nom']    ?? 'S', 0, 1)
            ) ?>
          </div>
          <div>
            <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f">
              <?= htmlspecialchars(($sessionUser['prenom'] ?? '') . ' ' . ($sessionUser['nom'] ?? '')) ?>
            </div>
            <div style="font-size:.78rem;color:#8899bb">
              <?= htmlspecialchars($sessionUser['email'] ?? '') ?>
              &nbsp;·&nbsp; ID #<?= $userId ?>
            </div>
          </div>
          <span class="ms-auto badge" style="background:#d1fadf;color:#16a34a;font-size:.75rem">
            <i class="fas fa-lock me-1"></i>Auto-detected
          </span>
        </div>

        <?php if (!empty($feedbackErrors)): ?>
          <div class="alert alert-danger mb-3">
            <ul class="mb-0 ps-3">
              <?php foreach ($feedbackErrors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=/front/feedback" id="feedbackForm" novalidate>
          <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
          <input type="hidden" name="score"   value="<?= $score ?>">
          <input type="hidden" name="total"   value="<?= $total ?>">
          <!-- No user_id field — read from session server-side -->

          <div class="mb-3">
            <label class="form-label" style="font-weight:600;font-size:.85rem;color:#1d2b4f">
              Your Feedback <span style="color:#e63946">*</span>
            </label>
            <textarea name="feedback" id="feedbackText" class="form-control" rows="4"
                      maxlength="1000"
                      placeholder="What did you think of this quiz? Was it too easy, too hard? Any suggestions?"><?= htmlspecialchars($feedbackText ?? '') ?></textarea>
            <div class="d-flex justify-content-between mt-1">
              <div class="invalid-feedback d-block" id="feedbackError"></div>
              <small class="text-muted ms-auto" id="charCount">0 / 1000</small>
            </div>
          </div>

          <button type="submit" class="btn-hero" style="padding:10px 28px;font-size:.9rem">
            <i class="fas fa-paper-plane me-2"></i>Submit Feedback
          </button>
        </form>

      <?php else: ?>

        <!-- Guest: prompt to log in -->
        <div class="text-center py-4">
          <i class="fas fa-lock fa-2x mb-3 text-muted"></i>
          <p class="text-muted mb-3">You need to be logged in to leave feedback.</p>
          <div class="d-flex gap-2 justify-content-center">
            <a href="index.php?route=/login" class="btn-hero" style="padding:9px 22px;font-size:.88rem">
              <i class="fas fa-sign-in-alt me-1"></i>Log In
            </a>
            <a href="index.php?route=/register" class="btn btn-outline-secondary" style="border-radius:9px">
              <i class="fas fa-user-plus me-1"></i>Register
            </a>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>

  <!-- ── Actions ───────────────────────────────────────────────────────────── -->
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="index.php?route=/front/play&id=<?= $quiz['id'] ?>" class="btn btn-outline-danger">
      <i class="fas fa-redo me-1"></i>Try Again
    </a>
    <?php if ($userId !== null): ?>
      <a href="index.php?route=/front/my-results" class="btn btn-outline-primary">
        <i class="fas fa-history me-1"></i>My Results
      </a>
    <?php endif; ?>
    <a href="index.php?route=/front" class="btn-hero" style="padding:10px 24px;font-size:.9rem">
      <i class="fas fa-home me-1"></i>Browse Quizzes
    </a>
  </div>

</section>

<script>
var textarea = document.getElementById('feedbackText');
if (textarea) {
  var counter = document.getElementById('charCount');
  function updateCount() { counter.textContent = textarea.value.length + ' / 1000'; }
  textarea.addEventListener('input', updateCount);
  updateCount();

  document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    var feedback = textarea.value.trim();
    document.getElementById('feedbackText').classList.remove('is-invalid');
    document.getElementById('feedbackError').textContent = '';
    if (!feedback) {
      document.getElementById('feedbackText').classList.add('is-invalid');
      document.getElementById('feedbackError').textContent = 'Feedback cannot be empty.';
      e.preventDefault();
    }
  });
}
</script>

<?php require __DIR__ . '/footer.php'; ?>
