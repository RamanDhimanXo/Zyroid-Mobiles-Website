<?php
session_start();
include('../db_config.php');

if (isset($_GET['did'])) {
    $id = mysqli_real_escape_string($con, $_GET['did']);
    $qr = "DELETE FROM `tb_orders` WHERE `order_id` = '$id'";
    $res = mysqli_query($con, $qr);
    if ($res) {
        $notif_msg = "Order #$id has been deleted.";
        $notif_msg = mysqli_real_escape_string($con, $notif_msg);
        $notif_date = date('Y-m-d');
        $notif_type = 'order_delete';
        $notif_q = "INSERT INTO `tb_notifications` (`type`, `source_id`, `is_read`, `created_at`, `message`) VALUES ('$notif_type', '$id', '2', '$notif_date', '$notif_msg')";
        mysqli_query($con, $notif_q);

        $_SESSION['action_msg'] = "Order Deleted Successfully";
        $_SESSION['action_type'] = "success";
    } else {
        $_SESSION['action_msg'] = "Failed to Delete Order";
        $_SESSION['action_type'] = "danger";
    }
    header("Location: orders.php");
    exit();
}