<?php
$pageTitle = 'Dashboard';
$activeNav = 'home';
require __DIR__ . '/header.php';
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card red">
      <div><div class="val" id="statTotal">—</div><div class="lbl">Total Quizzes</div></div>
      <i class="fas fa-layer-group"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card blue">
      <div><div class="val" id="statQ">—</div><div class="lbl">Questions</div></div>
      <i class="fas fa-question-circle"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card orange">
      <div><div class="val" id="statA">—</div><div class="lbl">Answers</div></div>
      <i class="fas fa-check-square"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card green">
      <div><div class="val">MVC</div><div class="lbl">Architecture</div></div>
      <i class="fas fa-code"></i>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="row g-3">
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 p-4 text-center" style="cursor:pointer" onclick="location.href='index.php?route=/quizzes/create'">
      <i class="fas fa-plus-circle fa-2x mb-3" style="color:#e63946"></i>
      <h6 class="fw-bold mb-1" style="color:#1d2b4f">Create Quiz</h6>
      <small class="text-muted">Build a new quiz with questions and answers</small>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 p-4 text-center" style="cursor:pointer" onclick="location.href='index.php?route=/quizzes'">
      <i class="fas fa-list fa-2x mb-3" style="color:#457b9d"></i>
      <h6 class="fw-bold mb-1" style="color:#1d2b4f">All Quizzes</h6>
      <small class="text-muted">Browse and preview existing quizzes</small>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 p-4 text-center" style="cursor:pointer" onclick="location.href='index.php?route=/quizzes/update-list'">
      <i class="fas fa-edit fa-2x mb-3" style="color:#f4a261"></i>
      <h6 class="fw-bold mb-1" style="color:#1d2b4f">Edit Quizzes</h6>
      <small class="text-muted">Modify questions, answers and correct choices</small>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100 p-4 text-center" style="cursor:pointer" onclick="location.href='index.php?route=/quizzes/delete-list'">
      <i class="fas fa-trash fa-2x mb-3" style="color:#2a9d8f"></i>
      <h6 class="fw-bold mb-1" style="color:#1d2b4f">Delete Quizzes</h6>
      <small class="text-muted">Remove quizzes or individual questions</small>
    </div>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
