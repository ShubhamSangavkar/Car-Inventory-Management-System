<?php
    include '../Backend/connect_db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post">
        <input type="number" name="id" require>
        <button name="sub">Submit</button>
    </form>

    <?php
        if(isset($_POST['sub'])){
            $id=$_POST['id'];
            $query = "SELECT * FROM users WHERE user_id = $id";
            $result = mysqli_query($conn, $query);

            if(mysqli_num_rows($result) > 0){
                $row=$result->fetch_assoc();
                echo $row['name']."         ".$row['address'];

                session_start();
                $_SESSION['user_id']= $row['user_id'];
                $_SESSION['username']=$row['name'];
                $_SESSION['address']=$row['address'];
                $_SESSION['email']=$row['email'];
                
                header("Location: index.php");
                exit();

            } else {
                echo "ID not found";
            }

        }
    ?>
</body>
</html>