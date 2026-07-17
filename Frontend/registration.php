<?php

$conn = mysqli_connect("localhost","root","","wad_project");

if(!$conn)
{
    die("Database Connection Failed");
}

$message = "";

if(isset($_POST['register']))
{
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // SERVER SIDE VALIDATION

    if(!preg_match("/^[A-Za-z]+(?: [A-Za-z]+)*$/",$name))
    {
        $message = "Invalid Name!";
    }

    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))
    {
        $message = "Invalid Email!";
    }

    elseif(!preg_match("/^[6-9][0-9]{9}$/",$phone))
    {
        $message = "Invalid Phone Number!";
    }

    elseif(strlen($address) < 10)
    {
        $message = "Address Too Short!";
    }

    elseif(
        strlen($password) < 8 ||
        !preg_match("/[A-Z]/",$password) ||
        !preg_match("/[a-z]/",$password) ||
        !preg_match("/[0-9]/",$password) ||
        !preg_match("/[\W]/",$password)
    )
    {
        $message = "Weak Password!";
    }

    elseif($password != $confirm)
    {
        $message = "Passwords Do Not Match!";
    }

    else
    {
        $check = "SELECT * FROM users WHERE email='$email'";

        $result = mysqli_query($conn,$check);

        if(mysqli_num_rows($result) > 0)
        {
            $message = "Email Already Exists!";
        }
        else
        {
            $query = "INSERT INTO users
            (name,email,phone_no,address,password)

            VALUES

            ('$name','$email','$phone','$address','$password')";

            if(mysqli_query($conn,$query))
            {
                header("Location: login.php");
            }
            else
            {
                $message = "Registration Failed!";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Registration</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    background:
    linear-gradient(rgba(0,0,0,0.65),
    rgba(0,0,0,0.65)),

    url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1600');

    background-size:cover;
    background-position:center;
}

.container{

    width:450px;

    padding:35px;

    border-radius:25px;

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(15px);

    border:1px solid rgba(255,255,255,0.2);

    box-shadow:0 8px 32px rgba(0,0,0,0.5);

    color:white;
}

.container h1{

    text-align:center;

    margin-bottom:25px;

    font-size:38px;
}

.input-box{

    margin-bottom:18px;
}

.input-box input,
.input-box textarea{

    width:100%;

    padding:15px;

    border:none;

    border-radius:12px;

    outline:none;

    font-size:15px;
}

.input-box textarea{

    height:80px;

    resize:none;
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

.login{

    text-align:center;

    margin-top:20px;
}

.login a{

    color:#00e5ff;

    text-decoration:none;

    font-weight:bold;
}

.login a:hover{

    text-decoration:underline;
}

.validation{

    margin-top:5px;

    padding-left:5px;

    font-size:13px;
}

.valid{
    color:#00ff88;
}

.invalid{
    color:#ff4d4d;
}

</style>

</head>

<body>

<div class="container">

    <h1>Create Account</h1>

    <?php
    if($message != "")
    {
        echo "<div class='error'>$message</div>";
    }
    ?>

    <form method="POST">

        <div class="input-box">

            <input type="text"
                   id="name"
                   name="name"
                   placeholder="Full Name"
                   required>

            <div id="nameMsg" class="validation"></div>

        </div>

        <div class="input-box">

            <input type="email"
                   id="email"
                   name="email"
                   placeholder="Email Address"
                   required>

            <div id="emailMsg" class="validation"></div>

        </div>

        <div class="input-box">

            <input type="text"
                   id="phone"
                   name="phone"
                   placeholder="Phone Number"
                   required>

            <div id="phoneMsg" class="validation"></div>

        </div>

        <div class="input-box">

            <textarea
                id="address"
                name="address"
                placeholder="Enter Full Address"
                required></textarea>

            <div id="addressMsg" class="validation"></div>

        </div>

        <div class="input-box">

            <input type="password"
                   id="password"
                   name="password"
                   placeholder="Create Password"
                   required>

            <div id="passwordMsg" class="validation"></div>

        </div>

        <div class="input-box">

            <input type="password"
                   id="confirm"
                   name="confirm_password"
                   placeholder="Confirm Password"
                   required>

            <div id="confirmMsg" class="validation"></div>

        </div>

        <button type="submit"
                name="register"
                class="btn">

            REGISTER

        </button>

    </form>

    <div class="login">

        Already Have Account?

        <a href="login.php">
            Login
        </a>

    </div>

</div>

<script>

// NAME

document.getElementById("name").addEventListener("keyup",function(){

    let name = this.value.trim();

    let msg = document.getElementById("nameMsg");

    let pattern = /^[A-Za-z]+(?: [A-Za-z]+)*$/;

    if(name.length == 0)
    {
        msg.innerHTML = "";
    }

    else if(name.length < 3)
    {
        msg.innerHTML = "Minimum 3 letters required";
        msg.className = "validation invalid";
    }

    else if(!pattern.test(name))
    {
        msg.innerHTML = "Only alphabets allowed";
        msg.className = "validation invalid";
    }

    else
    {
        msg.innerHTML = "✓ Valid Name";
        msg.className = "validation valid";
    }

});


// EMAIL

document.getElementById("email").addEventListener("keyup",function(){

    let email = this.value.trim();

    let msg = document.getElementById("emailMsg");

    let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

    if(email.length == 0)
    {
        msg.innerHTML = "";
    }

    else if(pattern.test(email))
    {
        msg.innerHTML = "✓ Valid Email";
        msg.className = "validation valid";
    }

    else
    {
        msg.innerHTML = "Enter Valid Email";
        msg.className = "validation invalid";
    }

});


// PHONE

document.getElementById("phone").addEventListener("keyup",function(){

    let phone = this.value.trim();

    let msg = document.getElementById("phoneMsg");

    if(phone.length == 0)
    {
        msg.innerHTML = "";
    }

    else if(phone.match(/^[6-9][0-9]{9}$/))
    {
        msg.innerHTML = "✓ Valid Phone Number";
        msg.className = "validation valid";
    }

    else
    {
        msg.innerHTML = "Enter Valid 10 Digit Number";
        msg.className = "validation invalid";
    }

});


// ADDRESS

document.getElementById("address").addEventListener("keyup",function(){

    let address = this.value.trim();

    let msg = document.getElementById("addressMsg");

    if(address.length == 0)
    {
        msg.innerHTML = "";
    }

    else if(address.length >= 10)
    {
        msg.innerHTML = "✓ Valid Address";
        msg.className = "validation valid";
    }

    else
    {
        msg.innerHTML = "Minimum 10 characters required";
        msg.className = "validation invalid";
    }

});


// PASSWORD

document.getElementById("password").addEventListener("keyup",function(){

    let password = this.value;

    let msg = document.getElementById("passwordMsg");

    let upper = /[A-Z]/.test(password);
    let lower = /[a-z]/.test(password);
    let number = /[0-9]/.test(password);
    let special = /[\W]/.test(password);

    if(password.length == 0)
    {
        msg.innerHTML = "";
    }

    else if(password.length >= 8 && upper && lower && number && special)
    {
        msg.innerHTML = "✓ Strong Password";
        msg.className = "validation valid";
    }

    else
    {
        msg.innerHTML =
        "Password must contain uppercase, lowercase, number & special character";

        msg.className = "validation invalid";
    }

});


// CONFIRM PASSWORD

document.getElementById("confirm").addEventListener("keyup",function(){

    let confirm = this.value.trim();

    let password = document.getElementById("password").value.trim();

    let msg = document.getElementById("confirmMsg");

    if(confirm.length == 0)
    {
        msg.innerHTML = "";
        return;
    }

    if(password.length == 0)
    {
        msg.innerHTML = "Enter Password First";
        msg.className = "validation invalid";
        return;
    }

    if(confirm === password)
    {
        msg.innerHTML = "✓ Password Matched";
        msg.className = "validation valid";
    }

    else
    {
        msg.innerHTML = "Passwords Do Not Match";
        msg.className = "validation invalid";
    }

});

</script>

</body>
</html>