<?php
$pageTitle = 'Feedback';
$activeNav = 'feedback';
require __DIR__ . '/../layouts/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="fas fa-comment-dots me-2"></i>All Feedback</span>
    <span class="badge" style="background:rgba(255,255,255,.15);font-size:.78rem;padding:5px 12px">
      <?= count($feedbacks) ?> total
    </span>
  </div>
  <div class="card-body p-4">

    <?php if (empty($feedbacks)): ?>
      <div class="text-center py-5">
        <i class="fas fa-comment-slash fa-3x mb-3 text-muted"></i>
        <p class="text-muted">No feedback submitted yet.</p>
      </div>

    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Quiz</th>
              <th>User ID</th>
              <th>Feedback</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feedbacks as $f): ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem;width:40px"><?= $f['id'] ?></td>
              <td style="width:160px">
                <span class="badge" style="background:#e8f4fd;color:#1a56db;font-size:.78rem">
                  <?= htmlspecialchars($f['quiz_type']) ?>
                </span>
              </td>
              <td style="width:90px">
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar" style="width:28px;height:28px;font-size:.7rem">
                    <?= $f['userId'] ?>
                  </div>
                  <small class="text-muted">User <?= $f['userId'] ?></small>
                </div>
              </td>
              <td style="font-size:.87rem;color:#1d2b4f">
                <?= nl2br(htmlspecialchars($f['feedback'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
