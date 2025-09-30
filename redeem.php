<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Redeem Rewards (Coming Soon)';
?>

<section class="redeem-section">
    <div class="container">
        <div class="section-header" style="text-align:center; margin-bottom:2rem;">
            <h1>Redeem Rewards</h1>
            <p>Convert your points into exciting rewards. Coming soon!</p>
        </div>

        <div class="redeem-grid">
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
            <div class="redeem-tier">
                <div class="tier-header">10000 pts</div>
                <div class="tier-body">Exclusive Chef Kit <span class="coming">Coming Soon</span></div>
            </div>
        </div>

        <div class="text-center" style="margin-top:2rem;">
            <a href="leaderboard.php" class="btn btn-outline">Back to Leaderboard</a>
        </div>
    </div>
</section>

<style>
.redeem-section { padding: 2rem 0; }
.redeem-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; }
.redeem-tier { background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.redeem-tier .tier-header { background:#eef2ff; color:#3730a3; font-weight:700; padding:.75rem 1rem; border-top-left-radius:12px; border-top-right-radius:12px; }
.redeem-tier .tier-body { padding:1rem; color:#374151; }
.redeem-tier .coming { margin-left:.5rem; background:#fff7ed; color:#c2410c; padding:.15rem .4rem; border-radius:6px; font-size:.8rem; font-weight:600; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


