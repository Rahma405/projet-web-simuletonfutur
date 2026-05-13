<?php
$pageTitle = 'Create Quiz';
$activeNav = 'create';
require __DIR__ . '/../layouts/header.php';
?>

<div class="card" style="max-width:560px">
  <div class="card-header"><span><i class="fas fa-plus-circle me-2"></i>Create Quiz — Step 1</span></div>
  <div class="card-body p-4">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="index.php?route=/quizzes/build-form" id="step1Form" novalidate>
      <div class="mb-3">
        <label class="form-label">Quiz Type / Category <span style="color:#e63946">*</span></label>
        <input type="text" name="type" class="form-control <?= !empty($errors) && empty($type) ? 'is-invalid' : '' ?>"
               placeholder="e.g. Science, History…" value="<?= htmlspecialchars($type ?? '') ?>">
        <div class="invalid-feedback" id="typeError"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Description <span style="color:#e63946">*</span></label>
        <textarea name="des" class="form-control" rows="3" placeholder="Describe this quiz…"><?= htmlspecialchars($des ?? '') ?></textarea>
        <div class="invalid-feedback" id="desError"></div>
      </div>
      <div class="mb-4">
        <label class="form-label">Number of Questions <span style="color:#e63946">*</span></label>
        <input type="number" name="nb" class="form-control" style="max-width:140px"
               min="1" max="50" placeholder="e.g. 5" value="<?= htmlspecialchars((string)($nb ?? '')) ?>">
        <div class="invalid-feedback" id="nbError"></div>
      </div>
      <button type="submit" class="btn-red" style="border-radius:9px;padding:10px 24px">
        <i class="fas fa-arrow-right me-1"></i>Continue to Questions
      </button>
    </form>

    <script>
    document.getElementById('step1Form').addEventListener('submit', function(e) {
      let valid = true;
      const type = this.querySelector('[name=type]').value.trim();
      const des  = this.querySelector('[name=des]').value.trim();
      const nb   = parseInt(this.querySelector('[name=nb]').value, 10);

      this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      document.getElementById('typeError').textContent = '';
      document.getElementById('desError').textContent  = '';
      document.getElementById('nbError').textContent   = '';

      if (!type) { this.querySelector('[name=type]').classList.add('is-invalid'); document.getElementById('typeError').textContent = 'Type is required.'; valid = false; }
      if (!des)  { this.querySelector('[name=des]').classList.add('is-invalid');  document.getElementById('desError').textContent  = 'Description is required.'; valid = false; }
      if (!nb || nb < 1) { this.querySelector('[name=nb]').classList.add('is-invalid'); document.getElementById('nbError').textContent = 'Enter at least 1.'; valid = false; }

      if (!valid) e.preventDefault();
    });
    </script>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
