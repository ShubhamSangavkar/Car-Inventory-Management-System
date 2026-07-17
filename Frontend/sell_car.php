<?php
    include "home.php";

    $success = "";
    $error = "";

    // Handle form submission
    if (isset($_POST['list_for_sale'])) {
        $car_id     = $_POST['car_id'];
        $price      = $_POST['price'];

        // Make sure the car belongs to the logged-in user
        $check = "SELECT * FROM cars WHERE car_id = $car_id AND owner_id = $user_id";
        $check_result = mysqli_query($conn, $check);

        if (mysqli_num_rows($check_result) == 0) {
            $error = "Invalid car selection.";
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = "Please enter a valid price.";
        } else {
            // Check if already listed for sale
            $already = "SELECT * FROM car_listings WHERE car_id = $car_id AND listing_type = 'sale' AND availability_status = 'available'";
            $already_result = mysqli_query($conn, $already);

            if (mysqli_num_rows($already_result) > 0) {
                $error = "This car is already listed for sale.";
            } else {
                $insert = "INSERT INTO car_listings (car_id, owner_id, listing_type, price, availability_status)
                           VALUES ($car_id, $user_id, 'sale', $price, 'available')";

                if (mysqli_query($conn, $insert)) {
                    $success = "Your car has been listed for sale successfully!";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }

    // Fetch user's cars for the dropdown
    $cars_query = "SELECT * FROM cars WHERE owner_id = $user_id";
    $cars_result = mysqli_query($conn, $cars_query);
?>

<section class="mainpart">

    <h2 class="page-title">🏷️ List Car for Sale</h2>

    <?php if ($success != ""): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($cars_result) == 0): ?>
        <div class="alert info">You have no cars to list. <a href="addcar.php">Add a car first</a>.</div>
    <?php else: ?>

    <div class="form-card">
        <form method="POST">

            <div class="form-group">
                <label for="car_id">Select Car</label>
                <select name="car_id" id="car_id" required>
                    <option value="">-- Choose a car --</option>
                    <?php while ($car = $cars_result->fetch_assoc()): ?>
                        <option value="<?= $car['car_id'] ?>">
                            <?= $car['name'] ?> — <?= $car['number_plate'] ?> (<?= $car['model'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Sale Price (₹)</label>
                <input type="number" name="price" id="price" placeholder="e.g. 500000" min="1" required>
                <small>Enter the price in Indian Rupees</small>
            </div>

            <button type="submit" name="list_for_sale" class="btn-submit">
                List for Sale
            </button>

        </form>
    </div>

    <?php endif; ?>

    <a href="mycar.php" class="back-link">← Back to My Cars</a>

</section>

<style>
.page-title {
    font-size: 1.8rem;
    color: #993333;
    margin-bottom: 20px;
    font-weight: 700;
}

.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 0.95rem;
}

.alert.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert.info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert a {
    color: #0c5460;
    font-weight: bold;
}

.form-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 520px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.form-group {
    margin-bottom: 22px;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 7px;
    font-size: 0.95rem;
    text-transform: none;
}

.form-group select,
.form-group input {
    padding: 12px 15px;
    border: 1.5px solid #ddd;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
    background: #fafafa;
}

.form-group select:focus,
.form-group input:focus {
    border-color: #993333;
    background: white;
    outline: none;
}

.form-group small {
    margin-top: 5px;
    color: #888;
    font-size: 0.8rem;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(to right, #993333, #cc4444);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-submit:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(153, 51, 51, 0.4);
}

.back-link {
    display: inline-block;
    margin-top: 20px;
    color: #993333;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}
</style>
