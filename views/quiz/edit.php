<?php
$pageTitle = 'Edit Quiz';
$activeNav = 'update';
require __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<form method="POST" action="index.php?route=/quizzes/update" id="editForm" novalidate>
  <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

  <div class="card mb-3">
    <div class="card-header"><i class="fas fa-info-circle me-2"></i>Quiz Details — ID #<?= $quiz['id'] ?></div>
    <div class="card-body p-4">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Type <span style="color:#e63946">*</span></label>
          <input type="text" name="type" id="editType" class="form-control" value="<?= htmlspecialchars($quiz['type']) ?>">
          <div class="invalid-feedback" id="typeError"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Description <span style="color:#e63946">*</span></label>
          <input type="text" name="description" id="editDesc" class="form-control" value="<?= htmlspecialchars($quiz['description']) ?>">
          <div class="invalid-feedback" id="desError"></div>
        </div>
      </div>
    </div>
  </div>

  <?php $n = 1; foreach ($questions as $qId => $q): ?>
    <div class="card mb-3" style="border:1px solid #dde3f0;box-shadow:none">
      <div class="card-body p-3">
        <p class="fw-semibold mb-2" style="color:#e63946;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Question <?= $n++ ?></p>
        <input type="hidden" name="questions[<?= $qId ?>][id]" value="<?= $qId ?>">
        <div class="mb-3">
          <input type="text" name="questions[<?= $qId ?>][text]" class="form-control" value="<?= htmlspecialchars($q['text']) ?>">
        </div>
        <div class="row g-2">
          <?php foreach ($q['answers'] as $ans): ?>
            <div class="col-md-6">
              <div class="input-group">
                <div class="input-group-text" style="background:#eef3fb;border-color:#dde3f0">
                  <input type="radio" name="correct_for_q_<?= $qId ?>" value="<?= $ans['id'] ?>" <?= $ans['is_correct'] ? 'checked' : '' ?>>
                </div>
                <input type="hidden" name="questions[<?= $qId ?>][answers][<?= $ans['id'] ?>][id]" value="<?= $ans['id'] ?>">
                <input type="text" name="questions[<?= $qId ?>][answers][<?= $ans['id'] ?>][text]"
                       class="form-control" value="<?= htmlspecialchars($ans['text']) ?>">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Select the radio button next to the correct answer.</small>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="d-flex gap-2 justify-content-end">
    <a href="index.php?route=/quizzes/update-list" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn-red" style="border-radius:9px;padding:10px 24px"><i class="fas fa-save me-1"></i>Save Changes</button>
  </div>
</form>

<script>
document.getElementById('editForm').addEventListener('submit', function(e) {
  let valid = true;
  const type = document.getElementById('editType').value.trim();
  const des  = document.getElementById('editDesc').value.trim();
  document.getElementById('typeError').textContent = '';
  document.getElementById('desError').textContent  = '';
  document.getElementById('editType').classList.remove('is-invalid');
  document.getElementById('editDesc').classList.remove('is-invalid');

  if (!type) { document.getElementById('editType').classList.add('is-invalid'); document.getElementById('typeError').textContent = 'Type is required.'; valid = false; }
  if (!des)  { document.getElementById('editDesc').classList.add('is-invalid'); document.getElementById('desError').textContent  = 'Description is required.'; valid = false; }
  if (!valid) e.preventDefault();
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
