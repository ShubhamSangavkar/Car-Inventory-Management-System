<?php
    include "home.php";
?>

<section class="mainpart">

       <h2 class="page-title">🚗 My Cars</h2>
 
    <?php
        $query_get_car = "
            SELECT c.*,
                (SELECT listing_type FROM car_listings
                 WHERE car_id = c.car_id AND availability_status = 'available'
                 AND listing_type = 'sale' LIMIT 1) AS sale_status,
                (SELECT listing_type FROM car_listings
                 WHERE car_id = c.car_id AND availability_status = 'available'
                 AND listing_type = 'rent' LIMIT 1) AS rent_status
            FROM cars c
            WHERE c.owner_id = $user_id
        ";
        $get_car = mysqli_query($conn, $query_get_car);
 
        if (mysqli_num_rows($get_car) == 0):
    ?>
        <div class="alert info">You haven't added any cars yet.</div>
    <?php else: ?>
 
    <div class="cars-grid">
        <?php while ($car = $get_car->fetch_assoc()): ?>
        <div class="car-card">
            <div class="car-info">
                <h3><?= $car['name'] ?></h3>
                <p><span class="label">Model:</span> <?= $car['model'] ?></p>
                <p><span class="label">Plate:</span> <?= $car['number_plate'] ?></p>
                <p><span class="label">Color:</span> <?= $car['color'] ?></p>
                <p><span class="label">Capacity:</span> <?= $car['capacity'] ?> seats</p>
            </div>
 
            <div class="car-badges">
                <?php if ($car['sale_status'] == 'sale'): ?>
                    <span class="badge badge-sale">Listed for Sale</span>
                <?php endif; ?>
                <?php if ($car['rent_status'] == 'rent'): ?>
                    <span class="badge badge-rent">Listed for Rent</span>
                <?php endif; ?>
            </div>
 
            <div class="car-actions">
                <?php if ($car['sale_status'] != 'sale'): ?>
                    <a href="sell_car.php?car_id=<?= $car['car_id'] ?>" class="btn btn-sell">
                        🏷️ Sell
                    </a>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled>✓ On Sale</button>
                <?php endif; ?>
 
                <?php if ($car['rent_status'] != 'rent'): ?>
                    <a href="rent_out_car.php?car_id=<?= $car['car_id'] ?>" class="btn btn-rent">
                        🔑 Rent Out
                    </a>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled>✓ On Rent</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
 
    <?php endif; ?>
 
    <a href="addcar.php" class="btn-add">+ Add New Car</a>
    </section>
 
<style>
.page-title {
    font-size: 1.8rem;
    color: #993333;
    margin-bottom: 25px;
    font-weight: 700;
}
 
.alert.info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
}
 
.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
 
.car-card {
    background: white;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid #eee;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: transform 0.2s, box-shadow 0.2s;
}
 
.car-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
 
.car-info h3 {
    font-size: 1.15rem;
    color: #993333;
    margin-bottom: 10px;
    font-weight: 700;
    text-transform: capitalize;
}
 
.car-info p {
    font-size: 0.88rem;
    color: #555;
    margin-bottom: 5px;
    text-transform: none;
}
 
.label {
    font-weight: 600;
    color: #333;
}
 
.car-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
 
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
 
.badge-sale {
    background: #ffeeba;
    color: #856404;
    border: 1px solid #ffc107;
}
 
.badge-rent {
    background: #c3e6cb;
    color: #155724;
    border: 1px solid #28a745;
}
 
.car-actions {
    display: flex;
    gap: 10px;
}
 
.btn {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
    transition: transform 0.15s, opacity 0.15s;
}
 
.btn:hover {
    transform: scale(1.03);
    opacity: 0.9;
}
 
.btn-sell {
    background: linear-gradient(to right, #993333, #cc4444);
    color: white;
}
 
.btn-rent {
    background: linear-gradient(to right, #1a6e2e, #27a844);
    color: white;
}
 
.btn-disabled {
    background: #e9ecef;
    color: #999;
    cursor: not-allowed;
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
}
 
.btn-add {
    display: inline-block;
    padding: 13px 28px;
    background: #993333;
    color: white;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
    text-transform: none;
}
 
.btn-add:hover {
    background: #7a2828;
    transform: scale(1.02);
}
</style>