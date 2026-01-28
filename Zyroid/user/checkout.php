<?php
session_start();
include('../db_config.php');

if (!isset($_SESSION['email'])) {
    header("location: login.php");
    exit();
}

$user_email = $_SESSION['email'];

$stmt = $con->prepare("SELECT token FROM tb_users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$tok_row = $stmt->get_result()->fetch_assoc();

if (!$tok_row || !isset($_SESSION['token']) || $tok_row['token'] != $_SESSION['token']) {
    session_destroy();
    header("location: login.php");
    exit();
}

$stmt = $con->prepare("SELECT id FROM tb_cart WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
if ($stmt->get_result()->num_rows == 0) {
    header("location: cart.php");
    exit();
}

$order_success = false;
$new_order_id = "";

$stmt = $con->prepare("SELECT * FROM tb_cart WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$cart_res = $stmt->get_result();

$cart_items = [];
$subtotal = 0;
while ($row = $cart_res->fetch_assoc()) {
    $cart_items[] = $row;
    $subtotal += ($row['product_price'] * $row['quantity']);
}
$tax = $subtotal * 0.05;
$total = $subtotal + $tax;

if (isset($_POST['place_order'])) {
    if (count($cart_items) > 0) {
        $fullname = $_POST['fullname'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip = $_POST['zip'];

        $new_order_id = "ZYR-" . rand(10000, 99999);
        $date = date("Y-m-d");
        $prod_list = "";
        foreach ($cart_items as $item) {
            $prod_list .= $item['product_name'] . " (x" . $item['quantity'] . "), ";
        }
        $prod_list = rtrim($prod_list, ", ");

        $stmt_ins = $con->prepare("INSERT INTO tb_orders (order_id, Product, Date, amount, status, email, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip) VALUES (?, ?, ?, ?, '1', ?, ?, ?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("sssssssssss", $new_order_id, $prod_list, $date, $total, $user_email, $fullname, $phone, $address, $city, $state, $zip);

        if ($stmt_ins->execute()) {
            $stmt_u = $con->prepare("SELECT id FROM tb_users WHERE email = ?");
            $stmt_u->bind_param("s", $user_email);
            $stmt_u->execute();
            $u_row = $stmt_u->get_result()->fetch_assoc();
            $u_id = $u_row ? $u_row['id'] : '0';

            $notif_msg = "New Order #$new_order_id placed by $fullname";
            $notif_date = date('Y-m-d');

            $stmt_n = $con->prepare("INSERT INTO tb_notifications (user_id, type, source_id, is_read, created_at, message, user_email) VALUES (?, 'order', ?, '2', ?, ?, ?)");
            $stmt_n->bind_param("sssss", $u_id, $new_order_id, $notif_date, $notif_msg, $user_email);
            $stmt_n->execute();

            $stmt_del = $con->prepare("DELETE FROM tb_cart WHERE email = ?");
            $stmt_del->bind_param("s", $user_email);
            $stmt_del->execute();

            $order_success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Zyroid</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">

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
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .btn-theme,
        .nav-link,
        .total-row {
            font-family: var(--font-head);
            text-transform: uppercase;
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

        .page-header {
            padding-top: 130px;
            padding-bottom: 30px;
        }

        .checkout-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 30px;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 8px;
            padding: 12px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 148, 68, 0.2);
            color: white;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 10px;
        }

        .order-item:last-of-type {
            border-bottom: none;
        }

        .total-row {
            border-top: 1px solid var(--glass-border);
            padding-top: 15px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
        }

        .btn-theme {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 50px;
            width: 100%;
            font-weight: 700;
            transition: 0.3s;
            margin-top: 20px;
            font-size: 1.1rem;
        }

        .btn-theme:hover {
            background: #007a38;
            color: white;
        }

        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .success-overlay.active {
            opacity: 1;
        }

        .success-card {
            background: #1a1a1a;
            border: 1px solid var(--primary);
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 0 60px rgba(0, 148, 68, 0.4);
            max-width: 90%;
            transform: scale(0.8);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .success-overlay.active .success-card {
            transform: scale(1);
            opacity: 1;
        }

        .check-icon {
            font-size: 6rem;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
            transform: rotate(-180deg) scale(0);
            transition: all 0.6s ease 0.3s;
        }

        .success-overlay.active .check-icon {
            transform: rotate(0) scale(1);
        }

        footer {
            border-top: 1px solid var(--glass-border);
            padding-top: 60px;
            margin-top: 100px;
            background: rgba(0, 0, 0, 0.3);
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: 0.2s;
            font-family: var(--font-body);
        }

        .footer-link:hover {
            color: var(--primary);
            padding-left: 5px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand text-white fw-bold fs-3" href="../index.php">
                Zyroid<span style="color: var(--primary)">.</span>
            </a>

            <button class="navbar-toggler custom-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#nav">
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
                            <a href="#" class="nav-link d-flex align-items-center gap-2 dropdown-toggle"
                                data-bs-toggle="dropdown">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                    style="width: 35px; height: 35px;">
                                    <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
                                </div>
                                <span><?php echo $_SESSION['user']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary">
                                </li>
                                <li>
                                    <form method="post">
                                        <button type="submit" name="logout"
                                            class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn-theme ms-2">Sign In</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container page-header mb-5">
        <form method="POST">
            <div class="row g-5 align-items-start">

                <div class="col-lg-8">
                    <h2 class="text-white mb-4">Checkout Details</h2>

                    <div class="checkout-card mb-4">
                        <h5 class="text-white mb-4"><i class="bi bi-truck text-success me-2"></i> Shipping Information
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="fullname" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone" placeholder="+1 234 567 890"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Delivery Address</label>
                                <input type="text" class="form-control" name="address"
                                    placeholder="123, Innovation Street, Tech City" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-control" name="zip" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="checkout-card position-sticky" style="top: 100px;">
                        <h4 class="text-white mb-4">Order Summary</h4>

                        <?php if (count($cart_items) > 0): ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <span><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</span>
                                    <span
                                        class="text-white">$<?= number_format($item['product_price'] * $item['quantity'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-white-50">Your cart is empty.</p>
                        <?php endif; ?>

                        <div class="order-item">
                            <span>Tax (5%)</span>
                            <span class="text-white-50">$<?= number_format($tax, 2) ?></span>
                        </div>
                        <div class="order-item">
                            <span>Shipping</span>
                            <span class="text-success">FREE</span>
                        </div>

                        <div class="total-row">
                            <span>Total</span>
                            <span class="text-success">$<?= number_format($total, 2) ?></span>
                        </div>

                        <button type="submit" name="place_order" class="btn-theme">PAY
                            $<?= number_format($total, 2) ?></button>

                        <div class="text-center mt-3 small text-white-50">
                            <i class="bi bi-shield-check text-success"></i> 100% Secure Payment
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="success-overlay" id="successModal">
        <div class="success-card">
            <i class="bi bi-check-circle-fill check-icon"></i>
            <h2 class="text-white mb-3">Order Placed Successfully!</h2>
            <p class="text-white-50 mb-4">Thank you for shopping with Zyroid. <br> Your Order ID is
                <strong>#<?= $new_order_id ?></strong>.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="../index.php" class="btn btn-outline-light rounded-pill px-4">Home</a>
                <a href="my-orders.php" class="btn btn-outline-light rounded-pill px-4">View Order</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container pb-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white fw-bold fs-3 mb-3 d-block" href="../index.php">Zyroid<span
                            style="color: var(--primary)">.</span></a>
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
                    <div class="input-group">
                        <input type="email" class="form-control bg-transparent border-secondary text-white"
                            placeholder="Email">
                        <button class="btn btn-success">Join</button>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary border-opacity-10 text-white-50 small">&copy;
                2025 Zyroid Mobiles. All rights reserved.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        <?php if ($order_success): ?>
            const modal = document.getElementById('successModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
            }, 100);
        <?php endif; ?>

        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        const toggler = document.querySelector('.custom-toggler');
        if(toggler) toggler.addEventListener('click', () => toggler.classList.toggle('open'));
    </script>
</body>

</html>