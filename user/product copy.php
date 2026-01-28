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

// --- Form Handling Logic (Kept exactly the same) ---
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #009444;
            --bg-dark: #050505;
            --text-main: #ffffff;
            --glass: rgba(20, 20, 20, 0.8);
            --border-color: rgba(255, 255, 255, 0.1);
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-dark);
            font-family: var(--font-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- Navbar (Same) --- */
        .navbar-custom { padding: 15px 0; background: transparent; position: absolute; width: 100%; z-index: 1000; transition: 0.4s; }
        .navbar-custom.scrolled { background: rgba(5, 5, 5, 0.95); backdrop-filter: blur(10px); padding: 10px 0; border-bottom: 1px solid var(--border-color); position: fixed; top: 0; }
        .navbar-brand, .nav-link { font-family: var(--font-head); text-transform: uppercase; }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; color: #fff; text-decoration: none; }
        .nav-link { color: rgba(255, 255, 255, 0.7) !important; font-weight: 600; position: relative; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .nav-link::after { content: ''; position: absolute; bottom: 5px; left: 0; width: 0%; height: 2px; background: var(--primary); transition: width 0.3s ease-in-out; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .dropdown-menu { background: #111; border: 1px solid var(--border-color); margin-top: 10px; }
        .dropdown-item { color: #ccc; padding: 10px 20px; transition: 0.2s; font-family: var(--font-head); text-transform: uppercase; }
        .dropdown-item:hover { background: var(--primary); color: white; padding-left: 25px; }
        .custom-toggler { border: none; background: transparent; padding: 5px; display: flex; flex-direction: column; gap: 6px; width: 40px; cursor: pointer; }
        .custom-toggler span { display: block; width: 100%; height: 3px; background: white; border-radius: 5px; transition: 0.4s; }
        .custom-toggler.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); background: var(--primary); }
        .custom-toggler.open span:nth-child(2) { opacity: 0; }
        .custom-toggler.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); background: var(--primary); }
        @media (max-width: 991px) { .navbar-collapse { background: #111; padding: 20px; border-radius: 15px; margin-top: 15px; border: 1px solid var(--border-color); } }

        /* --- IMMERSIVE PRODUCT LAYOUT --- */
        .showcase-wrapper {
            padding-top: 120px;
            position: relative;
        }

        /* Left Side: Scrolling Gallery */
        .gallery-container {
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding-bottom: 50px;
        }

        .gallery-img-box {
            background: #0f0f0f;
            border-radius: 30px;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .gallery-img-box img {
            max-height: 85%;
            max-width: 85%;
            object-fit: contain;
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.8));
            transition: transform 0.5s ease-out;
        }
        
        .gallery-img-box:hover img {
            transform: scale(1.05);
        }

        /* Right Side: Sticky Details */
        .details-sticky {
            position: sticky;
            top: 120px; /* Sticks 120px from top */
            height: fit-content;
            padding: 20px 0 20px 40px;
        }

        .category-tag {
            color: var(--primary);
            font-family: var(--font-head);
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: block;
        }

        .pro-title-big {
            font-size: 4rem;
            line-height: 0.95;
            font-weight: 800;
            font-family: var(--font-head);
            text-transform: uppercase;
            margin-bottom: 20px;
            background: linear-gradient(to bottom right, #fff, #888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .price-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .price-big {
            font-size: 2.5rem;
            color: #fff;
            font-weight: 700;
            font-family: var(--font-head);
        }

        .qty-selector {
            background: #111;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            display: flex;
            align-items: center;
            padding: 5px 15px;
        }

        .qty-btn {
            background: transparent; color: #fff; border: none; font-size: 1.2rem; cursor: pointer;
        }
        .qty-num {
            background: transparent; border: none; color: white; width: 40px; text-align: center; font-weight: bold; font-family: var(--font-head);
        }

        .desc-box {
            color: #aaa;
            line-height: 1.8;
            font-size: 1rem;
            margin-bottom: 40px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-add-cart-lg {
            background: var(--primary);
            color: white;
            border: none;
            padding: 20px;
            font-family: var(--font-head);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: 0.3s;
            width: 100%;
        }

        .btn-add-cart-lg:hover {
            background: #007a38;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 148, 68, 0.3);
        }

        .btn-wish-lg {
            background: transparent;
            color: white;
            border: 1px solid var(--border-color);
            padding: 15px;
            font-family: var(--font-head);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            border-radius: 12px;
            transition: 0.3s;
            width: 100%;
        }
        
        .btn-wish-lg:hover {
            border-color: #fff;
            background: rgba(255,255,255,0.05);
        }

        /* --- Tech Specs / Comments Section --- */
        .specs-section {
            background: #0a0a0a;
            padding: 80px 0;
            border-top: 1px solid var(--border-color);
            margin-top: 50px;
        }

        .section-head {
            font-size: 2rem;
            font-family: var(--font-head);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 40px;
            border-left: 4px solid var(--primary);
            padding-left: 20px;
        }

        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .review-card {
            background: #111;
            border: 1px solid var(--border-color);
            padding: 25px;
            border-radius: 16px;
        }

        .review-user {
            display: flex; align-items: center; gap: 15px; margin-bottom: 15px;
        }
        .user-initial {
            width: 40px; height: 40px; background: #222; color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 50%;
        }
        .review-text { color: #ccc; font-size: 0.9rem; line-height: 1.6; }

        .write-review-box {
            background: #151515;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
        }
        .comment-input {
            background: #000; border: 1px solid var(--border-color);
            color: #fff; padding: 15px; border-radius: 10px; width: 100%; margin-bottom: 15px;
        }
        .comment-input:focus { border-color: var(--primary); outline: none; }

        /* Similar Products Minimal */
        .similar-item {
            display: block; text-decoration: none; color: #fff;
            border: 1px solid transparent; padding: 15px; border-radius: 15px; transition: 0.3s;
        }
        .similar-item:hover { border-color: var(--border-color); background: #111; }
        .sim-img-wrap { height: 150px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .sim-img-wrap img { max-height: 100%; max-width: 100%; filter: grayscale(1); transition: 0.3s; }
        .similar-item:hover img { filter: grayscale(0); transform: scale(1.05); }

        /* Mobile Adjustments */
        @media (max-width: 991px) {
            .showcase-wrapper { padding-top: 100px; }
            .details-sticky { position: relative; top: 0; padding-left: 0; margin-top: 30px; }
            .gallery-img-box { height: 400px; }
            .pro-title-big { font-size: 2.5rem; }
        }

        /* Footer (Same) */
        footer { border-top: 1px solid var(--border-color); padding-top: 60px; margin-top: auto; background: #080808; }
        .footer-link { color: rgba(255, 255, 255, 0.5); text-decoration: none; display: block; margin-bottom: 10px; transition: 0.2s; font-family: var(--font-body); }
        .footer-link:hover { color: var(--primary); padding-left: 5px; }
        
        .toast-custom { position: fixed; top: 100px; right: 20px; z-index: 9999; background: var(--primary); color: white; border-radius: 8px; padding: 15px 25px; transform: translateX(150%); transition: 0.5s; }
        .toast-custom.show { transform: translateX(0); }
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
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Notification</h6>
                    <small><?php echo $_SESSION['flash_msg']; ?></small>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash_msg']); ?>
    <?php endif; ?>

    <div class="showcase-wrapper">
        <form method="POST">
            <div class="container-fluid px-lg-5">
                <div class="row">
                    
                    <div class="col-lg-7">
                        <div class="gallery-container">
                            <?php 
                                $imgs = explode(',', $product['product_image']);
                                foreach($imgs as $img): $img = trim($img); 
                            ?>
                                <div class="gallery-img-box gs_reveal">
                                    <img src="../admin/product_images/<?= $img; ?>" alt="Product View">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="details-sticky gs_reveal">
                            <span class="category-tag">Zyroid / <?= $current_category ?></span>
                            <h1 class="pro-title-big"><?= $product['product_name']; ?></h1>
                            
                            <div class="price-row">
                                <div class="price-big">$<span id="displayPrice"><?= $product['product_price']; ?></span></div>
                                <div class="qty-selector">
                                    <button type="button" class="qty-btn minus"><i class="bi bi-dash"></i></button>
                                    <input type="text" name="qty" id="qtyInput" class="qty-num" value="1" readonly>
                                    <button type="button" class="qty-btn plus"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>

                            <p class="desc-box"><?= $desc; ?></p>

                            <div class="action-buttons">
                                <button type="submit" name="add_to_cart" class="btn-add-cart-lg">
                                    Add to Cart <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" name="add_to_wishlist" class="btn-wish-lg">
                                    <i class="bi bi-heart me-2"></i> Save to Wishlist
                                </button>
                            </div>

                            <div class="mt-4 pt-4 border-top border-secondary border-opacity-10">
                                <div class="d-flex gap-4 text-white-50 small">
                                    <span><i class="bi bi-shield-check text-success me-1"></i> Official Warranty</span>
                                    <span><i class="bi bi-truck text-success me-1"></i> Free Shipping</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div class="specs-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <h3 class="section-head gs_reveal">Community Reviews</h3>
                    
                    <div class="write-review-box mb-5 gs_reveal">
                        <h5 class="text-white mb-3">Write a Review</h5>
                        <form method="POST">
                            <input type="text" name="commnts" class="comment-input" placeholder="Share your experience with this device...">
                            <button type="submit" name="cmt" class="btn btn-outline-light rounded-pill px-4">Submit Review</button>
                        </form>
                    </div>

                    <div class="review-grid">
                        <?php if (mysqli_num_rows($cmtshow) > 0) {
                            while ($cmt = mysqli_fetch_assoc($cmtshow)) { 
                                $initial = strtoupper(substr($cmt['user_name'], 0, 1));
                        ?>
                            <div class="review-card gs_reveal">
                                <div class="review-user">
                                    <div class="user-initial"><?= $initial ?></div>
                                    <div>
                                        <h6 class="text-white mb-0"><?= htmlspecialchars($cmt['user_name']); ?></h6>
                                        <small class="text-white-50"><?= $cmt['date']; ?></small>
                                    </div>
                                </div>
                                <p class="review-text"><?= htmlspecialchars($cmt['comment']); ?></p>
                            </div>
                        <?php } } else { ?>
                            <p class="text-white-50">No reviews yet.</p>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-lg-4 offset-lg-1 mt-5 mt-lg-0">
                    <h3 class="section-head gs_reveal">Similar Devices</h3>
                    <div class="d-flex flex-column gap-3">
                        <?php
                        $sim_query = "SELECT * FROM tb_products WHERE category = '$current_category' AND id != '$product_id' LIMIT 3";
                        $sim_run = mysqli_query($con, $sim_query);
                        while ($sim = mysqli_fetch_assoc($sim_run)) {
                            $sim_imgs = explode(',', $sim['product_image']);
                            $sim_thumb = trim($sim_imgs[0]);
                        ?>
                            <a href="product.php?id=<?= $sim['id']; ?>" class="similar-item gs_reveal">
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <div class="sim-img-wrap">
                                            <img src="../admin/product_images/<?= $sim_thumb; ?>" alt="Product">
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <h6 class="mb-1 fw-bold"><?= $sim['product_name']; ?></h6>
                                        <span class="text-success fw-bold">$<?= $sim['product_price']; ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
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
                    <form class="d-flex gap-2"><input type="email" class="form-control bg-transparent border-secondary text-white" placeholder="Email"><button class="btn btn-success rounded-pill px-3">Join</button></form>
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
            // Navbar Scroll
            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) { $('.navbar-custom').addClass('scrolled'); }
                else { $('.navbar-custom').removeClass('scrolled'); }
            });

            $('.custom-toggler').click(function () { $(this).toggleClass('open'); });

            // GSAP Animations
            gsap.registerPlugin(ScrollTrigger);
            
            gsap.utils.toArray('.gs_reveal').forEach((elem) => {
                gsap.from(elem, {
                    scrollTrigger: { trigger: elem, start: "top 90%" },
                    y: 50, opacity: 0, duration: 0.8, ease: "power3.out"
                });
            });

            // Flash Message
            setTimeout(() => { $('#flashToast').removeClass('show'); }, 3000);

            // Price Update Logic
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
        });
    </script>
</body>
</html>