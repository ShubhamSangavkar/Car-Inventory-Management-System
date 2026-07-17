<?php

$conn = mysqli_connect("localhost","root","","wad_project");

if(!$conn)
{
    die("Database Connection Failed");
}

$message = "";

if(isset($_POST['login']))
{
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users 
              WHERE email='$email' 
              AND password='$password'";

    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0)
    { $row=$result->fetch_assoc();
                echo $row['name']."         ".$row['address'];

                session_start();
                $_SESSION['user_id']= $row['user_id'];
                $_SESSION['username']=$row['name'];
                $_SESSION['address']=$row['address'];
                $_SESSION['email']=$row['email'];
                
                header("Location: index.php");
                exit();
    }
    else
    {
        $message = "Invalid Email or Password!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Car Store Login</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{

    height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    background:
    linear-gradient(rgba(0,0,0,0.6),
    rgba(0,0,0,0.6)),

    url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1600');

    background-size:cover;
    background-position:center;
}

.container{

    width:380px;

    padding:40px;

    border-radius:25px;

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(12px);

    border:1px solid rgba(255,255,255,0.2);

    box-shadow:0 8px 32px rgba(0,0,0,0.5);

    color:white;
}

.container h1{

    text-align:center;

    margin-bottom:30px;

    font-size:42px;
}

.input-box{

    margin-bottom:20px;
}

.input-box input{

    width:100%;

    padding:15px;

    border:none;

    border-radius:12px;

    outline:none;

    font-size:16px;
}

.btn{

    width:100%;

    padding:15px;

    border:none;

    border-radius:12px;

    background:linear-gradient(to right,#ff512f,#dd2476);

    color:white;

    font-size:20px;

    font-weight:bold;

    cursor:pointer;

    transition:0.3s;
}

.btn:hover{

    transform:scale(1.03);
}

.error{

    background:#ff4d4d;

    padding:12px;

    border-radius:10px;

    margin-bottom:18px;

    text-align:center;

    font-weight:bold;
}

.register{

    text-align:center;

    margin-top:20px;

    font-size:17px;
}

.register a{

    color:#00e5ff;

    text-decoration:none;

    font-weight:bold;
}

.register a:hover{

    text-decoration:underline;
}

</style>

</head>

<body>

<div class="container">

    <h1>Car Vista</h1>

    <?php
    if($message != "")
    {
        echo "<div class='error'>$message</div>";
    }
    ?>

    <form method="POST">

        <div class="input-box">

            <input type="email"
                   name="email"
                   placeholder="Enter Email"
                   required>

        </div>

        <div class="input-box">

            <input type="password"
                   name="password"
                   placeholder="Enter Password"
                   required>

        </div>

        <button type="submit"
                name="login"
                class="btn">

            LOGIN

        </button>

    </form>

    <div class="register">

        New User?

        <a href="registration.php">
            Create Account
        </a>

    </div>

</div>

</body>
</html>