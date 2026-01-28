<?php
    session_start();
    include('../db_config.php');

    if(!isset($_SESSION['email'])) {
        header("location: login.php");
        exit();
    }

    $user_email = $_SESSION['email'];
    $id = mysqli_real_escape_string($con, $_GET['dd']);
    
    $check_qr = "SELECT * FROM tb_orders WHERE order_id = '$id' AND email = '$user_email'";
    $check_res = mysqli_query($con, $check_qr);
    
    if(mysqli_num_rows($check_res) > 0){
        $qr = "DELETE FROM tb_orders WHERE order_id = '$id'";
        mysqli_query($con, $qr);
        $_SESSION['order_msg'] = "Order #$id cancelled successfully";
    } else {
        $_SESSION['order_msg'] = "Failed to cancel order or access denied.";
    }
    header("location: my-orders.php");
?>
