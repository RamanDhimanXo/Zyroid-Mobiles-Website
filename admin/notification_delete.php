<?php
session_start();
include('../db_config.php');

if(isset($_GET['ajax'])) {
    header('Content-Type: application/json');
}

if(!isset($_SESSION['admin_email'])){
    if(isset($_GET['ajax'])) { 
        echo json_encode(['success'=>false, 'message'=>'Unauthorized']); 
        exit; 
    }
    header("location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $qr = "DELETE FROM `tb_notifications` WHERE `id` = '$id'"; 
        $result = mysqli_query($con, $qr);
        
        if ($result && !isset($_GET['ajax'])) {
            $_SESSION['success'] = "Notification Deleted Successfully";
        } elseif (!$result && !isset($_GET['ajax'])) {
            $_SESSION['error'] = "Failed to delete notification";
        }

        if(isset($_GET['ajax'])) {
            if($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            exit();
        }
    }
}

if(isset($_GET['ajax'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit();
}

header("Location: notifications.php");
exit();
?>