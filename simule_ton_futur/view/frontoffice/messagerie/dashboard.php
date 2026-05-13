<?php
require_once 'header_messagerie.php';
require_once __DIR__ . '/../../../controller/StatsC.php';

$statsC = new StatsC();
$stats  = $statsC->getStats($userId);
?>

<div class="msg-page">
    <div class="msg-page-header"><h1>Dashboard</h1></div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-number"><?= $stats['msg_envoyes'] ?></div><div class="stat-label">Messages envoyés</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['msg_recus'] ?></div><div class="stat-label">Messages reçus</div></div>
        <div class="stat-card stat-card-accent"><div class="stat-number"><?= $stats['msg_non_lus'] ?></div><div class="stat-label">Non lus</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['nb_conversations'] ?></div><div class="stat-label">Conversations</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['nb_bloques'] ?></div><div class="stat-label">Bloqués</div></div>
    </div>

    <div class="stats-section">
        <h2>Activité des 7 derniers jours</h2>
        <?php if (empty($stats['messages_par_jour'])): ?>
            <p class="msg-info">Aucune activité récente.</p>
        <?php else: ?>
            <?php $maxMsg = max(array_column($stats['messages_par_jour'], 'nb')); ?>
            <div class="chart-bars">
                <?php foreach ($stats['messages_par_jour'] as $jour): ?>
                    <?php $pct = $maxMsg > 0 ? ($jour['nb'] / $maxMsg) * 100 : 0; ?>
                    <div class="chart-bar-wrap">
                        <div class="chart-bar-value"><?= $jour['nb'] ?></div>
                        <div class="chart-bar" style="height: <?= max($pct, 5) ?>%"></div>
                        <div class="chart-bar-label"><?= date('d/m', strtotime($jour['jour'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="stats-row">
        <div class="stats-section stats-half">
            <h2>Top contacts</h2>
            <?php if (empty($stats['top_contacts'])): ?><p class="msg-info">Aucun contact.</p>
            <?php else: ?>
                <ul class="stats-list">
                    <?php foreach ($stats['top_contacts'] as $i => $contact): ?>
                        <li class="stats-list-item">
                            <span class="stats-rank">#<?= $i + 1 ?></span>
                            <span class="stats-contact-name"><?= htmlspecialchars($contact['pseudo']) ?></span>
                            <span class="stats-contact-count"><?= $contact['nb_messages'] ?> msg</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="stats-section stats-half">
            <h2>Réactions reçues</h2>
            <?php if (empty($stats['reactions_recues'])): ?><p class="msg-info">Aucune réaction pour le moment.</p>
            <?php else: ?>
                <div class="stats-reactions">
                    <?php foreach ($stats['reactions_recues'] as $r): ?>
                        <div class="stats-reaction-item">
                            <span class="stats-reaction-emoji"><?= $r['emoji'] ?></span>
                            <span class="stats-reaction-count"><?= $r['nb'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
