<?php
session_start();
include('../db_config.php');

if (!isset($_SESSION['email'])) {
    header("location: login.php");
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $user_email = $_SESSION['email'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $prod_q = "SELECT * FROM tb_products WHERE id = '$product_id'";
    $prod_res = mysqli_query($con, $prod_q);
    if (mysqli_num_rows($prod_res) > 0) {
        $product_data = mysqli_fetch_assoc($prod_res);
        $product_name = mysqli_real_escape_string($con, $product_data['product_name']);
        $product_price = $product_data['product_price'];
        $product_image = $product_data['product_image'];

        $check_cart_q = "SELECT * FROM tb_cart WHERE email = '$user_email' AND product_id = '$product_id'";
        $check_cart_res = mysqli_query($con, $check_cart_q);

        if (mysqli_num_rows($check_cart_res) > 0) {
            $cart_data = mysqli_fetch_assoc($check_cart_res);
            $new_quantity = $cart_data['quantity'] + $quantity;
            $update_q = "UPDATE tb_cart SET quantity = '$new_quantity' WHERE email = '$user_email' AND product_id = '$product_id'";
            mysqli_query($con, $update_q);
        } else {
            $insert_q = "INSERT INTO tb_cart (email, product_id, product_name, product_price, product_image, quantity) VALUES ('$user_email', '$product_id', '$product_name', '$product_price', '$product_image', '$quantity')";
            mysqli_query($con, $insert_q);
        }
        $_SESSION['cart_success'] = "Cart updated successfully!";
        header("location: cart.php");
        exit();
    }
}

if (isset($_GET['remove_item'])) {
    $cart_id = $_GET['remove_item'];
    $user_email = $_SESSION['email'];
    $delete_q = "DELETE FROM tb_cart WHERE id = '$cart_id' AND email = '$user_email'";
    mysqli_query($con, $delete_q);
    header("location: cart.php");
    exit();
}

if (isset($_POST['change_qty'])) {
    $cart_id = $_POST['cart_id'];
    $change = (int) $_POST['change_qty'];
    $user_email = $_SESSION['email'];

    $get_qty_q = "SELECT quantity FROM tb_cart WHERE id = '$cart_id' AND email = '$user_email'";
    $get_qty_res = mysqli_query($con, $get_qty_q);
    if (mysqli_num_rows($get_qty_res) > 0) {
        $cart_data = mysqli_fetch_assoc($get_qty_res);
        $new_quantity = $cart_data['quantity'] + $change;
        if ($new_quantity >= 1) {
            $query = "UPDATE tb_cart SET quantity = '$new_quantity' WHERE id = '$cart_id'";
            mysqli_query($con, $query);
        }
    }
    header("location: cart.php");
    exit();
}

class user_profile
{
    public $name;
    public $email;
    function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
}

$obj = null;
if (isset($_SESSION['user']) && isset($_SESSION['email'])) {
    $user = $_SESSION['user'];
    $email = $_SESSION['email'];
    $sel = "SELECT * FROM tb_users Where `email`= '$email'";
    $res = mysqli_query($con, $sel);
    $row = mysqli_fetch_assoc($res);

    if ($row) {
        if (!isset($_SESSION['token']) || $row['token'] != $_SESSION['token']) {
            session_destroy();
            header("location: login.php");
            exit();
        }
        $obj = new user_profile($user, $email);
    }

    if (isset($_POST['logout'])) {
        mysqli_query($con, "UPDATE tb_users SET token=NULL WHERE email='$email'");
        session_destroy();
        header("location: login.php");
        exit();
    }
}

$user_email = $_SESSION['email'];
$cart = "SELECT * FROM tb_cart WHERE `email` = '$user_email'";
$cart_res = mysqli_query($con, $cart);

$subtotal = 0.0;
$items_array = [];
if ($cart_res && mysqli_num_rows($cart_res) > 0) {
    while ($item = mysqli_fetch_assoc($cart_res)) {
        $price = (float) str_replace(['$', ','], '', $item['product_price']);
        $qty = (int) $item['quantity'];
        $item_total = $price * $qty;
        $subtotal += $item_total;
        $items_array[] = $item;
    }
}

$tax_rate = 0.05;
$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Zyroid</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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

        h1, h2, h3, h4, h5, h6,
        .navbar-brand, .btn-theme, .nav-link, .dropdown-item {
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
        
        .page-header { padding-top: 130px; padding-bottom: 30px; }

        .cart-item {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            transition: var(--transition);
            position: relative;
        }

        .cart-item:hover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .item-checkbox {
            width: 25px;
            height: 25px;
            margin-right: 20px;
            cursor: pointer;
            accent-color: var(--primary);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .cart-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-right: 20px;
        }

        .qty-box {
            display: flex;
            align-items: center;
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            padding: 5px 10px;
            background: rgba(0, 0, 0, 0.2);
        }

        .qty-btn {
            background: transparent; border: none; color: white;
            font-size: 1.2rem; width: 30px; height: 30px;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
        }

        .qty-val {
            width: 30px; text-align: center; font-weight: bold;
            background: transparent; border: none; color: white;
            font-family: var(--font-body);
        }

        .summary-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 30px;
            position: sticky;
            top: 100px;
        }

        .summary-row {
            display: flex; justify-content: space-between; margin-bottom: 15px;
            font-size: 0.95rem; color: rgba(255, 255, 255, 0.7);
        }

        .summary-total {
            border-top: 1px solid var(--glass-border); padding-top: 20px; margin-top: 20px;
            display: flex; justify-content: space-between; font-size: 1.5rem;
            font-weight: 700; color: white; font-family: var(--font-head);
        }

        .btn-theme {
            background: var(--primary); color: #fff; border: none;
            padding: 12px 30px; border-radius: 50px; font-weight: 700;
            width: 100%; display: block; text-align: center; text-decoration: none;
            transition: 0.3s; cursor: pointer;
        }

        .btn-theme:disabled {
            background: #444;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .btn-theme:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 148, 68, 0.4);
            color: white;
        }

        footer { border-top: 1px solid var(--glass-border); padding-top: 60px; margin-top: 100px; background: rgba(0, 0, 0, 0.3); }
        .footer-link { color: rgba(255, 255, 255, 0.5); text-decoration: none; display: block; margin-bottom: 10px; transition: 0.2s; font-family: var(--font-body); }
        .footer-link:hover { color: var(--primary); padding-left: 5px; }

        @media (max-width: 576px) {
            .cart-item { flex-direction: column; text-align: center; }
            .cart-img { margin-right: 0; margin-bottom: 15px; }
            .qty-box { margin: 15px 0; }
            .item-checkbox { margin-right: 0; margin-bottom: 15px; }
            .btn-outline-danger { margin-left: 0 !important; margin-top: 10px; }
        }
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
                    <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
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

    <?php if (isset($_SESSION['cart_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-4 bg-success text-white border-0" style="z-index: 9999;" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['cart_success']; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>

    <section class="page-header container">
        <h1 class="display-4 fw-bold text-white">Your Shopping Cart</h1>
        <div class="form-check mt-3">
            <input class="form-check-input item-checkbox" type="checkbox" id="selectAll" checked onchange="toggleSelectAll()">
            <label class="form-check-label text-white-50" for="selectAll">Select All Items</label>
        </div>
    </section>

    <form id="checkout-form" action="checkout.php" method="POST"></form>

    <section class="container pb-5">
        <div class="row g-5">
            <div class="col-lg-8">
                <?php if (!empty($items_array)): ?>
                    <?php foreach ($items_array as $cart_item):
                        $d_price = (float) str_replace(['$', ','], '', $cart_item['product_price']);
                        $d_qty = (int) $cart_item['quantity'];
                        $d_total = $d_price * $d_qty;
                        ?>
                        <div class="cart-item">
                            <input type="checkbox" 
                                   name="selected_items[]" 
                                   value="<?= $cart_item['id'] ?>" 
                                   class="item-checkbox product-select" 
                                   form="checkout-form"
                                   data-item-total="<?= $d_total ?>"
                                   checked
                                   onchange="recalculateTotal()">

                            <img src="../admin/product_images/<?= htmlspecialchars($cart_item['product_image']) ?>"
                                class="cart-img" alt="<?= htmlspecialchars($cart_item['product_name']) ?>">
                            
                            <div class="flex-grow-1">
                                <h4 class="text-white mb-1"><?= htmlspecialchars($cart_item['product_name']) ?></h4>
                                <small class="text-white-50">Price: $<?= number_format($d_price, 2) ?></small>
                                <h5 class="text-white price mt-2">$<?= number_format($d_total, 2) ?></h5>
                            </div>

                            <div class="qty-box">
                                <form method="POST" action="cart.php" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?= $cart_item['id'] ?>">
                                    <input type="hidden" name="change_qty" value="-1">
                                    <button type="submit" class="qty-btn">-</button>
                                </form>
                                <input type="text" value="<?= $cart_item['quantity'] ?>" class="qty-val" readonly>
                                <form method="POST" action="cart.php" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?= $cart_item['id'] ?>">
                                    <input type="hidden" name="change_qty" value="1">
                                    <button type="submit" class="qty-btn">+</button>
                                </form>
                            </div>

                            <a href="cart.php?remove_item=<?= $cart_item['id'] ?>" class="btn btn-sm btn-outline-danger ms-3"
                                style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="checkout-card text-center p-5 border border-secondary border-opacity-25 rounded-3">
                        <h4 class='text-white-50'>Your cart is empty.</h4>
                        <a href='category.php' class='btn-theme mt-3 d-inline-block' style="max-width: 200px;">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h4 class="text-white mb-4">Order Summary</h4>
                    <div class="summary-row">
                        <span>Selected Items</span>
                        <span id="selected-count"><?= count($items_array) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal">$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (5%)</span>
                        <span id="tax">$<?= number_format($tax, 2) ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span class="text-success" id="total">$<?= number_format($total, 2) ?></span>
                    </div>
                    
                    <button type="submit" class="btn-theme mt-4" form="checkout-form" id="checkoutBtn">
                        Checkout Selected <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        function recalculateTotal() {
            let subtotal = 0;
            let count = 0;
            const checkboxes = document.querySelectorAll('.product-select');
            
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    subtotal += parseFloat(cb.getAttribute('data-item-total'));
                    count++;
                }
            });

            const tax = subtotal * 0.05;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = formatter.format(subtotal);
            document.getElementById('tax').textContent = formatter.format(tax);
            document.getElementById('total').textContent = formatter.format(total);
            document.getElementById('selected-count').textContent = count;

            const btn = document.getElementById('checkoutBtn');
            if(count === 0) {
                btn.disabled = true;
                btn.innerHTML = "Select Items to Checkout";
            } else {
                btn.disabled = false;
                btn.innerHTML = `Checkout Selected (${count}) <i class="bi bi-arrow-right ms-2"></i>`;
            }
        }

        function toggleSelectAll() {
            const master = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.product-select');
            
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
            });
            
            recalculateTotal();
        }

        document.addEventListener('DOMContentLoaded', () => {
            recalculateTotal();

            window.addEventListener('scroll', () => {
                const nav = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            });

            const toggler = document.querySelector('.custom-toggler');
            if(toggler) toggler.addEventListener('click', () => toggler.classList.toggle('open'));
        });
    </script>
</body>
</html>