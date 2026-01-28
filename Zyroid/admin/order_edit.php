<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_GET['uid'])) {
    $id = $_GET['uid'];
    $qr = "SELECT * FROM `tb_orders` WHERE `order_id` = '$id'";
    $res = mysqli_query($con, $qr);
    $data = mysqli_fetch_assoc($res);
}

if (isset($_POST['update_btn'])) {
    $order_id = $_POST['order_id'];
    $product = $_POST['product'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    $update_query = "UPDATE `tb_orders` SET `Product`='$product', `amount`='$amount', `status`='$status' WHERE `order_id`='$order_id'";

    if (mysqli_query($con, $update_query)) {
        $notif_msg = "Order #$order_id has been updated.";
        $notif_date = date('Y-m-d');
        $notif_type = 'order';
        $notif_q = "INSERT INTO `tb_notifications` (`type`, `source_id`, `is_read`, `created_at`, `message`) VALUES ('$notif_type', '$order_id', '2', '$notif_date', '$notif_msg')";
        mysqli_query($con, $notif_q);

        echo "<script>alert('Order Updated Successfully'); window.location.href='orders.php';</script>";
    } else {
        echo "<script>alert('Failed to Update');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Zyroid Theme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #009444;
            --bg-body: #1a1a1a;
            --text-main: #ffffff;
            --bg-surface: rgb(37 37 37);
            --glass-border: rgba(255, 255, 255, 0.08);
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-body);
            background-attachment: fixed;
            font-family: var(--font-body);
            color: #fff;
            min-height: 100vh;
        }

        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background: var(--bg-surface); border-right: 1px solid rgba(255, 255, 255, 0.05); z-index: 1000; overflow-y: auto; transition: all 0.3s ease; transform: translateX(0); white-space: nowrap; overflow-x: hidden; }

        .sidebar-brand {
            padding: 1.5rem;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-family: var(--font-head);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
            padding: 16px 2.2rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            text-decoration: none;
            font-family: var(--font-head);
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            transition: all 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--primary-color);
            box-shadow: none;
            color: #fff;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #007a38;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-content {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .glow-shape {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(111, 66, 193, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        #sidebarToggle { display: none; }
        #overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            #sidebarToggle { display: inline-block; }
            #overlay { display: block; }
            #overlay.active { opacity: 1; visibility: visible; }
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .btn {
            font-family: var(--font-head);
        }
    </style>
</head>

<body>

    <div class="glow-shape"></div>
    <div id="overlay"></div>

    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="bi bi-phone me-2"></i>ZYROID</a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none"><i class="bi bi-x-lg fs-4"></i></button>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a class="nav-link <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center" href="#customerSubmenu"
                    data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'true' : 'false'; ?>" aria-controls="customerSubmenu">
                    <div class="d-flex gap-3"></div>
                    <i class="bi bi-people me-2"></i> <span>Users</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'show' : ''; ?>" id="customerSubmenu">
                    <ul class="submenu flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'customers.php' || $current_page == 'customers-detail.php') ? 'active' : ''; ?>" href="customers.php">Users List</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item has-submenu">
                <?php $cat_pages = ['latest_products.php', 'iphone_products.php', 'android_products.php', 'gaming_products.php', 'accessories.php', 'hot_deals.php', 'edit_products.php']; ?>
                <a class="nav-link <?php echo in_array($current_page, $cat_pages) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center" href="#category"
                    data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($current_page, $cat_pages) ? 'true' : 'false'; ?>" aria-controls="category">
                    <i class="bi bi-tags me-2"></i> <span>Category</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, $cat_pages) ? 'show' : ''; ?>" id="category">
                    <ul class="submenu flex-column " style="list-style-type: none;">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'latest_products.php') ? 'active' : ''; ?>" href="latest_products.php">Latest Products</a>
                            <a class="nav-link <?php echo ($current_page == 'iphone_products.php') ? 'active' : ''; ?>" href="iphone_products.php">iPhone Products</a>
                            <a class="nav-link <?php echo ($current_page == 'android_products.php') ? 'active' : ''; ?>" href="android_products.php">Android Products</a>
                            <a class="nav-link <?php echo ($current_page == 'gaming_products.php') ? 'active' : ''; ?>" href="gaming_products.php">Gaming Products</a>
                            <a class="nav-link <?php echo ($current_page == 'accessories.php') ? 'active' : ''; ?>" href="accessories.php">Accessories</a>
                            <a class="nav-link <?php echo ($current_page == 'hot_deals.php') ? 'active' : ''; ?>" href="hot_deals.php">Hot Deals</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'orders.php' || $current_page == 'order_edit.php') ? 'active' : ''; ?>" href="orders.php">
                    <i class="bi bi-cart3 me-2"></i> <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'customer_review.php') ? 'active' : ''; ?>" href="customer_review.php">
                    <i class="bi bi-chat-left-text me-2"></i> <span>Reviews</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear me-2"></i> <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i
                        class="bi bi-list"></i></button>
                <div>
                    <h2 class="h3 fw-bold mb-1">Edit Order</h2>
                    <p class="text-white-50 mb-0">Update order details for ID #<?php echo $data['order_id']; ?></p>
                </div>
            </div>
            <a href="orders.php" class="btn btn-outline-light"><i class="bi bi-arrow-left me-2"></i> Back to Orders</a>
        </div>

        <div class="card">
            <form method="POST">

                <input type="hidden" name="order_id" value="<?php echo $data['order_id']; ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product" class="form-control" value="<?php echo $data['Product']; ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Order Date</label>
                        <input type="text" class="form-control" value="<?php echo $data['Date']; ?>" readonly
                            style="opacity: 0.7; cursor: not-allowed;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount ($)</label>
                        <input type="text" name="amount" class="form-control" value="<?php echo $data['amount']; ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select">
                            <option value="1" <?php if ($data['status'] == 1)
                                echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if ($data['status'] == 0)
                                echo 'selected'; ?>>Deactive</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="orders.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="update_btn" class="btn btn-primary"><i
                                class="bi bi-check-circle me-2"></i> Update Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        const overlay = document.getElementById('overlay');
        
        if (toggleBtn) toggleBtn.addEventListener('click', () => { sidebar.classList.add('active'); overlay.classList.add('active'); });
        const closeSidebar = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); };
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    </script>
</body>

</html>