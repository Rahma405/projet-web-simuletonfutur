<?php
$pageTitle = 'Quiz Results';
$activeNav = 'results';
require __DIR__ . '/../layouts/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="fas fa-chart-bar me-2"></i>All Quiz Results</span>
    <span class="badge" style="background:rgba(255,255,255,.15);font-size:.78rem;padding:5px 12px">
      <?= count($results) ?> total
    </span>
  </div>
  <div class="card-body p-4">

    <?php if (empty($results)): ?>
      <div class="text-center py-5">
        <i class="fas fa-chart-bar fa-3x mb-3 text-muted"></i>
        <p class="text-muted">No quiz results yet.</p>
      </div>

    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Quiz</th>
              <th class="text-center">Score</th>
              <th class="text-center">%</th>
              <th class="text-center">Result</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r):
              $pct = $r['total'] > 0 ? round(($r['score'] / $r['total']) * 100) : 0;
              if ($pct >= 70)     { $badge = ['label'=>'Pass',    'bg'=>'#d1fadf','color'=>'#16a34a']; }
              elseif ($pct >= 40) { $badge = ['label'=>'Average', 'bg'=>'#fef3c7','color'=>'#d97706']; }
              else                { $badge = ['label'=>'Fail',    'bg'=>'#fde8ea','color'=>'#e63946']; }
            ?>
            <tr>
              <td style="color:#8899bb;font-size:.78rem"><?= $r['id'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar" style="width:30px;height:30px;font-size:.72rem">
                    <?= strtoupper(substr($r['prenom'],0,1).substr($r['nom'],0,1)) ?>
                  </div>
                  <div>
                    <div style="font-size:.85rem;font-weight:600">
                      <?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?>
                    </div>
                    <div style="font-size:.75rem;color:#8899bb">
                      <?= htmlspecialchars($r['email']) ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge" style="background:#e8f4fd;color:#1a56db;font-size:.78rem">
                  <?= htmlspecialchars($r['quiz_type']) ?>
                </span>
              </td>
              <td class="text-center fw-bold" style="font-size:.88rem">
                <?= $r['score'] ?>/<?= $r['total'] ?>
              </td>
              <td class="text-center fw-bold" style="color:#1d2b4f">
                <?= $pct ?>%
              </td>
              <td class="text-center">
                <span class="badge" style="background:<?= $badge['bg'] ?>;color:<?= $badge['color'] ?>;font-size:.75rem;padding:4px 12px;border-radius:20px">
                  <?= $badge['label'] ?>
                </span>
              </td>
              <td style="font-size:.82rem;color:#65748f">
                <?= date('d/m/Y H:i', strtotime($r['passed_at'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <small class="text-muted"><?= count($results) ?> result(s)</small>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
