<?php
$pageTitle = 'Delete Quizzes';
$activeNav = 'delete';
require __DIR__ . '/../layouts/header.php';
?>

<?php if (empty($quizzes)): ?>
  <div class="card"><div class="card-body text-center py-5 text-muted"><i class="fas fa-layer-group fa-3x mb-3"></i><p>No quizzes found.</p></div></div>
<?php else: ?>
  <?php foreach ($quizzes as $quiz): ?>
    <div class="card mb-3">
      <div class="card-header" style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f)">
        <span><i class="fas fa-layer-group me-2"></i>#<?= $quiz['id'] ?> — <?= htmlspecialchars($quiz['type']) ?></span>
        <a href="index.php?route=/quizzes/delete&type=quiz&id=<?= $quiz['id'] ?>"
           class="btn btn-sm btn-danger btn-delete"
           onclick="return confirm('WARNING: Delete the entire quiz and all its questions?')">
          <i class="fas fa-trash me-1"></i>Delete Quiz
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($quiz['questions'])): ?>
          <p class="text-muted p-3 mb-0" style="font-size:.84rem"><em>No questions in this quiz yet.</em></p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($quiz['questions'] as $q): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center" style="font-size:.85rem">
                <?= htmlspecialchars($q['text']) ?>
                <a href="index.php?route=/quizzes/delete&type=question&id=<?= $q['id'] ?>"
                   class="btn btn-sm btn-outline-danger btn-delete"
                   onclick="return confirm('Delete this question only?')">
                  <i class="fas fa-times me-1"></i>Delete
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
