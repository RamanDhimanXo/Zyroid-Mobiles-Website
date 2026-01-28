<?php 
    session_start();
    include('../db_config.php');

    if(isset($_GET['did'])) {
        $id = mysqli_real_escape_string($con, $_GET['did']);

        $p_res = mysqli_query($con, "SELECT product_name FROM tb_products WHERE id='$id'");
        if(mysqli_num_rows($p_res) > 0) {
            $p_data = mysqli_fetch_assoc($p_res);
            $p_name = $p_data['product_name'];

            $qr = "DELETE FROM `tb_products` WHERE `id` = '$id'"; 
            $res = mysqli_query($con, $qr);
            if($res){
                $notif_msg = "Product '$p_name' has been deleted.";
                $notif_msg = mysqli_real_escape_string($con, $notif_msg);
                $notif_date = date('Y-m-d');
                $notif_type = 'product_delete';
                $notif_q = "INSERT INTO `tb_notifications` (`type`, `source_id`, `is_read`, `created_at`, `message`) VALUES ('$notif_type', '$id', '2', '$notif_date', '$notif_msg')";
                mysqli_query($con, $notif_q);
                $_SESSION['success'] = "Product Deleted Successfully";
                header("Location: latest_products.php");
                exit();
            }else{
                $_SESSION['error'] = "Failed to Delete";
                header("Location: latest_products.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Product not found";
            header("Location: latest_products.php");
            exit();
        }
    }

?>