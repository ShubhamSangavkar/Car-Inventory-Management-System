<?php
    include '../Backend/connect_db.php';
    session_start();  // required again
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarVista Home</title>
    <link rel="stylesheet" href="css/styles.css"> 
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <header style="background-color: #993333">
        <div><img src="res/logo.png" alt="logo"></div>
        <div>
            <nav>
                <ul>
                    <li><a href="home.html">Home</a></li>
                    <li><a href="">About</a></li>
                    <li><a href="">Contact</a></li>
                    <li><a href="">Review</a></li>
                </ul>
            </nav>
        </div>
    </header>


    <div class="sidebar">
    
        <div id="prof">
            <?php
            echo "<h3><b>" .$_SESSION['username']."</b></h3>
            <p><b>".$_SESSION['address']."</b></p>
            <p>" .$_SESSION['email']. "</p>";

            ?>
        </div>
        <div style="padding-left: 0x;">
            <a href="">  Home</a>
            <a href="">  Buy Cars</a>
            <a href="">  Rent Cars</a>
            <a href="mycar.php">  My Cars</a>
            <a href="">  My Deals</a>
            <a href="">  History</a>
            <a href="">  Edit Profile</a>
            <a href="">  Logout</a>
        </div>

    </div>
    
