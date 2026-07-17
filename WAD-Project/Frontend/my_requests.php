<?php
    include "home.php";

    $success = "";
    $error   = "";

    // ── Handle Buy Request Action ──────────────────────────────────────────
    if (isset($_POST['action_buy'])) {
        $request_id = intval($_POST['request_id']);
        $action     = $_POST['action']; // 'approved' or 'rejected'

        // Verify this request belongs to a listing owned by the current user
        $verify = "SELECT br.*, cl.owner_id, cl.car_id
                   FROM buy_requests br
                   JOIN car_listings cl ON br.listing_id = cl.listing_id
                   WHERE br.request_id = $request_id AND cl.owner_id = $user_id";
        $vres = mysqli_query($conn, $verify);

        if (mysqli_num_rows($vres) == 0) {
            $error = "Unauthorized action.";
        } else {
            $row = $vres->fetch_assoc();
            mysqli_query($conn, "UPDATE buy_requests SET status = '$action' WHERE request_id = $request_id");

            // If approved → mark listing as unavailable + reject all other pending requests
            if ($action == 'approved') {
                $lid = $row['listing_id'];
                mysqli_query($conn, "UPDATE car_listings SET availability_status = 'unavailable' WHERE listing_id = $lid");
                mysqli_query($conn, "UPDATE buy_requests SET status = 'rejected' WHERE listing_id = $lid AND request_id != $request_id AND status = 'pending'");
            }

            $success = "Buy request has been " . ucfirst($action) . ".";
        }
    }

    // ── Handle Rent Request Action ─────────────────────────────────────────
    if (isset($_POST['action_rent'])) {
        $request_id = intval($_POST['request_id']);
        $action     = $_POST['action'];

        $verify = "SELECT rr.*, cl.owner_id
                   FROM rent_requests rr
                   JOIN car_listings cl ON rr.listing_id = cl.listing_id
                   WHERE rr.request_id = $request_id AND cl.owner_id = $user_id";
        $vres = mysqli_query($conn, $verify);

        if (mysqli_num_rows($vres) == 0) {
            $error = "Unauthorized action.";
        } else {
            mysqli_query($conn, "UPDATE rent_requests SET status = '$action' WHERE request_id = $request_id");
            $success = "Rent request has been " . ucfirst($action) . ".";
        }
    }

    // ── Fetch Incoming Buy Requests ────────────────────────────────────────
    $buy_reqs = mysqli_query($conn, "
        SELECT br.*, c.name AS car_name, c.model, c.number_plate,
               u.name AS buyer_name, u.email AS buyer_email, u.phone_no AS buyer_phone,
               cl.price, cl.listing_id
        FROM buy_requests br
        JOIN car_listings cl ON br.listing_id = cl.listing_id
        JOIN cars c          ON cl.car_id = c.car_id
        JOIN users u         ON br.buyer_id = u.user_id
        WHERE cl.owner_id = $user_id
        ORDER BY br.requested_at DESC
    ");

    // ── Fetch Incoming Rent Requests ───────────────────────────────────────
    $rent_reqs = mysqli_query($conn, "
        SELECT rr.*, c.name AS car_name, c.model, c.number_plate,
               u.name AS renter_name, u.email AS renter_email,
               cl.price_per_day, cl.listing_id
        FROM rent_requests rr
        JOIN car_listings cl ON rr.listing_id = cl.listing_id
        JOIN cars c          ON cl.car_id = c.car_id
        JOIN users u         ON rr.renter_id = u.user_id
        WHERE cl.owner_id = $user_id
        ORDER BY rr.requested_at DESC
    ");
?>

<section class="mainpart">

    <h2 class="page-title">📋 My Requests</h2>
    <p class="page-sub">Review and respond to buy & rent requests on your cars.</p>

    <?php if ($success != ""): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error != ""): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <!-- ── BUY REQUESTS ──────────────────────────────────────────────── -->
    <h3 class="section-title">🏷️ Buy Requests</h3>

    <?php if (mysqli_num_rows($buy_reqs) == 0): ?>
        <div class="alert info">No buy requests yet.</div>
    <?php else: ?>
    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Plate</th>
                    <th>Sale Price</th>
                    <th>Buyer</th>
                    <th>Contact</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($req = $buy_reqs->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= $req['car_name'] ?></strong><br><small><?= $req['model'] ?></small></td>
                    <td><?= $req['number_plate'] ?></td>
                    <td>₹<?= number_format($req['price']) ?></td>
                    <td><?= $req['buyer_name'] ?></td>
                    <td>
                        <?= $req['buyer_email'] ?><br>
                        <small>📞 <?= $req['buyer_phone'] ?></small>
                    </td>
                    <td><?= date('d M Y', strtotime($req['requested_at'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $req['status'] ?>">
                            <?= ucfirst($req['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($req['status'] == 'pending'): ?>
                        <form method="POST" class="action-form">
                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                            <button type="submit" name="action_buy" value="action_buy"
                                    onclick="this.form.action.value='approved'"
                                    class="btn-approve"
                                    formaction="?">
                                ✓
                            </button>
                            <button type="submit" name="action_buy" value="action_buy"
                                    class="btn-reject">
                                ✗
                            </button>
                            <!-- Hidden action field updated by buttons -->
                            <input type="hidden" name="action" id="buy-action-<?= $req['request_id'] ?>" value="rejected">
                        </form>
                        <script>
                        (function() {
                            var form = document.querySelector('form[data-id="buy-<?= $req['request_id'] ?>"]');
                        })();
                        </script>
                        <?php else: ?>
                            <span class="no-action">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── RENT REQUESTS ─────────────────────────────────────────────── -->
    <h3 class="section-title" style="margin-top:40px;">🔑 Rent Requests</h3>

    <?php if (mysqli_num_rows($rent_reqs) == 0): ?>
        <div class="alert info">No rent requests yet.</div>
    <?php else: ?>
    <div class="requests-table-wrap">
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Plate</th>
                    <th>Rate</th>
                    <th>Renter</th>
                    <th>Dates</th>
                    <th>Contact</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($req = $rent_reqs->fetch_assoc()):
                $days  = (strtotime($req['end_date']) - strtotime($req['start_date'])) / 86400;
                $total = $days * $req['price_per_day'];
            ?>
                <tr>
                    <td><strong><?= $req['car_name'] ?></strong><br><small><?= $req['model'] ?></small></td>
                    <td><?= $req['number_plate'] ?></td>
                    <td>₹<?= number_format($req['price_per_day']) ?>/day</td>
                    <td><?= $req['renter_name'] ?><br><small><?= $req['renter_email'] ?></small></td>
                    <td>
                        <?= date('d M', strtotime($req['start_date'])) ?>
                        → <?= date('d M Y', strtotime($req['end_date'])) ?>
                        <br><small><?= $days ?> day(s)</small>
                    </td>
                    <td><?= $req['contact_info'] ?></td>
                    <td><strong>₹<?= number_format($total) ?></strong></td>
                    <td>
                        <span class="badge badge-<?= $req['status'] ?>">
                            <?= ucfirst($req['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($req['status'] == 'pending'): ?>
                        <form method="POST" class="action-form">
                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                            <input type="hidden" name="action" class="action-val" value="approved">
                            <button type="submit" name="action_rent"
                                    onclick="this.previousElementSibling.value='approved'"
                                    class="btn-approve">✓</button>
                            <button type="submit" name="action_rent"
                                    onclick="this.previousElementSibling.previousElementSibling.value='rejected'"
                                    class="btn-reject">✗</button>
                        </form>
                        <?php else: ?>
                            <span class="no-action">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</section>

<style>
.page-title {
    font-size: 1.8rem;
    color: #993333;
    margin-bottom: 6px;
    font-weight: 700;
}
.page-sub {
    color: #777;
    font-size: 0.9rem;
    margin-bottom: 22px;
    text-transform: none;
}
.section-title {
    font-size: 1.2rem;
    color: #444;
    font-weight: 700;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f0f0f0;
}
.alert {
    padding: 13px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 0.92rem;
}
.alert.success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert.error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
.alert.info    { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }

.requests-table-wrap {
    overflow-x: auto;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1px solid #eee;
}
.requests-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 0.87rem;
}
.requests-table thead {
    background: #993333;
    color: white;
}
.requests-table th {
    padding: 13px 14px;
    text-align: left;
    font-weight: 600;
    font-size: 0.82rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.requests-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #f5f5f5;
    color: #444;
    vertical-align: middle;
    text-transform: none;
}
.requests-table tbody tr:hover {
    background: #fafafa;
}
.requests-table small {
    color: #888;
    font-size: 0.78rem;
}
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-pending  { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.badge-approved { background:#d4edda; color:#155724; border:1px solid #28a745; }
.badge-rejected { background:#f8d7da; color:#721c24; border:1px solid #dc3545; }

.action-form {
    display: flex;
    gap: 6px;
}
.btn-approve, .btn-reject {
    width: 32px; height: 32px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.15s;
}
.btn-approve { background: #28a745; color: white; }
.btn-reject  { background: #dc3545; color: white; }
.btn-approve:hover, .btn-reject:hover { transform: scale(1.15); }
.no-action { color: #ccc; font-size: 1.1rem; }
</style>

<script>
// For buy request buttons - set action value before submit
document.querySelectorAll('.action-form').forEach(function(form) {
    var buttons = form.querySelectorAll('button[name="action_buy"]');
    if (buttons.length === 2) {
        buttons[0].addEventListener('click', function() {
            form.querySelector('[name="action"]').value = 'approved';
        });
        buttons[1].addEventListener('click', function() {
            form.querySelector('[name="action"]').value = 'rejected';
        });
    }
});
</script>