<?php
    include "home.php";

    $success = "";
    $error   = "";

    // Handle buy request submission

        $listing_id = intval($_POST['listing_id']);

        // Make sure listing exists and is available
        $check = "SELECT * FROM car_listings WHERE listing_id = $listing_id AND listing_type = 'sale' AND availability_status = 'available'";
        $check_result = mysqli_query($conn, $check);

        if (mysqli_num_rows($check_result) == 0) {
            $error = "This listing is no longer available.";
        } else {
            $listing = $check_result->fetch_assoc();

            // Prevent owner from buying their own car
            if ($listing['owner_id'] == $user_id) {
                $error = "You cannot buy your own car.";
            } else {
                // Check if user already sent a request for this listing
                $already = "SELECT * FROM buy_requests WHERE listing_id = $listing_id AND buyer_id = $user_id AND status = 'pending'";
                $already_result = mysqli_query($conn, $already);

                if (mysqli_num_rows($already_result) > 0) {
                    $error = "You already have a pending request for this car.";
                } else {
                    $insert = "INSERT INTO buy_requests (listing_id, buyer_id, status)
                               VALUES ($listing_id, $user_id, 'pending')";
                    if (mysqli_query($conn, $insert)) {
                        $success = "Your buy request has been sent! The seller will review it shortly.";
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }


    // Fetch all available cars for sale (exclude current user's own listings)
    $listings_query = "
        SELECT cl.*, c.name, c.model, c.color, c.capacity, c.description, c.number_plate,
               u.name AS seller_name, u.phone_no AS seller_phone
        FROM car_listings cl
        JOIN cars c ON cl.car_id = c.car_id
        JOIN users u ON cl.owner_id = u.user_id
        WHERE cl.listing_type = 'sale'
          AND cl.availability_status = 'available'
          AND cl.owner_id != $user_id
        ORDER BY cl.created_at DESC
    ";
    $listings = mysqli_query($conn, $listings_query);
//     if(!$listing){
//     die("Query Failed: " . mysqli_error($conn));
// }
?>

<section class="mainpart">

    <h2 class="page-title">🏷️ Cars for Sale</h2>

    <?php if ($success != ""): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error != ""): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($listings) == 0): ?>
        <div class="alert info">No cars are listed for sale right now. Check back later!</div>
    <?php else: ?>

    <div class="listings-grid">
        <?php while ($listing = $listings->fetch_assoc()): ?>
        <div class="listing-card">

            <div class="listing-header">
                <h3><?= $listing['name'] ?></h3>
                <span class="price-tag">₹<?= number_format($listing['price']) ?></span>
            </div>

            <div class="listing-details">
                <div class="detail-row">
                    <span class="detail-label">Model</span>
                    <span><?= $listing['model'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Color</span>
                    <span><?= $listing['color'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Plate</span>
                    <span><?= $listing['number_plate'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Seats</span>
                    <span><?= $listing['capacity'] ?></span>
                </div>
                <?php if (!empty($listing['description'])): ?>
                <div class="detail-row description-row">
                    <span class="detail-label">About</span>
                    <span><?= $listing['description'] ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="seller-info">
                <span>👤 <strong><?= $listing['seller_name'] ?></strong></span>
                <span>📞 <?= $listing['seller_phone'] ?></span>
            </div>

            <form method="POST">
                <input type="hidden" name="listing_id" value="<?= $listing['listing_id'] ?>">
                <button type="submit" name="send_request" class="btn-buy">
                    Send Buy Request
                </button>
            </form>

        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>

</section>

<style>
.page-title {
    font-size: 1.8rem;
    color: #993333;
    margin-bottom: 25px;
    font-weight: 700;
}

.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 0.95rem;
}
.alert.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert.info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }

.listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 22px;
}

.listing-card {
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

.listing-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}

.listing-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.listing-header h3 {
    font-size: 1.15rem;
    color: #222;
    font-weight: 700;
    text-transform: capitalize;
}

.price-tag {
    background: linear-gradient(to right, #993333, #cc4444);
    color: white;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    white-space: nowrap;
    text-transform: none;
}

.listing-details {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.87rem;
    color: #555;
    border-bottom: 1px dashed #f0f0f0;
    padding-bottom: 6px;
    text-transform: none;
}

.description-row {
    flex-direction: column;
    gap: 3px;
}

.detail-label {
    font-weight: 600;
    color: #333;
    min-width: 60px;
    text-transform: capitalize;
}

.seller-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #444;
    text-transform: none;
}

.btn-buy {
    width: 100%;
    padding: 13px;
    background: linear-gradient(to right, #993333, #cc4444);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: transform 0.15s, box-shadow 0.15s;
}

.btn-buy:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(153,51,51,0.35);
}
</style>
