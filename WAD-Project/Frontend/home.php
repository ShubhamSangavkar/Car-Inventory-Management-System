<?php
  session_start();
 
    // Redirect to login if not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
 
    include '../Backend/connect_db.php';
 
    $user_id = $_SESSION['user_id'];
    $query   = "SELECT * FROM users WHERE user_id = $user_id";
    $result  = mysqli_query($conn, $query);
    $user    = $result->fetch_assoc();
 
    // Keep session fresh
    $_SESSION['username'] = $user['name'];
    $_SESSION['address']  = $user['address'];
    $_SESSION['email']    = $user['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarVista</title>
    <link rel="stylesheet" href="css/new.css">
</head>
<body>

    <header style="background-color: #993333; width:100%;">
        <div><img src="res/logo.png" alt="logo"></div>
        <div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="">About</a></li>
                    <li><a href="">Contact</a></li>
                    <li><a href="">Review</a></li>
                </ul> 
            </nav>
        </div>
    </header>

    <div class="sidebar">
        <div id="prof">
            <h3><b><?= $_SESSION['username'] ?></b></h3>
            <p><b><?= $_SESSION['address'] ?></b></p>
            <p><?= $_SESSION['email'] ?></p>
        </div>
        <div style="padding-left: 0px;">
            <a href="index.php">🏠 Home</a>
            <a href="buy_cars.php">🏷️ Buy Cars</a>
            <a href="rent_cars.php">🔑 Rent Cars</a>
            <a href="mycar.php">🚗 My Cars</a>
            <a href="my_requests.php">📋 My Requests</a>
            <a href="my_deals.php">🤝 My Deals</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>