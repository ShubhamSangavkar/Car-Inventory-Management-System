<?php
    include "home.php";

    $success = "";
    $error   = "";

    // Handle rent request submission
    if (isset($_POST['send_rent_request'])) {
        $listing_id  = intval($_POST['listing_id']);
        $start_date  = $_POST['start_date'];
        $end_date    = $_POST['end_date'];
        $contact     = trim($_POST['contact_info']);

        // Validate listing
        $check = "SELECT * FROM car_listings WHERE listing_id = $listing_id AND listing_type = 'rent' AND availability_status = 'available'";
        $check_result = mysqli_query($conn, $check);

        if (mysqli_num_rows($check_result) == 0) {
            $error = "This listing is no longer available.";
        } else {
            $listing = $check_result->fetch_assoc();

            if ($listing['owner_id'] == $user_id) {
                $error = "You cannot rent your own car.";
            } elseif (empty($start_date) || empty($end_date)) {
                $error = "Please select both start and end dates.";
            } elseif ($start_date >= $end_date) {
                $error = "End date must be after start date.";
            } elseif ($start_date < date('Y-m-d')) {
                $error = "Start date cannot be in the past.";
            } elseif (empty($contact)) {
                $error = "Please provide your contact information.";
            } else {
                // Check for overlapping pending/approved rent requests
                $overlap = "SELECT * FROM rent_requests
                            WHERE listing_id = $listing_id
                              AND status != 'rejected'
                              AND (
                                  (start_date <= '$end_date' AND end_date >= '$start_date')
                              )";
                $overlap_result = mysqli_query($conn, $overlap);

                if (mysqli_num_rows($overlap_result) > 0) {
                    $error = "These dates are already booked. Please choose different dates.";
                } else {
                    $contact_escaped = mysqli_real_escape_string($conn, $contact);
                    $insert = "INSERT INTO rent_requests (listing_id, renter_id, start_date, end_date, contact_info, status)
                               VALUES ($listing_id, $user_id, '$start_date', '$end_date', '$contact_escaped', 'pending')";

                    if (mysqli_query($conn, $insert)) {
                        $success = "Your rent request has been sent! The owner will review it shortly.";
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }
    }

    // Fetch all available cars for rent (exclude current user's listings)
    $listings_query = "
        SELECT cl.*, c.name, c.model, c.color, c.capacity, c.description, c.number_plate,
               u.name AS owner_name, u.phone_no AS owner_phone
        FROM car_listings cl
        JOIN cars c ON cl.car_id = c.car_id
        JOIN users u ON cl.owner_id = u.user_id
        WHERE cl.listing_type = 'rent'
          AND cl.availability_status = 'available'
          AND cl.owner_id != $user_id
        ORDER BY cl.created_at DESC
    ";
    $listings = mysqli_query($conn, $listings_query);

    // Today's date for min date attribute
    $today = date('Y-m-d');
?>

<section class="mainpart">

    <h2 class="page-title">🔑 Cars for Rent</h2>

    <?php if ($success != ""): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error != ""): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($listings) == 0): ?>
        <div class="alert info">No cars are available for rent right now. Check back later!</div>
    <?php else: ?>

    <div class="listings-grid">
        <?php while ($listing = $listings->fetch_assoc()):
            $lid = $listing['listing_id'];
        ?>
        <div class="listing-card">

            <div class="listing-header">
                <h3><?= $listing['name'] ?></h3>
                <span class="price-tag">₹<?= number_format($listing['price_per_day']) ?>/day</span>
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

            <div class="owner-info">
                <span>👤 <strong><?= $listing['owner_name'] ?></strong></span>
                <span>📞 <?= $listing['owner_phone'] ?></span>
            </div>

            <!-- Rent Request Form -->
            <div class="rent-form-toggle">
                <button class="btn-toggle" onclick="toggleForm(<?= $lid ?>)">
                    📅 Request to Rent
                </button>
            </div>

            <div class="rent-form" id="form-<?= $lid ?>" style="display:none;">
                <form method="POST">
                    <input type="hidden" name="listing_id" value="<?= $lid ?>">

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" min="<?= $today ?>" required>
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" min="<?= $today ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Your Contact No.</label>
                        <input type="text" name="contact_info"
                               placeholder="e.g. 9876543210" required>
                    </div>

                    <div class="price-estimate" id="estimate-<?= $lid ?>"
                         data-ppd="<?= $listing['price_per_day'] ?>">
                        <span id="estimate-text-<?= $lid ?>">Select dates to see total estimate</span>
                    </div>

                    <button type="submit" name="send_rent_request" class="btn-rent">
                        Send Rent Request
                    </button>
                </form>
            </div>

        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>

</section>

<script>
function toggleForm(id) {
    const form = document.getElementById('form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Auto-calculate price estimate when dates change
document.addEventListener('change', function(e) {
    if (e.target.type === 'date') {
        const form = e.target.closest('.rent-form');
        if (!form) return;

        const id     = form.id.replace('form-', '');
        const start  = form.querySelector('[name="start_date"]').value;
        const end    = form.querySelector('[name="end_date"]').value;
        const ppd    = parseFloat(document.getElementById('estimate-' + id).dataset.ppd);
        const estEl  = document.getElementById('estimate-text-' + id);

        if (start && end && end > start) {
            const days = Math.ceil((new Date(end) - new Date(start)) / (1000 * 60 * 60 * 24));
            const total = (days * ppd).toLocaleString('en-IN');
            estEl.textContent = `${days} day(s) × ₹${ppd.toLocaleString('en-IN')}/day = ₹${total} total`;
        } else if (start && end && end <= start) {
            estEl.textContent = "⚠️ End date must be after start date";
        } else {
            estEl.textContent = "Select dates to see total estimate";
        }
    }
});
</script>

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
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
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
    background: linear-gradient(to right, #1a6e2e, #27a844);
    color: white;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
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

.owner-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #444;
    text-transform: none;
}

.btn-toggle {
    width: 100%;
    padding: 12px;
    background: linear-gradient(to right, #1a6e2e, #27a844);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: transform 0.15s, box-shadow 0.15s;
}

.btn-toggle:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(39,168,68,0.35);
}

.rent-form {
    background: #f9f9f9;
    border-radius: 12px;
    padding: 18px;
    border: 1px solid #e5e5e5;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 14px;
}

.form-group label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
    text-transform: capitalize;
}

.form-group input {
    padding: 10px 13px;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    font-size: 0.88rem;
    background: white;
    transition: border-color 0.2s;
}

.form-group input:focus {
    border-color: #27a844;
    outline: none;
}

.price-estimate {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.85rem;
    color: #6d5200;
    font-weight: 600;
    margin-bottom: 14px;
    text-transform: none;
}

.btn-rent {
    width: 100%;
    padding: 12px;
    background: linear-gradient(to right, #1a6e2e, #27a844);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: transform 0.15s, box-shadow 0.15s;
}

.btn-rent:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(39,168,68,0.35);
}
</style>
