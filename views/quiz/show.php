<?php
$pageTitle = 'View Quiz';
$activeNav = 'list';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="fw-bold mb-0" style="color:#1d2b4f"><?= htmlspecialchars($quiz['type']) ?></h5>
    <small class="text-muted"><?= htmlspecialchars($quiz['description']) ?></small>
  </div>
  <div class="d-flex gap-2">
    <a href="index.php?route=/quizzes/edit&id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Edit</a>
    <a href="index.php?route=/quizzes" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
</div>

<?php if (empty($questions)): ?>
  <div class="card"><div class="card-body text-center py-5 text-muted">No questions added yet.</div></div>
<?php else: ?>
  <?php $n = 1; foreach ($questions as $q): ?>
    <div class="card mb-3">
      <div class="card-body p-4">
        <p class="fw-semibold mb-1" style="font-size:.75rem;color:#e63946;text-transform:uppercase;letter-spacing:.06em">Question <?= $n++ ?></p>
        <p class="fw-bold mb-3" style="color:#1d2b4f"><?= htmlspecialchars($q['text']) ?></p>
        <div class="row g-2">
          <?php foreach ($q['answers'] as $ans): ?>
            <div class="col-md-6">
              <div class="p-2 rounded" style="background:<?= $ans['is_correct'] ? '#d1fadf' : '#f8f9fc' ?>;border:1px solid <?= $ans['is_correct'] ? '#86efac' : '#dde3f0' ?>">
                <?php if ($ans['is_correct']): ?><i class="fas fa-check-circle me-1" style="color:#16a34a"></i><?php endif; ?>
                <span style="font-size:.86rem"><?= htmlspecialchars($ans['text']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
