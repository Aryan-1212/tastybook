<?php
/**
 * Leaderboard - Top 5 Chefs of the Month
 */

require_once __DIR__ . '/includes/header.php';

$db = new Database();

// Determine current month window
$monthStart = date('Y-m-01 00:00:00');
$monthEnd = date('Y-m-t 23:59:59');

// Aggregate points from ledger within month
$stmt = $db->prepare("SELECT u.id, u.first_name, u.last_name, u.username, u.profile_image, COALESCE(SUM(pt.points),0) as month_points, u.points_total FROM users u LEFT JOIN points_transactions pt ON pt.user_id = u.id AND pt.created_at BETWEEN ? AND ? GROUP BY u.id ORDER BY month_points DESC, u.points_total DESC LIMIT 5");
$stmt->execute([$monthStart, $monthEnd]);
$leaders = $stmt->fetchAll();

$pageTitle = 'Leaderboard';
?>

<section class="leaderboard-section">
    <div class="container">
        <div class="section-header">
            <h1>Top 5 Chefs of the Month</h1>
            <p>Based on points earned in <?php echo date('F Y'); ?></p>
            <div>
                <a href="redeem.php" class="btn btn-outline">Redeem Rewards (Coming Soon)</a>
            </div>
        </div>

        <div class="redeem-coming-soon">
            <div class="redeem-card">
                <div class="redeem-icon"><i class="fas fa-gift"></i></div>
                <div class="redeem-content">
                    <h3>Redeem Coming Soon</h3>
                    <p>Collect points and soon redeem them for rewards!</p>
                    <ul>
                        <li><strong>1000 points</strong> = voucher or cash equivalent</li>
                        <li><strong>2500 points</strong> = premium rewards</li>
                        <li><strong>5000 points</strong> = exclusive chef goodies</li>
                    </ul>
                    <span class="badge-coming">Coming Soon</span>
                </div>
            </div>
        </div>

        <?php if (!empty($leaders)): ?>
            <div class="leaderboard-vertical">
                <?php foreach ($leaders as $index => $user): ?>
                    <?php $badge = getBadgeForPoints($user['points_total']); ?>
                    <div class="leader-card <?php echo $index === 0 ? 'leader-first' : ''; ?>">
                        <div class="leader-rank">#<?php echo $index + 1; ?></div>
                        <div class="leader-avatar">
                            <img src="<?php echo $user['profile_image'] ? '/TastyBook/public/uploads/' . $user['profile_image'] : 'https://via.placeholder.com/100x100?text=' . substr($user['first_name'], 0, 1); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>">
                        </div>
                        <div class="leader-info">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <div class="leader-points"><?php echo (int)$user['month_points']; ?> pts this month</div>
                            <div class="leader-badge"><i class="fas fa-medal"></i> <?php echo htmlspecialchars($badge); ?> (Total: <?php echo (int)$user['points_total']; ?>)</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="redeem-coming-soon-cards">
                <div class="redeem-tier">
                    <div class="tier-header">1000 pts</div>
                    <div class="tier-body">Voucher worth $10 <span class="coming">Coming Soon</span></div>
                </div>
                <div class="redeem-tier">
                    <div class="tier-header">2500 pts</div>
                    <div class="tier-body">Voucher worth $30 <span class="coming">Coming Soon</span></div>
                </div>
                <div class="redeem-tier">
                    <div class="tier-header">5000 pts</div>
                    <div class="tier-body">Cashout or Premium Bundle <span class="coming">Coming Soon</span></div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-trophy"></i>
                <h3>No leaderboard data yet</h3>
                <p>As users earn points, the leaderboard will update automatically.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.leaderboard-section { padding: 2rem 0; }
.leaderboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; }
.leaderboard-vertical { display: flex; flex-direction: column; gap: 1rem; max-width: 720px; margin: 0 auto; }
.leader-card { background: #fff; border-radius: 12px; padding: 1.25rem; position: relative; box-shadow: 0 2px 10px rgba(0,0,0,.06); display:flex; align-items:center; gap:1rem; }
.leader-first { border: 2px solid #fbbf24; background: #fffbea; }
.leader-rank { position: absolute; top: .5rem; right: .75rem; font-weight: 700; color:#667eea; }
.leader-avatar img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; }
.leader-info h3 { margin: 0; }
.leader-points { font-weight: 600; color:#111827; }
.leader-badge { color:#6b7280; margin-top:.25rem; }
.redeem-coming-soon { margin: 1.5rem 0 2rem; }
.redeem-card { display:flex; gap:1rem; align-items: center; background:#f8fafc; border:1px dashed #cbd5e1; padding:1rem; border-radius:12px; }
.redeem-icon { width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#eef2ff; color:#4f46e5; font-size:1.25rem; }
.redeem-content h3 { margin:0 0 .25rem; }
.redeem-content ul { margin:.5rem 0; padding-left:1.25rem; }
.badge-coming { display:inline-block; margin-top:.25rem; padding:.25rem .5rem; background:#fde68a; color:#92400e; border-radius:8px; font-size:.85rem; font-weight:600; }
.redeem-coming-soon-cards { margin-top:1rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; }
.redeem-tier { background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.redeem-tier .tier-header { background:#eef2ff; color:#3730a3; font-weight:700; padding:.75rem 1rem; border-top-left-radius:12px; border-top-right-radius:12px; }
.redeem-tier .tier-body { padding:1rem; color:#374151; }
.redeem-tier .coming { margin-left:.5rem; background:#fff7ed; color:#c2410c; padding:.15rem .4rem; border-radius:6px; font-size:.8rem; font-weight:600; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


