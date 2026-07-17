<?php
    include "home.php";

    // Fetch user's Buy Requests
    $buy_deals = mysqli_query($conn, "
        SELECT br.*, c.name AS car_name, c.model, c.color, c.number_plate,
               u.name AS seller_name, u.phone_no AS seller_phone, u.email AS seller_email,
               cl.price
        FROM buy_requests br
        JOIN car_listings cl ON br.listing_id = cl.listing_id
        JOIN cars c          ON cl.car_id = c.car_id
        JOIN users u         ON cl.owner_id = u.user_id
        WHERE br.buyer_id = $user_id
        ORDER BY br.requested_at DESC
    ");

    // Fetch user's Rent Requests
    $rent_deals = mysqli_query($conn, "
        SELECT rr.*, c.name AS car_name, c.model, c.color, c.number_plate,
               u.name AS owner_name, u.phone_no AS owner_phone, u.email AS owner_email,
               cl.price_per_day
        FROM rent_requests rr
        JOIN car_listings cl ON rr.listing_id = cl.listing_id
        JOIN cars c          ON cl.car_id = c.car_id
        JOIN users u         ON cl.owner_id = u.user_id
        WHERE rr.renter_id = $user_id
        ORDER BY rr.requested_at DESC
    ");

    // Pre-collect rows and stats
    $buy_rows = []; $rent_rows = [];
    $buy_pending = $buy_approved = $buy_rejected = 0;
    $rent_pending = $rent_approved = $rent_rejected = 0;

    while ($r = $buy_deals->fetch_assoc()) {
        $buy_rows[] = $r;
        if ($r['status'] == 'pending')        $buy_pending++;
        elseif ($r['status'] == 'approved')   $buy_approved++;
        else                                   $buy_rejected++;
    }
    while ($r = $rent_deals->fetch_assoc()) {
        $rent_rows[] = $r;
        if ($r['status'] == 'pending')        $rent_pending++;
        elseif ($r['status'] == 'approved')   $rent_approved++;
        else                                   $rent_rejected++;
    }
?>

<section class="mainpart">

    <h2 class="page-title">🤝 My Deals</h2>
    <p class="page-sub">Track the status of all your buy and rent requests.</p>

    <!-- Summary Stats -->
    <div class="stats-row">
        <div class="stat-card stat-pending">
            <span class="stat-num"><?= $buy_pending + $rent_pending ?></span>
            <span class="stat-label">Pending</span>
        </div>
        <div class="stat-card stat-approved">
            <span class="stat-num"><?= $buy_approved + $rent_approved ?></span>
            <span class="stat-label">Approved</span>
        </div>
        <div class="stat-card stat-rejected">
            <span class="stat-num"><?= $buy_rejected + $rent_rejected ?></span>
            <span class="stat-label">Rejected</span>
        </div>
        <div class="stat-card stat-total">
            <span class="stat-num"><?= count($buy_rows) + count($rent_rows) ?></span>
            <span class="stat-label">Total</span>
        </div>
    </div>

    <!-- Buy Deals -->
    <h3 class="section-title">🏷️ My Buy Requests</h3>

    <?php if (count($buy_rows) == 0): ?>
        <div class="alert info">No buy requests yet. <a href="buy_cars.php">Browse cars for sale →</a></div>
    <?php else: ?>
    <div class="deals-grid">
        <?php foreach ($buy_rows as $deal): ?>
        <div class="deal-card deal-<?= $deal['status'] ?>">
            <div class="deal-header">
                <div>
                    <h4><?= $deal['car_name'] ?></h4>
                    <small><?= $deal['model'] ?> · <?= $deal['color'] ?> · <?= $deal['number_plate'] ?></small>
                </div>
                <span class="badge badge-<?= $deal['status'] ?>"><?= ucfirst($deal['status']) ?></span>
            </div>
            <div class="deal-body">
                <div class="deal-row"><span>💰 Sale Price</span><strong>₹<?= number_format($deal['price']) ?></strong></div>
                <div class="deal-row"><span>👤 Seller</span><span><?= $deal['seller_name'] ?></span></div>
                <div class="deal-row"><span>📞 Phone</span><span><?= $deal['seller_phone'] ?></span></div>
                <div class="deal-row"><span>📧 Email</span><span><?= $deal['seller_email'] ?></span></div>
                <div class="deal-row"><span>📅 Requested</span><span><?= date('d M Y', strtotime($deal['requested_at'])) ?></span></div>
            </div>
            <?php if ($deal['status'] == 'approved'): ?>
                <div class="deal-msg msg-approved">✅ Approved! Contact the seller to complete your purchase.</div>
            <?php elseif ($deal['status'] == 'rejected'): ?>
                <div class="deal-msg msg-rejected">❌ Not approved. <a href="buy_cars.php">Browse other cars →</a></div>
            <?php else: ?>
                <div class="deal-msg msg-pending">⏳ Waiting for seller approval...</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Rent Deals -->
    <h3 class="section-title" style="margin-top:40px;">🔑 My Rent Requests</h3>

    <?php if (count($rent_rows) == 0): ?>
        <div class="alert info">No rent requests yet. <a href="rent_cars.php">Browse cars for rent →</a></div>
    <?php else: ?>
    <div class="deals-grid">
        <?php foreach ($rent_rows as $deal):
            $days  = (strtotime($deal['end_date']) - strtotime($deal['start_date'])) / 86400;
            $total = $days * $deal['price_per_day'];
        ?>
        <div class="deal-card deal-<?= $deal['status'] ?>">
            <div class="deal-header">
                <div>
                    <h4><?= $deal['car_name'] ?></h4>
                    <small><?= $deal['model'] ?> · <?= $deal['color'] ?> · <?= $deal['number_plate'] ?></small>
                </div>
                <span class="badge badge-<?= $deal['status'] ?>"><?= ucfirst($deal['status']) ?></span>
            </div>
            <div class="deal-body">
                <div class="deal-row"><span>📅 From</span><strong><?= date('d M Y', strtotime($deal['start_date'])) ?></strong></div>
                <div class="deal-row"><span>📅 To</span><strong><?= date('d M Y', strtotime($deal['end_date'])) ?></strong></div>
                <div class="deal-row"><span>🗓️ Days</span><span><?= $days ?> day(s)</span></div>
                <div class="deal-row"><span>💰 Rate</span><span>₹<?= number_format($deal['price_per_day']) ?>/day</span></div>
                <div class="deal-row"><span>💵 Total</span><strong>₹<?= number_format($total) ?></strong></div>
                <div class="deal-row"><span>👤 Owner</span><span><?= $deal['owner_name'] ?></span></div>
                <div class="deal-row"><span>📞 Phone</span><span><?= $deal['owner_phone'] ?></span></div>
                <div class="deal-row"><span>📱 Your Contact</span><span><?= $deal['contact_info'] ?></span></div>
            </div>
            <?php if ($deal['status'] == 'approved'): ?>
                <div class="deal-msg msg-approved">✅ Approved! Contact the owner to pick up the car.</div>
            <?php elseif ($deal['status'] == 'rejected'): ?>
                <div class="deal-msg msg-rejected">❌ Not approved. <a href="rent_cars.php">Browse other cars →</a></div>
            <?php else: ?>
                <div class="deal-msg msg-pending">⏳ Waiting for owner approval...</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>

<style>
.page-title { font-size:1.8rem; color:#993333; margin-bottom:6px; font-weight:700; }
.page-sub   { color:#777; font-size:0.9rem; margin-bottom:22px; text-transform:none; }
.section-title { font-size:1.2rem; color:#444; font-weight:700; margin-bottom:14px; padding-bottom:8px; border-bottom:2px solid #f0f0f0; }
.alert { padding:13px 18px; border-radius:10px; margin-bottom:20px; font-weight:600; font-size:0.92rem; }
.alert.info { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
.alert a { color:#0c5460; font-weight:bold; }

.stats-row { display:flex; gap:14px; margin-bottom:30px; flex-wrap:wrap; }
.stat-card { flex:1; min-width:100px; border-radius:14px; padding:18px 20px; display:flex; flex-direction:column; align-items:center; gap:4px; box-shadow:0 2px 10px rgba(0,0,0,0.07); }
.stat-num   { font-size:2rem; font-weight:800; line-height:1; }
.stat-label { font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; opacity:0.8; }
.stat-pending  { background:#fff8e1; color:#856404; border:1px solid #ffe082; }
.stat-approved { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.stat-rejected { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.stat-total    { background:#e8eaf6; color:#283593; border:1px solid #c5cae9; }

.deals-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; margin-bottom:20px; }
.deal-card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 3px 14px rgba(0,0,0,0.08); border:1px solid #eee; display:flex; flex-direction:column; transition:transform 0.2s; }
.deal-card:hover { transform:translateY(-3px); }
.deal-pending  { border-top:4px solid #ffc107; }
.deal-approved { border-top:4px solid #28a745; }
.deal-rejected { border-top:4px solid #dc3545; }

.deal-header { display:flex; justify-content:space-between; align-items:flex-start; padding:16px 18px 10px; gap:10px; }
.deal-header h4 { font-size:1rem; color:#222; font-weight:700; margin-bottom:3px; text-transform:capitalize; }
.deal-header small { color:#888; font-size:0.78rem; text-transform:none; }

.badge { padding:4px 12px; border-radius:20px; font-size:0.72rem; font-weight:700; text-transform:uppercase; white-space:nowrap; }
.badge-pending  { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.badge-approved { background:#d4edda; color:#155724; border:1px solid #28a745; }
.badge-rejected { background:#f8d7da; color:#721c24; border:1px solid #dc3545; }

.deal-body { padding:4px 18px 14px; display:flex; flex-direction:column; gap:8px; }
.deal-row { display:flex; justify-content:space-between; font-size:0.84rem; color:#555; border-bottom:1px dashed #f5f5f5; padding-bottom:6px; text-transform:none; }
.deal-row span:first-child { color:#888; }

.deal-msg { margin:0 18px 16px; padding:10px 14px; border-radius:8px; font-size:0.83rem; font-weight:600; text-transform:none; }
.msg-approved { background:#d4edda; color:#155724; }
.msg-rejected { background:#f8d7da; color:#721c24; }
.msg-rejected a { color:#721c24; }
.msg-pending  { background:#fff8e1; color:#856404; }
</style>