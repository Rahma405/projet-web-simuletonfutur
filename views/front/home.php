<?php
$pageTitle   = 'Browse Quizzes';
$sessionUser = $_SESSION['user'] ?? null;
require __DIR__ . '/header.php';

function foSortUrl($col, $currentCol, $currentDir, $currentSearch) {
    $dir = ($col === $currentCol && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    return 'index.php?route=/front&sort=' . $col . '&dir=' . $dir . '&search=' . urlencode($currentSearch);
}
?>

<div class="<?= $sessionUser ? 'container-fluid py-4' : 'container py-5' ?>">
  <div class="<?= $sessionUser ? 'row g-4' : '' ?>">

  <?php if ($sessionUser): ?>
  <!-- ── Sidebar with quiz list for logged-in users ─────────────────────── -->
  <div class="col-lg-3 col-md-4">
    <div class="card" style="position:sticky;top:80px">

      <!-- User chip -->
      <div class="card-body pb-0">
        <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded"
             style="background:#f0f3fa;border:1px solid #dde3f0">
          <div class="avatar" style="width:40px;height:40px;font-size:.85rem;flex-shrink:0">
            <?= strtoupper(substr($sessionUser['prenom'],0,1).substr($sessionUser['nom'],0,1)) ?>
          </div>
          <div>
            <div class="fw-bold" style="font-size:.88rem;color:#1d2b4f">
              <?= htmlspecialchars($sessionUser['prenom'].' '.$sessionUser['nom']) ?>
            </div>
            <a href="index.php?route=/front/my-results"
               style="font-size:.75rem;color:#e63946;text-decoration:none;font-weight:600">
              <i class="fas fa-history me-1"></i>My Results
            </a>
          </div>
        </div>
      </div>

      <!-- Quiz list in sidebar -->
      <div class="card-header" style="font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em">
        <i class="fas fa-layer-group me-2"></i>Available Quizzes
      </div>
      <div class="list-group list-group-flush" style="max-height:420px;overflow-y:auto">
        <?php if (empty($quizzes)): ?>
          <div class="list-group-item text-muted text-center" style="font-size:.83rem">
            No quizzes available.
          </div>
        <?php else: ?>
          <?php foreach ($quizzes as $q): ?>
            <a href="index.php?route=/front/play&id=<?= $q['id'] ?>"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               style="font-size:.85rem;padding:12px 16px">
              <div>
                <div class="fw-semibold" style="color:#1d2b4f"><?= htmlspecialchars($q['type']) ?></div>
                <div style="font-size:.75rem;color:#8899bb">
                  <?= (int)$q['question_count'] ?> question<?= $q['question_count'] != 1 ? 's' : '' ?>
                </div>
              </div>
              <i class="fas fa-chevron-right" style="color:#e63946;font-size:.75rem"></i>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Main content ───────────────────────────────────────────────────── -->
  <div class="col-lg-9 col-md-8">
  <?php endif; ?>

    <!-- Page title -->
    <div class="mb-4">
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">
        <?= $sessionUser
          ? 'Welcome back, <span style="color:#e63946">'.htmlspecialchars($sessionUser['prenom']).'</span>! Pick a quiz.'
          : 'Available Quizzes' ?>
      </h2>
      <p class="text-muted mb-0" style="font-size:.9rem">
        <?= $sessionUser
          ? 'Your results are saved automatically after each quiz.'
          : 'Log in to save your results.' ?>
      </p>
    </div>

    <!-- Search + Sort -->
    <form method="GET" action="index.php" id="filterForm" class="mb-4">
      <input type="hidden" name="route" value="/front">
      <div class="card p-3">
        <div class="row g-3 align-items-end">
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
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;color:#1d2b4f">
              <i class="fas fa-sort me-1" style="color:#e63946"></i>Sort by
            </label>
            <div class="d-flex gap-3">
              <?php foreach (['id'=>'#','type'=>'Name','question_count'=>'Questions'] as $val => $lbl): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sort" value="<?= $val ?>"
                         id="fsort_<?= $val ?>" style="accent-color:#e63946"
                         <?= $sortCol === $val ? 'checked' : '' ?>>
                  <label class="form-check-label" for="fsort_<?= $val ?>"
                         style="font-size:.86rem;cursor:pointer"><?= $lbl ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;color:#1d2b4f">Direction</label>
            <div class="d-flex gap-2">
              <select name="dir" class="form-select form-select-sm">
                <option value="ASC"  <?= $sortDir==='ASC'  ? 'selected':'' ?>>A → Z</option>
                <option value="DESC" <?= $sortDir==='DESC' ? 'selected':'' ?>>Z → A</option>
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
        <p class="text-muted mb-2">No quizzes found<?= $search ? ' for "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?>.</p>
        <?php if ($search): ?><a href="index.php?route=/front" style="color:#e63946">Clear →</a><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($quizzes as $q): ?>
          <div class="col-md-6 col-lg-<?= $sessionUser ? '6' : '4' ?>">
            <div class="card h-100 p-4">
              <div class="d-flex align-items-center gap-2 mb-3">
                <div class="avatar"><i class="fas fa-question"></i></div>
                <div>
                  <div class="fw-bold" style="color:#1d2b4f"><?= htmlspecialchars($q['type']) ?></div>
                  <div class="text-muted" style="font-size:.8rem">
                    <?= (int)$q['question_count'] ?> question<?= $q['question_count'] != 1 ? 's' : '' ?>
                  </div>
                </div>
                <?php if ($sessionUser): ?>
                  <span class="ms-auto badge" style="background:#d1fadf;color:#16a34a;font-size:.72rem">
                    <i class="fas fa-save me-1"></i>Auto-save
                  </span>
                <?php endif; ?>
              </div>
              <p class="text-muted mb-3" style="font-size:.87rem;flex:1"><?= htmlspecialchars($q['description']) ?></p>
              <?php if ($sessionUser): ?>
                <a href="index.php?route=/front/play&id=<?= $q['id'] ?>" class="btn-hero"
                   style="padding:9px 20px;font-size:.85rem;">
                  <i class="fas fa-play me-1"></i>Start Quiz
                </a>
              <?php else: ?>
                <a href="index.php?route=/login" class="btn-hero"
                   style="padding:9px 20px;font-size:.85rem;background:linear-gradient(135deg,#457b9d,#1d3557)">
                  <i class="fas fa-sign-in-alt me-1"></i>Log in to play
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php if ($sessionUser): ?>
  </div><!-- /.col main -->
  </div><!-- /.row -->
  <?php endif; ?>
</div>

<script>
(function() {
  var timer;
  var input = document.getElementById('searchInput');
  if (input) {
    input.addEventListener('input', function() {
      clearTimeout(timer);
      timer = setTimeout(function() {
        document.getElementById('filterForm').submit();
      }, 400);
    });
  }
})();
</script>

<?php require __DIR__ . '/footer.php'; ?>
