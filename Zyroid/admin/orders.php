<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = "";
$where_clause = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $where_clause = " WHERE o.order_id LIKE '%$search%' OR o.Product LIKE '%$search%' OR o.email LIKE '%$search%' OR u.user LIKE '%$search%' ";
}

$count_qr = "SELECT COUNT(*) as total FROM tb_orders o LEFT JOIN tb_users u ON o.email = u.email $where_clause";
$count_res = mysqli_query($con, $count_qr);
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $limit);

$qr = "SELECT o.*, u.user as customer_name, u.phone as customer_phone FROM tb_orders o LEFT JOIN tb_users u ON o.email = u.email $where_clause ORDER BY o.id DESC LIMIT $offset, $limit";
$res = mysqli_query($con, $qr);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Zyroid Theme</title>
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
            --border-color: rgba(255, 255, 255, 0.05);
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

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            transform: translateX(0);
            white-space: nowrap;
            overflow-x: hidden;
        }

        .sidebar-brand {
            padding: 1.5rem;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid var(--border-color);
            font-family: var(--font-head);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
            padding: 16px 2.2rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            font-family: var(--font-head);
        }

        ul {
            list-style-type: none;
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: all 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
        }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #e0e0e0;
            border-color: var(--border-color);
            min-width: 800px;
        }

        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid var(--border-color);
            padding: 15px;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .badge-status {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border: 1px solid transparent;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            border-color: rgba(255, 193, 7, 0.2);
        }

        .status-shipped {
            background: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
            border-color: rgba(13, 202, 240, 0.2);
        }

        .status-delivered {
            background: rgba(0, 148, 68, 0.15);
            color: #009444;
            border-color: rgba(0, 148, 68, 0.2);
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.2);
        }

        .modal-content {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            color: white;
        }

        .modal-header,
        .modal-footer {
            border-color: var(--border-color);
        }

        .invoice-box {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            padding: 15px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: #007a38;
        }

        #sidebarToggle {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }

        #sidebarToggle:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        #sidebarToggle i {
            transition: transform 0.3s ease;
        }

        #sidebarToggle.active i {
            transform: rotate(90deg);
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
            z-index: 0;
            pointer-events: none;
            animation: pulseGlow 8s infinite alternate;
        }

        #particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: floatUp linear infinite;
        }

        .main-content {
            position: relative;
            z-index: 1;
        }

        @keyframes pulseGlow {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0.4;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.7;
            }
        }

        @keyframes floatUp {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            100% {
                transform: translateY(-10vh) scale(1);
                opacity: 0;
            }
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-head);
        }

        #sidebarToggle {
            display: none;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            #sidebarToggle {
                display: inline-block;
            }

            #overlay {
                display: block;
            }

            #overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }

        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.5s ease-out forwards;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="glow-shape"></div>
    <div id="particles"></div>
    <div id="overlay"></div>
    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="bi bi-phone me-2"></i>ZYROID</a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none"><i
                class="bi bi-x-lg fs-4"></i></button>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a class="nav-link <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center"
                    href="#customerSubmenu" data-bs-toggle="collapse" role="button"
                    aria-expanded="<?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'true' : 'false'; ?>"
                    aria-controls="customerSubmenu">
                    <div class="d-flex gap-3"></div>
                    <i class="bi bi-people me-2"></i> <span>Users</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'show' : ''; ?>"
                    id="customerSubmenu">
                    <ul class="submenu flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'customers.php' || $current_page == 'customers-detail.php') ? 'active' : ''; ?>"
                                href="customers.php">Users List</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item has-submenu">
                <?php $cat_pages = ['latest_products.php', 'iphone_products.php', 'android_products.php', 'gaming_products.php', 'accessories.php', 'edit_products.php']; ?>
                <a class="nav-link <?php echo in_array($current_page, $cat_pages) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center"
                    href="#category" data-bs-toggle="collapse" role="button"
                    aria-expanded="<?php echo in_array($current_page, $cat_pages) ? 'true' : 'false'; ?>"
                    aria-controls="category">
                    <i class="bi bi-tags me-2"></i> <span>Category</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, $cat_pages) ? 'show' : ''; ?>" id="category">
                    <ul class="submenu flex-column " style="list-style-type: none;">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'latest_products.php') ? 'active' : ''; ?>"
                                href="latest_products.php">Latest Products</a>
                            <a class="nav-link <?php echo ($current_page == 'iphone_products.php') ? 'active' : ''; ?>"
                                href="iphone_products.php">iPhone Products</a>
                            <a class="nav-link <?php echo ($current_page == 'android_products.php') ? 'active' : ''; ?>"
                                href="android_products.php">Android Products</a>
                            <a class="nav-link <?php echo ($current_page == 'gaming_products.php') ? 'active' : ''; ?>"
                                href="gaming_products.php">Gaming Products</a>
                            <a class="nav-link <?php echo ($current_page == 'accessories.php') ? 'active' : ''; ?>"
                                href="accessories.php">Accessories</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="orders.php">
                    <i class="bi bi-cart3 me-2"></i> <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'customer_review.php') ? 'active' : ''; ?>"
                    href="customer_review.php">
                    <i class="bi bi-chat-left-text me-2"></i> <span>Reviews</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear me-2"></i> <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <?php if (isset($_SESSION['action_msg'])): ?>
            <div class="alert alert-<?= $_SESSION['action_type'] ?> alert-dismissible fade show alert-floating bg-<?= $_SESSION['action_type'] ?> text-white border-0"
                role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['action_msg'] ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['action_msg']);
            unset($_SESSION['action_type']); ?>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i class="bi bi-list"></i></button>
                <div>
                    <h2 class="h3 fw-bold mb-1">Order Management</h2>
                    <p class="text-white-50 mb-0">View and update customer orders</p>
                </div>
            </div>
            <div class="d-flex gap-2">
            </div>
        </div>
        <form method="GET" class="d-flex align-items-center mb-3 gap-2">
            <input type="text" name="search" class="form-control bg-transparent text-white border-secondary flex-grow-1"
                placeholder="Search Order ID, Product or Customer..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-light"><i class="bi bi-search"></i></button>
            <?php if (!empty($search)): ?>
                <a href="orders.php" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 table-hover">
                        <thead>
                            <tr>
                                <th class="ps-4">Order ID</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($data = mysqli_fetch_assoc($res)) {
                                $p_names = explode(',', $data['Product']);
                                $p_images = [];
                                foreach ($p_names as $pn) {
                                    $pn = trim($pn);
                                    $pn_esc = mysqli_real_escape_string($con, $pn);
                                    $img_q = mysqli_query($con, "SELECT product_image FROM tb_products WHERE product_name='$pn_esc'");
                                    $img_d = mysqli_fetch_assoc($img_q);
                                    $p_images[] = $img_d ? $img_d['product_image'] : '';
                                }
                                $images_str = implode(',', $p_images);
                                ?>
                                <tr>
                                    <td class="ps-4 text-white">#<?= $data['order_id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($p_images[0])): ?>
                                                <img src="product_images/<?= $p_images[0] ?>" class="rounded me-2"
                                                    style="width: 40px; height: 40px; object-fit: cover;"
                                                    onerror="this.style.display='none'">
                                            <?php endif; ?>
                                            <span><?= $data['Product'] ?></span>
                                        </div>
                                    </td>
                                    <td><?= $data['Date'] ?></td>
                                    <td>$<?= $data['amount'] ?></td>
                                    <td>
                                        <?php if ($data['status'] == 1) {
                                            echo "<span class='badge badge-status status-delivered'>Active</span>";
                                        } else {
                                            echo "<span class='badge badge-status status-cancelled'>Deactive</span>";
                                        }
                                        ?>
                                    </td>

                                    <td class="text-end pe-4 d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#orderDetailModal"
                                            data-order-id="<?= htmlspecialchars($data['order_id']) ?>"
                                            data-product="<?= htmlspecialchars($data['Product']) ?>"
                                            data-images="<?= htmlspecialchars($images_str) ?>"
                                            data-date="<?= htmlspecialchars($data['Date']) ?>"
                                            data-amount="<?= htmlspecialchars($data['amount']) ?>"
                                            data-status="<?= $data['status'] ?>"
                                            data-customer-name="<?= htmlspecialchars($data['customer_name'] ?? 'N/A') ?>"
                                            data-customer-email="<?= htmlspecialchars($data['email']) ?>"
                                            data-customer-phone="<?= htmlspecialchars($data['customer_phone'] ?? 'N/A') ?>"
                                            data-shipping-name="<?= htmlspecialchars($data['shipping_name'] ?? '') ?>"
                                            data-shipping-address="<?= htmlspecialchars($data['shipping_address'] ?? '') ?>"
                                            data-shipping-city="<?= htmlspecialchars($data['shipping_city'] ?? '') ?>"
                                            data-shipping-state="<?= htmlspecialchars($data['shipping_state'] ?? '') ?>"
                                            data-shipping-zip="<?= htmlspecialchars($data['shipping_zip'] ?? '') ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="generate_invoice.php?id=<?= $data['order_id'] ?>" target="_blank" class="btn btn-sm btn-outline-light" title="Download Invoice"><i class="bi bi-download"></i></a>
                                        <a href="order_edit.php?uid=<?= $data['order_id'] ?>"
                                            class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></a>
                                        <a href="order_delete.php?did=<?= $data['order_id'] ?>"
                                            class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="p-3 border-top border-secondary border-opacity-25">
                        <ul class="pagination justify-content-end mb-0">
                            <?php
                            $search_param = !empty($search) ? '&search=' . urlencode($search) : '';
                            if ($page > 1) {
                                $prev = $page - 1;
                                echo '<li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="orders.php?page=' . $prev . $search_param . '">Previous</a></li>';
                            }

                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = ($i == $page) ? 'active' : '';
                                $bg = ($i == $page) ? 'bg-success border-success' : 'bg-transparent border-secondary';
                                echo '<li class="page-item ' . $active . '"><a class="page-link ' . $bg . ' text-white" href="orders.php?page=' . $i . $search_param . '">' . $i . '</a></li>';
                            }

                            if ($page < $total_pages) {
                                $next = $page + 1;
                                echo '<li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="orders.php?page=' . $next . $search_param . '">Next</a></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalOrderId">Order #</h5>
                        <span class="text-white-50 small" id="modalOrderDate"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="text-primary mb-3">Order Items</h6>
                            <div class="invoice-box" id="modalProductList">
                            </div>
                            <div
                                class="d-flex justify-content-between border-top border-secondary border-opacity-25 pt-2 mt-2">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold fs-5 text-primary" id="modalTotalAmount"></span>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-primary mb-3">Customer Details</h6>
                            <div class="mb-3">
                                <div class="fw-bold" id="modalCustomerName"></div>
                                <div class="text-white-50 small" id="modalCustomerEmail"></div>
                                <div class="text-white-50 small" id="modalCustomerPhone"></div>
                            </div>

                            <h6 class="text-primary mb-2">Shipping Address</h6>
                            <div class="text-white-50 small mb-4" id="modalShippingAddress">

                            </div>

                            <h6 class="text-primary mb-2">Order Status</h6>
                            <div id="modalOrderStatus"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="printInvoiceBtn" target="_blank" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-printer me-2"></i>Print Invoice</a>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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

        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('particles');
            for (let i = 0; i < 15; i++) {
                const p = document.createElement('div');
                p.classList.add('particle');
                p.style.width = Math.random() * 4 + 2 + 'px'; p.style.height = p.style.width;
                p.style.left = Math.random() * 100 + '%'; p.style.animationDelay = Math.random() * 5 + 's';
                p.style.animationDuration = Math.random() * 10 + 10 + 's'; container.appendChild(p);
            }
        });

        const orderDetailModal = document.getElementById('orderDetailModal');
        orderDetailModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const orderId = button.getAttribute('data-order-id');
            const product = button.getAttribute('data-product');
            const images = button.getAttribute('data-images');
            const date = button.getAttribute('data-date');
            const amount = button.getAttribute('data-amount');
            const status = button.getAttribute('data-status');
            const customerName = button.getAttribute('data-customer-name');
            const customerEmail = button.getAttribute('data-customer-email');
            const customerPhone = button.getAttribute('data-customer-phone');
            const shippingName = button.getAttribute('data-shipping-name');
            const shippingAddress = button.getAttribute('data-shipping-address');
            const shippingCity = button.getAttribute('data-shipping-city');
            const shippingState = button.getAttribute('data-shipping-state');
            const shippingZip = button.getAttribute('data-shipping-zip');

            const modalTitle = orderDetailModal.querySelector('.modal-title');
            const modalDate = orderDetailModal.querySelector('#modalOrderDate');
            const modalProductList = orderDetailModal.querySelector('#modalProductList');
            const modalTotalAmount = orderDetailModal.querySelector('#modalTotalAmount');
            const modalCustomerName = orderDetailModal.querySelector('#modalCustomerName');
            const modalCustomerEmail = orderDetailModal.querySelector('#modalCustomerEmail');
            const modalCustomerPhone = orderDetailModal.querySelector('#modalCustomerPhone');
            const modalShippingAddressDiv = orderDetailModal.querySelector('#modalShippingAddress');
            const modalOrderStatus = orderDetailModal.querySelector('#modalOrderStatus');
            const printBtn = orderDetailModal.querySelector('#printInvoiceBtn');

            printBtn.href = 'generate_invoice.php?id=' + orderId;

            modalTitle.textContent = 'Order ' + orderId;
            modalDate.textContent = 'Placed on ' + date;

            modalProductList.innerHTML = '';
            const products = product.split(',');
            const productImages = images.split(',');
            products.forEach((p, index) => {
                const img = productImages[index] ? `product_images/${productImages[index]}` : 'https://via.placeholder.com/50';
                const itemDiv = document.createElement('div');
                itemDiv.className = 'd-flex align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-2';
                itemDiv.innerHTML = `
                    <img src="${img}" onerror="this.src='https://via.placeholder.com/50'" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                    <span>${p.trim()}</span>`;
                modalProductList.appendChild(itemDiv);
            });

            modalTotalAmount.textContent = '$' + parseFloat(amount).toFixed(2);
            modalCustomerName.textContent = customerName;
            modalCustomerEmail.textContent = customerEmail;
            modalCustomerPhone.textContent = customerPhone;

            if (shippingAddress) {
                modalShippingAddressDiv.innerHTML = `<strong>${shippingName}</strong><br>${shippingAddress}<br>${shippingCity}, ${shippingState} ${shippingZip}`;
            } else {
                modalShippingAddressDiv.innerHTML = 'Address not provided during checkout.';
            }

            modalOrderStatus.innerHTML = (status == '1')
                ? `<span class='badge badge-status status-delivered'>Active</span>`
                : `<span class='badge badge-status status-cancelled'>Deactive</span>`;
        });
    </script>
</body>

</html>