<?php
$pageTitle = 'All Quizzes';
$activeNav = 'list';
require __DIR__ . '/../layouts/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="fas fa-list me-2"></i>All Quizzes</span>
    <a href="index.php?route=/quizzes/create" class="btn-red"><i class="fas fa-plus me-1"></i>New Quiz</a>
  </div>
  <div class="card-body p-4">

    <!-- Search + Sort form -->
    <form method="GET" action="index.php" id="filterForm" class="mb-4">
      <input type="hidden" name="route" value="/quizzes">

      <div class="row g-3 align-items-end">

        <!-- Search input (auto-submit on typing) -->
        <div class="col-md-5">
          <label class="form-label fw-semibold" style="font-size:.8rem">
            <i class="fas fa-search me-1"></i>Search
          </label>
          <div class="input-group">
            <input type="text" name="search" id="searchInput" class="form-control"
                   placeholder="Search by name or description…"
                   value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            <?php if ($search !== ''): ?>
              <a href="index.php?route=/quizzes" class="btn btn-outline-secondary" title="Clear">
                <i class="fas fa-times"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Sort column (radio buttons) -->
        <div class="col-md-4">
          <label class="form-label fw-semibold" style="font-size:.8rem">
            <i class="fas fa-sort me-1"></i>Sort by
          </label>
          <div class="d-flex gap-3">
            <?php foreach (['id' => '#', 'type' => 'Name', 'question_count' => 'Questions'] as $val => $lbl): ?>
              <div class="form-check">
                <input class="form-check-input sort-radio" type="radio"
                       name="sort" value="<?= $val ?>" id="sort_<?= $val ?>"
                       <?= $sortCol === $val ? 'checked' : '' ?>>
                <label class="form-check-label" for="sort_<?= $val ?>"
                       style="font-size:.85rem;cursor:pointer"><?= $lbl ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Sort direction + Submit -->
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:.8rem">
            <i class="fas fa-arrow-down-up me-1"></i>Direction
          </label>
          <div class="d-flex gap-2">
            <select name="dir" class="form-select form-select-sm sort-dir">
              <option value="ASC"  <?= $sortDir === 'ASC'  ? 'selected' : '' ?>>A → Z / Low→High</option>
              <option value="DESC" <?= $sortDir === 'DESC' ? 'selected' : '' ?>>Z → A / High→Low</option>
            </select>
            <button type="submit" class="btn-red text-nowrap" style="border-radius:9px;padding:6px 14px">
              <i class="fas fa-filter"></i>
            </button>
          </div>
        </div>

      </div>
    </form>

    <?php if (empty($quizzes)): ?>
      <div class="text-center py-5">
        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
        <p class="text-muted mb-1">No quizzes found<?= $search ? ' for <strong>' . htmlspecialchars($search) . '</strong>' : '' ?>.</p>
        <?php if ($search): ?><a href="index.php?route=/quizzes" style="color:#e63946;font-size:.87rem">Clear search →</a><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover" id="quizTable">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Name</th>
              <th>Description</th>
              <th style="width:90px" class="text-center">Questions</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($quizzes as $q): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $q['id'] ?></td>
              <td><strong style="font-size:.86rem"><?= htmlspecialchars($q['type']) ?></strong></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($q['description']) ?></td>
              <td class="text-center">
                <span class="badge" style="background:#e8f4fd;color:#1a6fa8;font-size:.75rem">
                  <?= (int)$q['question_count'] ?> Q
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                  <a href="index.php?route=/quizzes/show&id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                  <a href="index.php?route=/quizzes/edit&id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                  <a href="index.php?route=/quizzes/delete&type=quiz&id=<?= $q['id'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Delete this quiz and all its questions?')" title="Delete">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted">
        <?= count($quizzes) ?> result(s)
        <?= $search ? '— filtered by <strong>' . htmlspecialchars($search) . '</strong>' : '' ?>
      </small>
    <?php endif; ?>
  </div>
</div>

<script>
// Auto-submit search after user stops typing (400ms debounce)
(function () {
  var timer;
  document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () {
      document.getElementById('filterForm').submit();
    }, 400);
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
