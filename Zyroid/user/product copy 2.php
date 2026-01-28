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

// --- Form Handling (Cart, Wishlist, Comment) ---
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
        $_SESSION['flash_msg'] = "Already in wishlist!";
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
            --transition: all 0.3s ease-in-out;
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

        /* --- Navbar Styles (Category Page Style) --- */
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

        /* --- Product Layout --- */
        .content-wrapper { padding-top: 130px; padding-bottom: 50px; flex: 1; }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 30px;
            backdrop-filter: blur(10px);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        /* Image Viewer */
        .img-display-area {
            position: relative;
            height: 400px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 20px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
            margin-bottom: 20px; cursor: crosshair;
        }
        .img-display-area img {
            max-height: 90%; max-width: 90%; object-fit: contain;
            transition: transform 0.1s ease-out; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));
        }
        .thumb-scroll {
            display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none;
        }
        .thumb-scroll::-webkit-scrollbar { display: none; }
        .thumb-item {
            width: 70px; height: 70px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.02); padding: 5px; cursor: pointer; transition: 0.3s;
        }
        .thumb-item.active, .thumb-item:hover {
            border-color: var(--primary); background: rgba(0, 148, 68, 0.1); transform: translateY(-3px);
        }
        .thumb-item img { width: 100%; height: 100%; object-fit: contain; }

        /* Product Info */
        .badge-stock {
            background: rgba(0, 148, 68, 0.2); color: #00ff73; padding: 5px 12px;
            border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
            border: 1px solid rgba(0, 148, 68, 0.3); display: inline-block; margin-bottom: 10px;
        }
        .product-title-lg { font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; line-height: 1.1; }
        .product-price-lg { font-size: 2rem; color: var(--primary); font-weight: 700; font-family: var(--font-head); margin-bottom: 20px; }
        .desc-text { color: rgba(255,255,255,0.7); line-height: 1.6; margin-bottom: 30px; font-size: 0.95rem; }

        .qty-wrapper {
            display: flex; align-items: center; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; padding: 5px; width: fit-content;
        }
        .qty-btn { width: 40px; height: 40px; background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; }
        .qty-input { width: 50px; background: transparent; border: none; text-align: center; color: white; font-weight: bold; font-size: 1.1rem; }

        .btn-add-cart {
            background: var(--primary); color: white; border: none; border-radius: 50px;
            padding: 12px 30px; font-family: var(--font-head); font-weight: 700; font-size: 1rem;
            text-transform: uppercase; transition: 0.3s; display: flex; align-items: center; gap: 10px; flex-grow: 1; justify-content: center;
        }
        .btn-add-cart:hover { box-shadow: 0 0 20px rgba(0, 148, 68, 0.4); transform: translateY(-2px); }
        
        .btn-wishlist {
            width: 50px; height: 50px; background: transparent; border: 1px solid rgba(255,255,255,0.2);
            color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; transition: 0.3s;
        }
        .btn-wishlist:hover { border-color: #ff4757; color: #ff4757; background: rgba(255, 71, 87, 0.1); }

        /* Comments */
        .comment-input { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 50px; padding: 12px 20px; width: 100%; transition: 0.3s; }
        .comment-input:focus { outline: none; border-color: var(--primary); }
        .user-comment { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 15px 0; display: flex; gap: 15px; }
        .user-comment:last-child { border-bottom: none; }
        .comment-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), #004d26); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid rgba(255,255,255,0.1); font-size: 0.9rem; flex-shrink: 0; }

        /* Similar Products (Copied Style) */
        .product-card-v2 {
            display: block; text-decoration: none;
            background: linear-gradient(145deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 20px; position: relative; overflow: hidden;
            transition: all 0.4s; height: 100%;
        }
        .product-card-v2:hover { border-color: rgba(0, 148, 68, 0.5); background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%); transform: translateY(-5px); }
        .img-wrapper-sm { height: 180px; display: flex; align-items: center; justify-content: center; margin: 15px 0; transition: transform 0.4s ease; }
        .product-card-v2:hover .img-wrapper-sm { transform: scale(1.08); }
        .img-wrapper-sm img { max-height: 100%; max-width: 100%; filter: drop-shadow(0 15px 15px rgba(0,0,0,0.5)); object-fit: contain; }
        .btn-arrow { width: 35px; height: 35px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: white; border: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
        .product-card-v2:hover .btn-arrow { background: var(--primary); border-color: var(--primary); transform: rotate(-45deg); }

        /* Toast */
        .toast-custom { position: fixed; top: 100px; right: 20px; z-index: 9999; background: rgba(0, 148, 68, 0.95); backdrop-filter: blur(5px); color: white; border-radius: 10px; padding: 15px 25px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); transform: translateX(150%); transition: 0.5s; display: flex; align-items: center; gap: 15px; }
        .toast-custom.show { transform: translateX(0); }

        /* Footer */
        footer { border-top: 1px solid var(--glass-border); padding-top: 60px; margin-top: auto; background: rgba(0, 0, 0, 0.3); }
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
                            if ($nav_cat_res) {
                                while ($nav_row = mysqli_fetch_assoc($nav_cat_res)) {
                                    echo '<li><a class="dropdown-item" href="category.php?cat=' . urlencode($nav_row['cat_name']) . '">' . $nav_row['cat_name'] . '</a></li>';
                                }
                            }
                            ?>
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
            
            <nav aria-label="breadcrumb" class="mb-4 gs_reveal">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?cat=<?= $current_category ?>" class="text-white-50 text-decoration-none"><?= $current_category ?></a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"><?= $product['product_name'] ?></li>
                </ol>
            </nav>

            <form method="POST">
                <div class="row g-5">
                    <div class="col-lg-6 gs_reveal">
                        <div class="glass-panel p-3">
                            <?php 
                                $imgs = explode(',', $product['product_image']);
                                $main_img = trim($imgs[0]);
                            ?>
                            <div class="img-display-area" id="zoomArea">
                                <img src="../admin/product_images/<?= $main_img; ?>" id="mainImg" alt="Product">
                            </div>
                            <div class="thumb-scroll">
                                <?php foreach($imgs as $index => $img): $img = trim($img); ?>
                                    <div class="thumb-item <?= ($index === 0) ? 'active' : ''; ?>" onclick="switchImage(this, '../admin/product_images/<?= $img; ?>')">
                                        <img src="../admin/product_images/<?= $img; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 gs_reveal">
                        <div class="glass-panel">
                            <span class="badge-stock">In Stock</span>
                            <h1 class="product-title-lg"><?= $product['product_name']; ?></h1>
                            <div class="product-price-lg mb-3">$<span id="displayPrice"><?= $product['product_price']; ?></span></div>
                            
                            <p class="desc-text"><?= $desc; ?></p>

                            <div class="d-flex flex-wrap align-items-center gap-3">
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
                                        <button type="submit" name="add_to_wishlist" class="btn-wishlist">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row mt-5">
                <div class="col-12 gs_reveal">
                    <div class="glass-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="mb-0 text-white" style="font-family: var(--font-head);">Reviews</h3>
                            <span class="badge bg-secondary"><?= mysqli_num_rows($cmtshow) ?></span>
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
                                <p class="text-center text-white-50 py-3">No reviews yet.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4">
                <div class="d-flex justify-content-between align-items-center mb-4 gs_reveal">
                    <h3 class="text-white text-uppercase fw-bold m-0" style="font-family: var(--font-head);">You May Also Like</h3>
                    <a href="category.php?cat=<?= $current_category ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">View All</a>
                </div>
                
                <div class="row g-4">
                    <?php
                    $sim_query = "SELECT * FROM tb_products WHERE category = '$current_category' AND id != '$product_id' LIMIT 4";
                    $sim_run = mysqli_query($con, $sim_query);
                    if (mysqli_num_rows($sim_run) > 0) {
                        while ($sim = mysqli_fetch_assoc($sim_run)) {
                            $sim_imgs = explode(',', $sim['product_image']);
                            $sim_thumb = trim($sim_imgs[0]);
                            ?>
                            <div class="col-md-6 col-lg-3 gs_reveal_card">
                                <a href="product.php?id=<?= $sim['id']; ?>" class="product-card-v2">
                                    <div class="img-glow"></div>
                                    <div class="card-header-top">
                                        <span class="badge-pill"><?= $sim['category'] ?></span>
                                    </div>
                                    <div class="img-wrapper-sm">
                                        <img src="../admin/product_images/<?= $sim_thumb; ?>" alt="<?= $sim['product_name'] ?>">
                                    </div>
                                    <div class="card-info">
                                        <h5 class="card-title"><?= $sim['product_name'] ?></h5>
                                        <div class="card-bottom">
                                            <div class="price-text">$<?= $sim['product_price']; ?></div>
                                            <div class="btn-arrow"><i class="bi bi-arrow-right"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='col-12 text-center text-white-50 gs_reveal'>No similar products found.</div>";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <script>
        $(document).ready(function () {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) { $('.navbar-custom').addClass('scrolled'); }
                else { $('.navbar-custom').removeClass('scrolled'); }
            });

            $('.custom-toggler').click(function () { $(this).toggleClass('open'); });

            // GSAP Animations
            gsap.registerPlugin(ScrollTrigger);
            
            // Staggered reveal for main elements
            gsap.from(".gs_reveal", {
                y: 50, opacity: 0, duration: 0.8, stagger: 0.15, ease: "power3.out"
            });

            // Reveal cards on scroll
            gsap.utils.toArray('.gs_reveal_card').forEach((elem, i) => {
                gsap.from(elem, {
                    scrollTrigger: { trigger: elem, start: "top 95%" },
                    y: 30, opacity: 0, duration: 0.6, delay: i * 0.1, ease: "power2.out"
                });
            });

            // Flash Message Auto Hide
            setTimeout(() => { $('#flashToast').removeClass('show'); }, 3000);

            // Price & Qty Logic
            const basePrice = <?= $product['product_price']; ?>;
            $('.plus').click(function () {
                let val = parseInt($('#qtyInput').val());
                $('#qtyInput').val(val + 1);
                updatePrice(val + 1);
            });
            $('.minus').click(function () {
                let val = parseInt($('#qtyInput').val());
                if (val > 1) {
                    $('#qtyInput').val(val - 1);
                    updatePrice(val - 1);
                }
            });
            function updatePrice(qty) {
                let newTotal = (basePrice * qty).toFixed(2);
                $('#displayPrice').fadeOut(100, function () { $(this).text(newTotal).fadeIn(100); });
            }

            // Image Zoom Effect
            const $zoomArea = $('#zoomArea');
            const $img = $('#mainImg');
            $zoomArea.on('mousemove', function (e) {
                const { left, top, width, height } = this.getBoundingClientRect();
                const x = (e.clientX - left) / width;
                const y = (e.clientY - top) / height;
                $img.css('transform', `scale(1.5) translate(${-(x - 0.5) * 50}px, ${-(y - 0.5) * 50}px)`);
            });
            $zoomArea.on('mouseleave', function () { $img.css('transform', 'scale(1) translate(0,0)'); });
        });

        function switchImage(el, src) {
            $('#mainImg').attr('src', src);
            $('.thumb-item').removeClass('active');
            $(el).addClass('active');
        }
    </script>
</body>
</html>