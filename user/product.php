<?php
session_start();
include('../db_config.php');

$product_id = mysqli_real_escape_string($con, $_GET['id'] ?? '');
$res = mysqli_query($con, "SELECT * FROM tb_products WHERE id = '$product_id'");

if (mysqli_num_rows($res) == 0) {
    header("Location: ../index.php");
    exit();
}

$product = mysqli_fetch_assoc($res);
$desc = $product['product_des'] ?? "No description available.";
$current_category = $product['category'];

$in_wishlist = false;
if (isset($_SESSION['email'])) {
    $chk_email = $_SESSION['email'];
    $w_res = mysqli_query($con, "SELECT * FROM tb_wishlist WHERE email='$chk_email' AND wishlist='$product_id'");
    if(mysqli_num_rows($w_res) > 0){
        $in_wishlist = true;
    }
}

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['email'])) {
        echo "<script>alert('Please login to add items.'); window.location='login.php';</script>";
        exit();
    }
    $user_email = $_SESSION['email'];
    $qty = (int) ($_POST['qty'] ?? 1);
    $p_name = mysqli_real_escape_string($con, $product['product_name']);
    $p_price = $product['product_price'];
    $img_arr = explode(',', $product['product_image']);
    $p_img = trim($img_arr[0]);

    $check = mysqli_query($con, "SELECT * FROM tb_cart WHERE product_id = '$product_id' AND email = '$user_email'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($con, "UPDATE tb_cart SET quantity = quantity + $qty WHERE product_id = '$product_id' AND email = '$user_email'");
    } else {
        mysqli_query($con, "INSERT INTO tb_cart (email, product_id, product_name, product_price, product_image, quantity) VALUES ('$user_email', '$product_id', '$p_name', '$p_price', '$p_img', '$qty')");
    }
    $_SESSION['flash_msg'] = "Product added to cart!";
    header("Location: product.php?id=$product_id");
    exit();
}

if (isset($_POST['add_to_wishlist'])) {
    if (!isset($_SESSION['email'])) {
        echo "<script>alert('Please login.'); window.location='login.php';</script>";
        exit();
    }
    $user_email = $_SESSION['email'];
    $check_wish = mysqli_query($con, "SELECT * FROM tb_wishlist WHERE email = '$user_email' AND wishlist = '$product_id'");
    if (mysqli_num_rows($check_wish) == 0) {
        mysqli_query($con, "INSERT INTO tb_wishlist (email, wishlist) VALUES ('$user_email', '$product_id')");
        $_SESSION['flash_msg'] = "Added to wishlist!";
    } else {
        mysqli_query($con, "DELETE FROM tb_wishlist WHERE email = '$user_email' AND wishlist = '$product_id'");
        $_SESSION['flash_msg'] = "Removed from wishlist!";
    }
    header("Location: product.php?id=$product_id");
    exit();
}

if (isset($_POST['cmt'])) {
    if (!isset($_SESSION['email'])) {
        echo "<script>alert('Login required.'); window.location='login.php';</script>";
        exit();
    }
    $user_name = $_SESSION['user'];
    $comment_text = mysqli_real_escape_string($con, $_POST['commnts']);
    $date = date('Y-m-d');
    mysqli_query($con, "INSERT INTO `tb_comments` (`product_id`, `user_name`, `comment`, `date`) VALUES ('$product_id', '$user_name', '$comment_text', '$date')");
    header("Location: product.php?id=$product_id");
    exit();
}

$cmtshow = mysqli_query($con, "SELECT * FROM `tb_comments` WHERE `product_id` = '$product_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?> - Zyroid</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #009444;
            --bg-dark: #1a1a1a;
            --text-main: #ffffff;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 0%, #1e3c2e 0%, #1a1a1a 40%);
            font-family: var(--font-body);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6, .navbar-brand, .btn-theme, .nav-link, .dropdown-item {
            font-family: var(--font-head);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .navbar-custom { padding: 15px 0; background: transparent; position: absolute; width: 100%; z-index: 1000; transition: 0.4s; }
        .navbar-custom.scrolled { background: rgba(26, 26, 26, 0.95); backdrop-filter: blur(10px); padding: 10px 0; border-bottom: 1px solid var(--glass-border); position: fixed; top: 0; }
        .nav-link { color: rgba(255, 255, 255, 0.8) !important; font-weight: 600; position: relative; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .nav-link::after { content: ''; position: absolute; bottom: 5px; left: 0; width: 0%; height: 2px; background: var(--primary); transition: width 0.3s ease-in-out; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .dropdown-menu { background: rgba(10, 10, 10, 0.95); border: 1px solid rgba(255, 255, 255, 0.15); margin-top: 10px; }
        .dropdown-item { color: rgba(255, 255, 255, 0.8); padding: 10px 20px; transition: 0.2s; }
        .dropdown-item:hover { background: var(--primary); color: white; padding-left: 25px; }
        .custom-toggler { border: none; background: transparent; padding: 5px; display: flex; flex-direction: column; gap: 6px; width: 40px; cursor: pointer; }
        .custom-toggler span { display: block; width: 100%; height: 3px; background: white; border-radius: 5px; transition: 0.4s; }
        .custom-toggler.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); background: var(--primary); }
        .custom-toggler.open span:nth-child(2) { opacity: 0; }
        .custom-toggler.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); background: var(--primary); }
        @media (max-width: 991px) { .navbar-collapse { background: rgba(26, 26, 26, 0.98); backdrop-filter: blur(15px); padding: 20px; border-radius: 15px; margin-top: 15px; border: 1px solid rgba(255, 255, 255, 0.1); } }

        .content-wrapper { padding-top: 130px; padding-bottom: 50px; flex: 1; }

        .reveal-element {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                        transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .reveal-element.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .img-display-area {
            position: relative;
            height: 450px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 20px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
            margin-bottom: 20px;
            overflow: hidden;
            cursor: zoom-in;
            border: 1px solid var(--glass-border);
        }
        
        .img-display-area img {
            max-height: 90%; max-width: 90%; object-fit: contain;
            transition: transform 0.1s ease-out;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));
        }

        .thumb-group {
            display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px;
            scrollbar-width: none;
        }
        .thumb-group::-webkit-scrollbar { display: none; }

        .thumb-item {
            width: 70px; height: 70px; flex-shrink: 0;
            border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.02); padding: 5px; cursor: pointer; transition: 0.3s;
        }
        .thumb-item.active, .thumb-item:hover {
            border-color: var(--primary); background: rgba(0, 148, 68, 0.1); transform: translateY(-3px);
        }
        .thumb-item img { width: 100%; height: 100%; object-fit: contain; }

        .badge-stock {
            display: inline-block;
            background: rgba(0, 148, 68, 0.2); color: #00ff73;
            padding: 5px 12px; border-radius: 50px;
            font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
            border: 1px solid rgba(0, 148, 68, 0.3); margin-bottom: 10px;
        }

        .product-title-lg {
            font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; line-height: 1.1;
        }
        
        .product-price-lg {
            font-size: 2rem; color: var(--primary); font-weight: 700; font-family: var(--font-head); margin-bottom: 20px;
        }

        .desc-text {
            color: rgba(255,255,255,0.7); line-height: 1.7; font-size: 0.95rem; margin-bottom: 30px;
        }

        .qty-wrapper {
            display: flex; align-items: center; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; padding: 5px; width: fit-content;
        }
        .qty-btn {
            width: 40px; height: 40px; background: transparent; border: none;
            color: white; font-size: 1.2rem; cursor: pointer; transition: 0.2s;
        }
        .qty-btn:hover { color: var(--primary); }
        .qty-input {
            width: 50px; background: transparent; border: none;
            text-align: center; color: white; font-weight: bold; font-size: 1.1rem;
        }

        .btn-add-cart {
            background: var(--primary); color: white; border: none; border-radius: 50px;
            padding: 12px 30px; font-family: var(--font-head); font-weight: 700; font-size: 1rem;
            text-transform: uppercase; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%;
        }
        .btn-add-cart:hover {
            box-shadow: 0 0 20px rgba(0, 148, 68, 0.4); transform: translateY(-2px); background: #007a38;
        }

        .btn-wishlist {
            width: 50px; height: 50px; background: transparent;
            border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: 0.3s;
            flex-shrink: 0;
        }
        .btn-wishlist:hover {
            border-color: #ff4757; color: #ff4757; background: rgba(255, 71, 87, 0.1);
        }
        .btn-wishlist.active {
            color: #ff4757;
            border-color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
        }
        .btn-wishlist.active i {
            animation: heartBeat 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }

        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .comment-input {
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
            color: white; border-radius: 50px; padding: 12px 20px; width: 100%; transition: 0.3s;
        }
        .comment-input:focus { outline: none; border-color: var(--primary); }
        
        .user-comment {
            border-bottom: 1px solid rgba(255,255,255,0.05); padding: 15px 0; display: flex; gap: 15px;
        }
        .user-comment:last-child { border-bottom: none; }
        .comment-avatar {
            width: 45px; height: 45px; background: linear-gradient(135deg, var(--primary), #004d26);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1rem; border: 2px solid rgba(255,255,255,0.1); flex-shrink: 0;
        }

        .product-card-v2 {
            display: block; text-decoration: none;
            background: linear-gradient(145deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 20px;
            position: relative; overflow: hidden; transition: all 0.4s ease; height: 100%;
        }
        .product-card-v2:hover {
            border-color: rgba(0, 148, 68, 0.5);
            background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%);
            transform: translateY(-5px);
        }
        .img-wrapper-sm {
            height: 180px; display: flex; align-items: center; justify-content: center; margin: 15px 0; transition: transform 0.4s ease;
        }
        .product-card-v2:hover .img-wrapper-sm { transform: scale(1.08); }
        .img-wrapper-sm img { max-height: 100%; max-width: 100%; filter: drop-shadow(0 15px 15px rgba(0,0,0,0.5)); object-fit: contain; }
        
        .btn-arrow {
            width: 35px; height: 35px; border-radius: 50%; background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center; color: white;
            border: 1px solid rgba(255,255,255,0.1); transition: 0.3s;
        }
        .product-card-v2:hover .btn-arrow { background: var(--primary); border-color: var(--primary); transform: rotate(-45deg); }

        .toast-custom {
            position: fixed; top: 100px; right: 20px; z-index: 9999;
            background: rgba(0, 148, 68, 0.95); backdrop-filter: blur(5px);
            color: white; border-radius: 10px; padding: 15px 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transform: translateX(150%); transition: 0.5s ease-out;
            display: flex; align-items: center; gap: 15px;
        }
        .toast-custom.show { transform: translateX(0); }

        @media (max-width: 991px) {
            .img-display-area { height: 350px; }
            .product-title-lg { font-size: 2rem; }
            .col-lg-6 { margin-bottom: 30px; }
        }

        footer { border-top: 1px solid var(--glass-border); padding-top: 60px; margin-top: 100px; background: rgba(0, 0, 0, 0.3); }
        .footer-link { color: rgba(255, 255, 255, 0.5); text-decoration: none; display: block; margin-bottom: 10px; transition: 0.2s; font-family: var(--font-body); }
        .footer-link:hover { color: var(--primary); padding-left: 5px; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand text-white fw-bold fs-3" href="../index.php">
                Zyroid<span style="color: var(--primary)">.</span>
            </a>
            <button class="navbar-toggler custom-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span></span><span></span><span></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto gap-3 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Mobiles</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="category.php?cat=all">All Mobiles</a></li>
                            <?php
                            $nav_cat_qr = "SELECT * FROM tb_categories";
                            $nav_cat_res = mysqli_query($con, $nav_cat_qr);
                            if ($nav_cat_res) { while ($nav_row = mysqli_fetch_assoc($nav_cat_res)) {
                                echo '<li><a class="dropdown-item" href="category.php?cat=' . urlencode($nav_row['cat_name']) . '">' . $nav_row['cat_name'] . '</a></li>';
                            }} ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <?php if (isset($_SESSION['user'])) { ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 35px; height: 35px;">
                                    <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
                                </div>
                                <span><?php echo $_SESSION['user']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li><form method="post"><button type="submit" name="logout" class="dropdown-item text-danger">Logout</button></form></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item"><a href="login.php" class="btn-theme ms-2">Sign In</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="toast-custom show" id="flashToast">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div>
                <h6 class="mb-0 fw-bold">Notification</h6>
                <small><?php echo $_SESSION['flash_msg']; ?></small>
            </div>
        </div>
        <?php unset($_SESSION['flash_msg']); ?>
    <?php endif; ?>

    <div class="content-wrapper">
        <div class="container">
            
            <nav aria-label="breadcrumb" class="mb-4 reveal-element">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?cat=<?= $current_category ?>" class="text-white-50 text-decoration-none"><?= $current_category ?></a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"><?= $product['product_name'] ?></li>
                </ol>
            </nav>

            <form method="POST">
                <div class="row g-5">
                    <div class="col-lg-6 reveal-element" style="transition-delay: 0.1s;">
                        <div class="glass-panel p-3">
                            <?php 
                                $imgs = explode(',', $product['product_image']);
                                $main_img = trim($imgs[0]);
                            ?>
                            <div class="img-display-area" id="zoomArea">
                                <img src="../admin/product_images/<?= $main_img; ?>" id="mainImg" alt="Product">
                            </div>
                            <div class="thumb-group">
                                <?php foreach($imgs as $index => $img): $img = trim($img); ?>
                                    <div class="thumb-item <?= ($index === 0) ? 'active' : ''; ?>" onclick="switchImage(this, '../admin/product_images/<?= $img; ?>')">
                                        <img src="../admin/product_images/<?= $img; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 reveal-element" style="transition-delay: 0.2s;">
                        <div class="glass-panel justify-content-center">
                            <div>
                                <span class="badge-stock">In Stock</span>
                                <h1 class="product-title-lg"><?= $product['product_name']; ?></h1>
                                <div class="product-price-lg mb-3">$<span id="displayPrice"><?= $product['product_price']; ?></span></div>
                                
                                <p class="desc-text"><?= $desc; ?></p>

                                <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
                                    <div class="col-auto">
                                        <label class="d-block text-white-50 small fw-bold mb-1 text-uppercase">Quantity</label>
                                        <div class="qty-wrapper">
                                            <button type="button" class="qty-btn minus"><i class="bi bi-dash"></i></button>
                                            <input type="number" name="qty" id="qtyInput" class="qty-input" value="1" readonly>
                                            <button type="button" class="qty-btn plus"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="col flex-grow-1">
                                        <label class="d-block text-white-50 small fw-bold mb-1 text-uppercase">&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="add_to_cart" class="btn-add-cart">
                                                <i class="bi bi-cart3"></i> Add to Cart
                                            </button>
                                            <button type="submit" name="add_to_wishlist" class="btn-wishlist <?php echo $in_wishlist ? 'active' : ''; ?>">
                                                <i class="bi <?php echo $in_wishlist ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row mt-5">
                <div class="col-12 reveal-element" style="transition-delay: 0.3s;">
                    <div class="glass-panel">
                        <div class="comment-header">
                            <h3 class="mb-0 text-white" style="font-family: var(--font-head);">Reviews</h3>
                            <span class="badge bg-secondary rounded-pill"><?= mysqli_num_rows($cmtshow) ?> Reviews</span>
                        </div>

                        <form method="POST" class="mb-4">
                            <div class="d-flex gap-2">
                                <input type="text" name="commnts" class="comment-input" placeholder="Write a review...">
                                <button type="submit" name="cmt" class="btn-theme" style="padding: 0 25px; border-radius: 50px;">Post</button>
                            </div>
                        </form>

                        <div class="comments-list">
                            <?php if (mysqli_num_rows($cmtshow) > 0) {
                                while ($cmt = mysqli_fetch_assoc($cmtshow)) { 
                                    $initial = strtoupper(substr($cmt['user_name'], 0, 1));
                            ?>
                                <div class="user-comment">
                                    <div class="comment-avatar"><?= $initial ?></div>
                                    <div>
                                        <h6 class="text-white mb-1 fw-bold"><?= htmlspecialchars($cmt['user_name']); ?> <small class="text-white-50 fw-normal ms-2" style="font-size: 0.75rem;"><?= $cmt['date']; ?></small></h6>
                                        <p class="text-white-50 mb-0 small"><?= htmlspecialchars($cmt['comment']); ?></p>
                                    </div>
                                </div>
                            <?php } } else { ?>
                                <div class="text-center py-4 text-white-50">
                                    <i class="bi bi-chat-square-dots fs-1 d-block mb-3 opacity-25"></i>
                                    <p>No comments yet. Be the first!</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4">
                <div class="d-flex justify-content-between align-items-center mb-4 reveal-element" style="transition-delay: 0.4s;">
                    <h3 class="text-white text-uppercase fw-bold m-0" style="font-family: var(--font-head);">You May Also Like</h3>
                    <a href="category.php?cat=<?= $current_category ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">View All</a>
                </div>
                
                <div class="row g-4">
                    <?php
                    $sim_query = "SELECT * FROM tb_products WHERE category = '$current_category' AND id != '$product_id' LIMIT 4";
                    $sim_run = mysqli_query($con, $sim_query);
                    if (mysqli_num_rows($sim_run) > 0) {
                        $delay = 0.5;
                        while ($sim = mysqli_fetch_assoc($sim_run)) {
                            $sim_imgs = explode(',', $sim['product_image']);
                            $sim_thumb = trim($sim_imgs[0]);
                            ?>
                            <div class="col-md-6 col-lg-3 reveal-element" style="transition-delay: <?= $delay ?>s;">
                                <a href="product.php?id=<?= $sim['id']; ?>" class="product-card-v2">
                                    <div class="img-wrapper-sm">
                                        <img src="../admin/product_images/<?= $sim_thumb; ?>" alt="<?= $sim['product_name'] ?>">
                                    </div>
                                    <div class="card-info">
                                        <h5 class="card-title text-white mb-2 text-truncate" style="font-weight:700;"><?= $sim['product_name'] ?></h5>
                                        <div class="card-bottom d-flex justify-content-between align-items-center">
                                            <div class="price-text">$<?= $sim['product_price']; ?></div>
                                            <div class="btn-arrow"><i class="bi bi-arrow-right"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                            $delay += 0.1;
                        }
                    } else {
                        echo "<div class='col-12 text-center text-white-50 reveal-element'>No similar products found.</div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <footer>
        <div class="container pb-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white fw-bold fs-3 mb-3 d-block" href="../index.php">Zyroid<span style="color: var(--primary)">.</span></a>
                    <p class="text-white-50 small">Your trusted partner for latest smartphones.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold text-white mb-3">Mobiles</h6>
                    <a href="category.php?cat=iphones" class="footer-link">iPhones</a>
                    <a href="category.php?cat=android" class="footer-link">Android</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold text-white mb-3">Help</h6>
                    <a href="my-orders.php" class="footer-link">Track Order</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold text-white mb-3">Offers</h6>
                    <form class="d-flex gap-2"><input type="email" class="form-control bg-transparent border-secondary text-white" placeholder="Email"><button class="btn-theme px-3">Join</button></form>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary border-opacity-10 text-white-50 small">&copy; 2025 Zyroid Mobiles. All rights reserved.</div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('scroll', () => {
                const nav = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            });

            const toggler = document.querySelector('.custom-toggler');
            if(toggler) {
                toggler.addEventListener('click', () => toggler.classList.toggle('open'));
            }

            const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal-element').forEach(el => observer.observe(el));

            const flash = document.getElementById('flashToast');
            if(flash) {
                setTimeout(() => { flash.classList.remove('show'); }, 3000);
            }

            const basePrice = <?= $product['product_price']; ?>;
            const qtyInput = document.getElementById('qtyInput');
            const displayPrice = document.getElementById('displayPrice');

            document.querySelector('.plus').addEventListener('click', (e) => {
                e.preventDefault();
                let val = parseInt(qtyInput.value);
                qtyInput.value = val + 1;
                updatePrice(val + 1);
            });

            document.querySelector('.minus').addEventListener('click', (e) => {
                e.preventDefault();
                let val = parseInt(qtyInput.value);
                if (val > 1) {
                    qtyInput.value = val - 1;
                    updatePrice(val - 1);
                }
            });

            function updatePrice(qty) {
                let newTotal = (basePrice * qty).toFixed(2);
                displayPrice.style.opacity = 0;
                setTimeout(() => {
                    displayPrice.innerText = newTotal;
                    displayPrice.style.opacity = 1;
                }, 150);
            }

            const zoomArea = document.getElementById('zoomArea');
            const mainImg = document.getElementById('mainImg');

            if(zoomArea && mainImg) {
                zoomArea.addEventListener('mousemove', function(e) {
                    const rect = zoomArea.getBoundingClientRect();
                    const x = (e.clientX - rect.left) / rect.width;
                    const y = (e.clientY - rect.top) / rect.height;
                    
                    const moveX = -(x - 0.5) * 50; 
                    const moveY = -(y - 0.5) * 50; 

                    mainImg.style.transform = `scale(1.5) translate(${moveX}px, ${moveY}px)`;
                });

                zoomArea.addEventListener('mouseleave', function() {
                    mainImg.style.transform = 'scale(1) translate(0,0)';
                });
            }
        });

        function switchImage(el, src) {
            const mainImg = document.getElementById('mainImg');
            mainImg.style.opacity = 0;
            
            setTimeout(() => {
                mainImg.src = src;
                mainImg.style.opacity = 1;
            }, 150);

            document.querySelectorAll('.thumb-item').forEach(item => item.classList.remove('active'));
            el.closest('.thumb-item').classList.add('active');
        }
    </script>
</body>
</html>