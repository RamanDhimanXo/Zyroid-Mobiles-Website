<?php 
    session_start();
    include('../db_config.php');

    if(isset($_GET['did'])) {
        $id = mysqli_real_escape_string($con, $_GET['did']);

        $u_res = mysqli_query($con, "SELECT user, email FROM tb_users WHERE id='$id'");
        if(mysqli_num_rows($u_res) > 0) {
            $u_data = mysqli_fetch_assoc($u_res);
            $u_name = $u_data['user'];
            $u_email = $u_data['email'];

            $qr = "DELETE FROM `tb_users` WHERE `id` = '$id'"; 
            $res = mysqli_query($con, $qr);
            if($res){
                mysqli_query($con, "DELETE FROM tb_notifications WHERE user_id='$id' OR user_email='$u_email' OR (type='user' AND source_id='$id')");

                $notif_msg = "User '$u_name' has been deleted.";
                $notif_msg = mysqli_real_escape_string($con, $notif_msg);
                $notif_date = date('Y-m-d');
                $notif_type = 'user_delete';
                $notif_q = "INSERT INTO `tb_notifications` (`type`, `source_id`, `is_read`, `created_at`, `message`) VALUES ('$notif_type', '$id', '2', '$notif_date', '$notif_msg')";
                mysqli_query($con, $notif_q);

                $_SESSION['success'] = "User Deleted Successfully";
            }else{
                $_SESSION['error'] = "Failed to Delete";
            }
        } else {
            $_SESSION['error'] = "User not found";
        }
        header("Location: customers.php");
        exit();
    }
?>