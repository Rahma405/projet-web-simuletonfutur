<?php
$pageTitle = 'Edit Quizzes';
$activeNav = 'update';
require __DIR__ . '/../layouts/header.php';
?>

<div class="card">
  <div class="card-header"><span><i class="fas fa-edit me-2"></i>Select a Quiz to Edit</span></div>
  <div class="card-body p-4">

    <form method="GET" action="index.php" id="filterForm" class="mb-4">
      <input type="hidden" name="route" value="/quizzes/update-list">

      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label fw-semibold" style="font-size:.8rem"><i class="fas fa-search me-1"></i>Search</label>
          <div class="input-group">
            <input type="text" name="search" id="searchInput" class="form-control"
                   placeholder="Search by name or description…"
                   value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            <?php if ($search !== ''): ?>
              <a href="index.php?route=/quizzes/update-list" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold" style="font-size:.8rem"><i class="fas fa-sort me-1"></i>Sort by</label>
          <div class="d-flex gap-3">
            <?php foreach (['id' => '#', 'type' => 'Name', 'question_count' => 'Questions'] as $val => $lbl): ?>
              <div class="form-check">
                <input class="form-check-input sort-radio" type="radio"
                       name="sort" value="<?= $val ?>" id="usort_<?= $val ?>"
                       <?= $sortCol === $val ? 'checked' : '' ?>>
                <label class="form-check-label" for="usort_<?= $val ?>" style="font-size:.85rem;cursor:pointer"><?= $lbl ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:.8rem">Direction</label>
          <div class="d-flex gap-2">
            <select name="dir" class="form-select form-select-sm">
              <option value="ASC"  <?= $sortDir === 'ASC'  ? 'selected' : '' ?>>A → Z</option>
              <option value="DESC" <?= $sortDir === 'DESC' ? 'selected' : '' ?>>Z → A</option>
            </select>
            <button type="submit" class="btn-red text-nowrap" style="border-radius:9px;padding:6px 14px">
              <i class="fas fa-filter"></i>
            </button>
          </div>
        </div>
      </div>
    </form>

    <?php if (empty($quizzes)): ?>
      <div class="text-center py-5 text-muted">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>No quizzes found<?= $search ? ' for <strong>' . htmlspecialchars($search) . '</strong>' : '' ?>.</p>
        <?php if ($search): ?><a href="index.php?route=/quizzes/update-list" style="color:#e63946;font-size:.87rem">Clear →</a><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead><tr><th>#</th><th>Name</th><th>Description</th><th class="text-center">Questions</th><th class="text-center">Action</th></tr></thead>
          <tbody>
            <?php foreach ($quizzes as $q): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $q['id'] ?></td>
              <td><strong style="font-size:.86rem"><?= htmlspecialchars($q['type']) ?></strong></td>
              <td style="font-size:.83rem"><?= htmlspecialchars($q['description']) ?></td>
              <td class="text-center"><span class="badge" style="background:#e8f4fd;color:#1a6fa8;font-size:.75rem"><?= (int)$q['question_count'] ?> Q</span></td>
              <td class="text-center">
                <a href="index.php?route=/quizzes/edit&id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Edit</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($quizzes) ?> result(s)<?= $search ? ' — <strong>' . htmlspecialchars($search) . '</strong>' : '' ?></small>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  var timer;
  document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { document.getElementById('filterForm').submit(); }, 400);
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
