<?php
include "connect_db.php";
if (isset($_POST['login'])) {
    $email = $_POST['login_email'] ?? '';
    echo $email;
}
?>