<?php
$pageTitle   = 'My Results';
$sessionUser = $_SESSION['user'] ?? null;
require __DIR__ . '/header.php';
?>

<section class="container py-5">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h2 class="fw-bold mb-1" style="color:#1d2b4f">
        <i class="fas fa-history me-2" style="color:#e63946"></i>My Results
      </h2>
      <p class="text-muted mb-0" style="font-size:.9rem">
        All quizzes you have completed, <?= htmlspecialchars(($sessionUser['prenom'] ?? '') . ' ' . ($sessionUser['nom'] ?? '')) ?>
      </p>
    </div>
    <a href="index.php?route=/front" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i>Browse Quizzes
    </a>
  </div>

  <?php if (empty($results)): ?>
    <div class="card text-center p-5">
      <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
      <p class="text-muted mb-3">You haven't completed any quiz yet.</p>
      <a href="index.php?route=/front" class="btn-hero" style="padding:9px 24px;font-size:.88rem;display:inline-flex;width:fit-content;margin:0 auto">
        <i class="fas fa-play me-1"></i>Start your first quiz
      </a>
    </div>

  <?php else: ?>

    <!-- Stats row -->
    <?php
    $totalPassed = count($results);
    $avgScore    = $totalPassed > 0
        ? round(array_sum(array_map(fn($r) => $r['total'] > 0 ? ($r['score']/$r['total'])*100 : 0, $results)) / $totalPassed)
        : 0;
    $bestScore   = $totalPassed > 0
        ? max(array_map(fn($r) => $r['total'] > 0 ? round(($r['score']/$r['total'])*100) : 0, $results))
        : 0;
    ?>
    <div class="row g-3 mb-4">
      <div class="col-4">
        <div class="card p-3 text-center" style="border-left:4px solid #e63946">
          <div class="fw-bold" style="font-size:1.6rem;color:#e63946"><?= $totalPassed ?></div>
          <div class="text-muted" style="font-size:.78rem">Quizzes Passed</div>
        </div>
      </div>
      <div class="col-4">
        <div class="card p-3 text-center" style="border-left:4px solid #457b9d">
          <div class="fw-bold" style="font-size:1.6rem;color:#457b9d"><?= $avgScore ?>%</div>
          <div class="text-muted" style="font-size:.78rem">Average Score</div>
        </div>
      </div>
      <div class="col-4">
        <div class="card p-3 text-center" style="border-left:4px solid #16a34a">
          <div class="fw-bold" style="font-size:1.6rem;color:#16a34a"><?= $bestScore ?>%</div>
          <div class="text-muted" style="font-size:.78rem">Best Score</div>
        </div>
      </div>
    </div>

    <!-- Results table -->
    <div class="card">
      <div class="card-header">
        <span><i class="fas fa-list me-2"></i>History</span>
        <span class="badge" style="background:rgba(255,255,255,.15);font-size:.78rem;padding:5px 12px">
          <?= $totalPassed ?> result<?= $totalPassed !== 1 ? 's' : '' ?>
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Quiz</th>
                <th class="text-center">Score</th>
                <th class="text-center">Result</th>
                <th>Date</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $r):
                $pct = $r['total'] > 0 ? round(($r['score'] / $r['total']) * 100) : 0;
                if ($pct >= 70)      { $badge = ['label'=>'Pass',    'bg'=>'#d1fadf','color'=>'#16a34a']; }
                elseif ($pct >= 40)  { $badge = ['label'=>'Average', 'bg'=>'#fef3c7','color'=>'#d97706']; }
                else                 { $badge = ['label'=>'Fail',    'bg'=>'#fde8ea','color'=>'#e63946']; }
              ?>
              <tr>
                <td style="color:#8899bb;font-size:.78rem"><?= $r['id'] ?></td>
                <td>
                  <div class="fw-bold" style="font-size:.86rem;color:#1d2b4f">
                    <?= htmlspecialchars($r['quiz_type']) ?>
                  </div>
                  <div style="font-size:.75rem;color:#8899bb">
                    <?= htmlspecialchars(mb_strimwidth($r['quiz_description'], 0, 50, '…')) ?>
                  </div>
                </td>
                <td class="text-center">
                  <div class="fw-bold" style="font-size:.95rem;color:#1d2b4f"><?= $pct ?>%</div>
                  <div style="font-size:.75rem;color:#8899bb"><?= $r['score'] ?>/<?= $r['total'] ?></div>
                </td>
                <td class="text-center">
                  <span class="badge"
                        style="background:<?= $badge['bg'] ?>;color:<?= $badge['color'] ?>;font-size:.78rem;padding:4px 12px;border-radius:20px">
                    <?= $badge['label'] ?>
                  </span>
                </td>
                <td style="font-size:.82rem;color:#65748f">
                  <?= date('d/m/Y H:i', strtotime($r['passed_at'])) ?>
                </td>
                <td class="text-center">
                  <a href="index.php?route=/front/play&id=<?= $r['quiz_id'] ?? '' ?>"
                     class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-redo me-1"></i>Retry
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <?php endif; ?>
</section>

<?php require __DIR__ . '/footer.php'; ?>
