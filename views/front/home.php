<?php
$pageTitle = 'Browse Quizzes';
require __DIR__ . '/header.php';
?>

<section class="container py-5">

  <div class="mb-4">
    <h2 class="fw-bold mb-1" style="color:#1d2b4f">Available Quizzes</h2>
    <p class="text-muted mb-0" style="font-size:.9rem">Pick a quiz and test your knowledge</p>
  </div>

  <!-- Search + Sort form -->
  <form method="GET" action="index.php" id="filterForm" class="mb-4">
    <input type="hidden" name="route" value="/front">

    <div class="card p-3">
      <div class="row g-3 align-items-end">

        <!-- Search -->
        <div class="col-md-5">
          <label class="form-label fw-semibold" style="font-size:.82rem;color:#1d2b4f">
            <i class="fas fa-search me-1" style="color:#e63946"></i>Search
          </label>
          <div class="input-group">
            <input type="text" name="search" id="searchInput" class="form-control"
                   placeholder="Search by name or description…"
                   value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            <?php if ($search !== ''): ?>
              <a href="index.php?route=/front" class="btn btn-outline-secondary" title="Clear">
                <i class="fas fa-times"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Sort by (radio buttons) -->
        <div class="col-md-4">
          <label class="form-label fw-semibold" style="font-size:.82rem;color:#1d2b4f">
            <i class="fas fa-sort me-1" style="color:#e63946"></i>Sort by
          </label>
          <div class="d-flex gap-3">
            <?php foreach (['id' => '#', 'type' => 'Name', 'question_count' => 'Questions'] as $val => $lbl): ?>
              <div class="form-check">
                <input class="form-check-input" type="radio"
                       name="sort" value="<?= $val ?>" id="fsort_<?= $val ?>"
                       style="accent-color:#e63946"
                       <?= $sortCol === $val ? 'checked' : '' ?>>
                <label class="form-check-label" for="fsort_<?= $val ?>"
                       style="font-size:.86rem;cursor:pointer"><?= $lbl ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Direction + button -->
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:.82rem;color:#1d2b4f">Direction</label>
          <div class="d-flex gap-2">
            <select name="dir" class="form-select form-select-sm">
              <option value="ASC"  <?= $sortDir === 'ASC'  ? 'selected' : '' ?>>A → Z / Low→High</option>
              <option value="DESC" <?= $sortDir === 'DESC' ? 'selected' : '' ?>>Z → A / High→Low</option>
            </select>
            <button type="submit" class="btn-hero text-nowrap" style="padding:7px 16px;font-size:.85rem">
              <i class="fas fa-filter"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </form>

  <?php if ($search !== ''): ?>
    <p class="text-muted mb-3" style="font-size:.87rem">
      <i class="fas fa-filter me-1"></i>
      <?= count($quizzes) ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
      — <a href="index.php?route=/front" style="color:#e63946">clear</a>
    </p>
  <?php endif; ?>

  <?php if (empty($quizzes)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-2">No quizzes found<?= $search ? ' for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>.</p>
      <?php if ($search): ?><a href="index.php?route=/front" style="color:#e63946">Clear search →</a><?php endif; ?>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($quizzes as $q): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="avatar"><i class="fas fa-question"></i></div>
              <div>
                <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($q['type']) ?></div>
                <div class="text-muted" style="font-size:.8rem">
                  <?= (int)$q['question_count'] ?> question<?= $q['question_count'] != 1 ? 's' : '' ?>
                </div>
              </div>
            </div>
            <p class="text-muted mb-3" style="font-size:.87rem;flex:1"><?= htmlspecialchars($q['description']) ?></p>
            <a href="index.php?route=/front/play&id=<?= $q['id'] ?>" class="btn-hero" style="padding:9px 20px;font-size:.85rem;">
              <i class="fas fa-play me-1"></i>Start Quiz
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
// Auto-submit on typing (400ms debounce)
(function () {
  var timer;
  document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { document.getElementById('filterForm').submit(); }, 400);
  });
})();
</script>

<?php require __DIR__ . '/footer.php'; ?>
