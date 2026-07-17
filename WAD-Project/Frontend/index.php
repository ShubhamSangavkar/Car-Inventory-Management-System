<?php
    include "home.php";

    // Stats
    $total_cars     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM cars"))['c'];
    $cars_for_sale  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM car_listings WHERE listing_type='sale' AND availability_status='available'"))['c'];
    $cars_for_rent  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM car_listings WHERE listing_type='rent' AND availability_status='available'"))['c'];
    $my_cars        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM cars WHERE owner_id=$user_id"))['c'];

    // Pending requests on user's listings
    $pending_buy    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM buy_requests br JOIN car_listings cl ON br.listing_id=cl.listing_id WHERE cl.owner_id=$user_id AND br.status='pending'"))['c'];
    $pending_rent   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM rent_requests rr JOIN car_listings cl ON rr.listing_id=cl.listing_id WHERE cl.owner_id=$user_id AND rr.status='pending'"))['c'];
    $total_pending  = $pending_buy + $pending_rent;

    // My active deals (approved)
    $my_approved    = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT (SELECT COUNT(*) FROM buy_requests  WHERE buyer_id=$user_id  AND status='approved') +
               (SELECT COUNT(*) FROM rent_requests WHERE renter_id=$user_id AND status='approved') AS c
    "))['c'];

    // Latest 6 cars for sale
    $latest_sale = mysqli_query($conn, "
        SELECT cl.*, c.name, c.model, c.color, c.capacity, c.number_plate,
               u.name AS seller_name
        FROM car_listings cl
        JOIN cars c  ON cl.car_id  = c.car_id
        JOIN users u ON cl.owner_id = u.user_id
        WHERE cl.listing_type = 'sale' AND cl.availability_status = 'available'
        ORDER BY cl.created_at DESC LIMIT 6
    ");

    // Latest 6 cars for rent
    $latest_rent = mysqli_query($conn, "
        SELECT cl.*, c.name, c.model, c.color, c.capacity, c.number_plate,
               u.name AS owner_name
        FROM car_listings cl
        JOIN cars c  ON cl.car_id  = c.car_id
        JOIN users u ON cl.owner_id = u.user_id
        WHERE cl.listing_type = 'rent' AND cl.availability_status = 'available'
        ORDER BY cl.created_at DESC LIMIT 6
    ");
?>

<section class="mainpart">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <p class="welcome-greeting">Good <?= (date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening')) ?> 👋</p>
            <h1>Welcome back, <span><?= htmlspecialchars($_SESSION['username']) ?></span></h1>
            <p class="welcome-sub">Here's what's happening on CarVista today</p>
        </div>
        <div class="welcome-actions">
            <a href="addcar.php"    class="wa-btn wa-primary">+ Add Car</a>
            <a href="buy_cars.php"  class="wa-btn wa-outline">Browse Sale</a>
            <a href="rent_cars.php" class="wa-btn wa-outline">Browse Rent</a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <a href="buy_cars.php" class="stat-box stat-red">
            <div class="stat-icon">🏷️</div>
            <div class="stat-info">
                <span class="stat-num"><?= $cars_for_sale ?></span>
                <span class="stat-label">Cars for Sale</span>
            </div>
        </a>
        <a href="rent_cars.php" class="stat-box stat-green">
            <div class="stat-icon">🔑</div>
            <div class="stat-info">
                <span class="stat-num"><?= $cars_for_rent ?></span>
                <span class="stat-label">Cars for Rent</span>
            </div>
        </a>
        <a href="mycar.php" class="stat-box stat-blue">
            <div class="stat-icon">🚗</div>
            <div class="stat-info">
                <span class="stat-num"><?= $my_cars ?></span>
                <span class="stat-label">My Cars</span>
            </div>
        </a>
        <a href="my_requests.php" class="stat-box stat-orange <?= $total_pending > 0 ? 'has-badge' : '' ?>">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <span class="stat-num"><?= $total_pending ?></span>
                <span class="stat-label">Pending Requests</span>
            </div>
            <?php if ($total_pending > 0): ?>
                <span class="notif-dot"></span>
            <?php endif; ?>
        </a>
        <a href="my_deals.php" class="stat-box stat-purple">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <span class="stat-num"><?= $my_approved ?></span>
                <span class="stat-label">Approved Deals</span>
            </div>
        </a>
        <div class="stat-box stat-dark">
            <div class="stat-icon">🚘</div>
            <div class="stat-info">
                <span class="stat-num"><?= $total_cars ?></span>
                <span class="stat-label">Total on Platform</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <?php if ($total_pending > 0): ?>
    <div class="alert-banner">
        <span>🔔</span>
        <span>You have <strong><?= $total_pending ?> pending request<?= $total_pending > 1 ? 's' : '' ?></strong> on your listings.</span>
        <a href="my_requests.php">Review Now →</a>
    </div>
    <?php endif; ?>

    <!-- Cars for Sale Section -->
    <div class="section-header">
        <div>
            <h2 class="section-title">🏷️ Latest Cars for Sale</h2>
            <p class="section-sub">Recently listed by sellers on CarVista</p>
        </div>
        <a href="buy_cars.php" class="see-all">See All →</a>
    </div>

    <?php if (mysqli_num_rows($latest_sale) == 0): ?>
        <div class="empty-state">
            <span>🚗</span>
            <p>No cars listed for sale yet. <a href="sell_car.php">Be the first to list one!</a></p>
        </div>
    <?php else: ?>
    <div class="cars-row">
        <?php while ($car = $latest_sale->fetch_assoc()): ?>
        <div class="car-card">
            <div class="car-card-top sale-top">
                <span class="car-type-badge">FOR SALE</span>
                <span class="car-seats">👥 <?= $car['capacity'] ?></span>
            </div>
            <div class="car-card-body">
                <h3><?= htmlspecialchars($car['name']) ?></h3>
                <p class="car-meta"><?= htmlspecialchars($car['model']) ?> · <span class="car-color-dot" style="background:<?= strtolower($car['color']) ?>"></span> <?= htmlspecialchars($car['color']) ?></p>
                <p class="car-plate">📍 <?= htmlspecialchars($car['number_plate']) ?></p>
                <p class="car-seller">👤 <?= htmlspecialchars($car['seller_name']) ?></p>
            </div>
            <div class="car-card-footer">
                <span class="car-price">₹<?= number_format($car['price']) ?></span>
                <a href="buy_cars.php" class="car-cta cta-red">Buy →</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- Cars for Rent Section -->
    <div class="section-header" style="margin-top: 40px;">
        <div>
            <h2 class="section-title">🔑 Latest Cars for Rent</h2>
            <p class="section-sub">Available for daily rental on CarVista</p>
        </div>
        <a href="rent_cars.php" class="see-all">See All →</a>
    </div>

    <?php if (mysqli_num_rows($latest_rent) == 0): ?>
        <div class="empty-state">
            <span>🔑</span>
            <p>No cars listed for rent yet. <a href="rent_out_car.php">List yours now!</a></p>
        </div>
    <?php else: ?>
    <div class="cars-row">
        <?php while ($car = $latest_rent->fetch_assoc()): ?>
        <div class="car-card">
            <div class="car-card-top rent-top">
                <span class="car-type-badge">FOR RENT</span>
                <span class="car-seats">👥 <?= $car['capacity'] ?></span>
            </div>
            <div class="car-card-body">
                <h3><?= htmlspecialchars($car['name']) ?></h3>
                <p class="car-meta"><?= htmlspecialchars($car['model']) ?> · <span class="car-color-dot" style="background:<?= strtolower($car['color']) ?>"></span> <?= htmlspecialchars($car['color']) ?></p>
                <p class="car-plate">📍 <?= htmlspecialchars($car['number_plate']) ?></p>
                <p class="car-seller">👤 <?= htmlspecialchars($car['owner_name']) ?></p>
            </div>
            <div class="car-card-footer">
                <span class="car-price">₹<?= number_format($car['price_per_day']) ?><small>/day</small></span>
                <a href="rent_cars.php" class="car-cta cta-green">Rent →</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- How It Works -->
    <div class="how-it-works">
        <h2 class="section-title" style="margin-bottom:6px;">⚙️ How CarVista Works</h2>
        <p class="section-sub" style="margin-bottom:24px;">Three simple steps to buy, sell or rent</p>
        <div class="steps-row">
            <div class="step">
                <div class="step-num">01</div>
                <div class="step-icon">🚗</div>
                <h4>Add Your Car</h4>
                <p>Register your vehicle with details like model, color, and plate number.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <div class="step-num">02</div>
                <div class="step-icon">📋</div>
                <h4>List It</h4>
                <p>Choose to sell or rent it out. Set your price and it goes live instantly.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step">
                <div class="step-num">03</div>
                <div class="step-icon">🤝</div>
                <h4>Approve & Deal</h4>
                <p>Review incoming requests and approve the ones you like. Done!</p>
            </div>
        </div>
    </div>

</section>

<style>
/* ── Welcome Banner ───────────────────────── */
.welcome-banner {
    background: linear-gradient(135deg, #993333 0%, #7a2020 60%, #4a0e0e 100%);
    border-radius: 20px;
    padding: 32px 36px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.welcome-banner::before {
    content: '🚗';
    position: absolute;
    right: 200px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 7rem;
    opacity: 0.07;
    pointer-events: none;
}
.welcome-greeting {
    font-size: 0.85rem;
    opacity: 0.8;
    margin-bottom: 6px;
    text-transform: none;
}
.welcome-text h1 {
    font-size: 1.7rem;
    font-weight: 800;
    margin-bottom: 6px;
    text-transform: capitalize;
}
.welcome-text h1 span { color: #ffd700; }
.welcome-sub { font-size: 0.88rem; opacity: 0.75; text-transform: none; }
.welcome-actions { display: flex; gap: 10px; flex-wrap: wrap; flex-shrink: 0; }
.wa-btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 0.87rem;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
    white-space: nowrap;
    text-transform: none;
}
.wa-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.3); }
.wa-primary { background: white; color: #993333; }
.wa-outline { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.4); }

/* ── Stats Grid ───────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.stat-box {
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid transparent;
}
.stat-box:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.stat-icon { font-size: 1.8rem; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; }
.stat-num  { font-size: 1.7rem; font-weight: 800; line-height: 1; color: inherit; }
.stat-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.75; }
.stat-red    { background: #fff0f0; color: #993333; border-color: #ffd5d5; }
.stat-green  { background: #f0fff4; color: #1a6e2e; border-color: #b2f0c5; }
.stat-blue   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.stat-orange { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.stat-purple { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
.stat-dark   { background: #f8fafc; color: #1e293b; border-color: #e2e8f0; }
.notif-dot {
    position: absolute;
    top: 12px; right: 12px;
    width: 10px; height: 10px;
    background: #ef4444;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%       { transform: scale(1.4); opacity: 0.7; }
}

/* ── Alert Banner ─────────────────────────── */
.alert-banner {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-left: 4px solid #ffc107;
    border-radius: 10px;
    padding: 14px 20px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.9rem;
    color: #6d4c00;
    text-transform: none;
}
.alert-banner a {
    margin-left: auto;
    color: #c2410c;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

/* ── Section Header ───────────────────────── */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 16px;
}
.section-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #1e293b;
}
.section-sub {
    font-size: 0.82rem;
    color: #94a3b8;
    text-transform: none;
    margin-top: 3px;
}
.see-all {
    color: #993333;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    white-space: nowrap;
}
.see-all:hover { text-decoration: underline; }

/* ── Cars Row ─────────────────────────────── */
.cars-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 10px;
}
.car-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s;
}
.car-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}
.car-card-top {
    padding: 12px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sale-top { background: linear-gradient(135deg, #993333, #cc4444); }
.rent-top { background: linear-gradient(135deg, #1a6e2e, #27a844); }
.car-type-badge {
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: white;
    background: rgba(255,255,255,0.2);
    padding: 3px 10px;
    border-radius: 20px;
}
.car-seats {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.9);
    text-transform: none;
}
.car-card-body {
    padding: 14px 16px;
    flex: 1;
}
.car-card-body h3 {
    font-size: 0.98rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    text-transform: capitalize;
}
.car-meta {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
    text-transform: none;
}
.car-color-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.15);
    flex-shrink: 0;
}
.car-plate {
    font-size: 0.78rem;
    color: #94a3b8;
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.car-seller { font-size: 0.78rem; color: #94a3b8; text-transform: none; }
.car-card-footer {
    padding: 12px 16px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.car-price {
    font-size: 1rem;
    font-weight: 800;
    color: #1e293b;
    text-transform: none;
}
.car-price small { font-size: 0.7rem; color: #94a3b8; font-weight: 400; }
.car-cta {
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.15s;
    text-transform: none;
}
.car-cta:hover { transform: scale(1.05); }
.cta-red   { background: #993333; color: white; }
.cta-green { background: #1a6e2e; color: white; }

/* ── Empty State ──────────────────────────── */
.empty-state {
    text-align: center;
    padding: 36px;
    background: #f8fafc;
    border-radius: 14px;
    border: 2px dashed #e2e8f0;
    margin-bottom: 10px;
}
.empty-state span { font-size: 2.5rem; display: block; margin-bottom: 10px; }
.empty-state p { color: #94a3b8; font-size: 0.9rem; text-transform: none; }
.empty-state a { color: #993333; font-weight: 700; }

/* ── How It Works ─────────────────────────── */
.how-it-works {
    background: white;
    border-radius: 20px;
    padding: 32px 36px;
    margin-top: 40px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9;
}
.steps-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.step {
    flex: 1;
    min-width: 160px;
    text-align: center;
    padding: 20px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.step-num {
    font-size: 0.7rem;
    font-weight: 800;
    color: #993333;
    letter-spacing: 2px;
    margin-bottom: 8px;
    text-transform: uppercase;
}
.step-icon { font-size: 2rem; margin-bottom: 10px; display: block; }
.step h4 { font-size: 0.95rem; color: #1e293b; font-weight: 700; margin-bottom: 8px; }
.step p  { font-size: 0.8rem; color: #64748b; line-height: 1.6; text-transform: none; }
.step-arrow { font-size: 1.5rem; color: #cbd5e1; flex-shrink: 0; }

@media (max-width: 700px) {
    .welcome-banner { flex-direction: column; align-items: flex-start; }
    .welcome-banner::before { display: none; }
    .step-arrow { display: none; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>