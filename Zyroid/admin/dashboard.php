<?php
session_start();
include('../db_config.php');
if (!isset($_SESSION['admin_email'])) {
    header("location: index.php");
    exit();
}
$order_qr = "SELECT * FROM `tb_orders`";
$res = mysqli_query($con, $order_qr);
$order_row = mysqli_num_rows($res);
$users_qr = "SELECT * FROM `tb_users`";
$res_users = mysqli_query($con, $users_qr);
$users_row = mysqli_num_rows($res_users);

$recent_qr = "SELECT * FROM `tb_orders` ORDER BY order_id DESC LIMIT 2";
$recent_res = mysqli_query($con, $recent_qr);

$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $sales_data[$d] = 0;
}

$g_qr = "SELECT DATE(`Date`) as o_date, SUM(amount) as total FROM tb_orders WHERE status = 1 GROUP BY DATE(`Date`)";
$g_res = mysqli_query($con, $g_qr);
if ($g_res) {
    while ($row = mysqli_fetch_assoc($g_res)) {
        if (isset($sales_data[$row['o_date']])) {
            $sales_data[$row['o_date']] = $row['total'];
        }
    }
}
$chart_labels = array_map(function ($d) {
    return date('D', strtotime($d));
}, array_keys($sales_data));
$chart_data = array_values($sales_data);

if (isset($_POST['logout'])) {
    session_destroy();
    header("location: index.php");
    exit();
}

$new_notif_alert = false;
$notif_alert_msg = "";

$latest_n_q = "SELECT * FROM tb_notifications ORDER BY id DESC LIMIT 1";
$latest_n_res = mysqli_query($con, $latest_n_q);
if (mysqli_num_rows($latest_n_res) > 0) {
    $latest_n = mysqli_fetch_assoc($latest_n_res);
    $ln_id = $latest_n['id'];

    if (!isset($_SESSION['last_seen_notif']) || $_SESSION['last_seen_notif'] < $ln_id) {
        $new_notif_alert = true;
        $notif_alert_msg = $latest_n['message'];
        $_SESSION['last_seen_notif'] = $ln_id;
    }
}

$notif_res = mysqli_query($con, "SELECT COUNT(*) as c FROM tb_notifications WHERE is_read = 2");
$notif_row = mysqli_fetch_assoc($notif_res);
$total_notif = $notif_row['c'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MobileStore Admin - Zyroid Theme</title>
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

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-surface);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 1050;
            overflow-y: auto;
            transition: all 0.3s ease;
            transform: translateX(0);
            white-space: nowrap;
            overflow-x: hidden;
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
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
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

        .sidebar-brand {
            padding: 1.5rem 1rem;
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
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            padding: 10px 20px;
        }

        .stat-card {
            border-left: 4px solid transparent;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }


        .border-left-primary {
            border-left-color: #009444 !important;
        }

        .border-left-info {
            border-left-color: #0dcaf0 !important;
        }

        .border-left-warning {
            border-left-color: #f6c23e !important;
        }

        .border-left-danger {
            border-left-color: #e74a3b !important;
        }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #fff;
            --bs-table-border-color: rgba(255, 255, 255, 0.1);
        }

        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            border-bottom-width: 1px;
            padding-bottom: 15px;
        }

        .table-custom td {
            padding: 15px 0.75rem;
            vertical-align: middle;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-head);
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
    </style>
</head>

<body>

    <?php if ($new_notif_alert): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div id="liveToast" class="toast show bg-success text-white border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="toast-header bg-success text-white border-bottom border-white border-opacity-25">
                    <i class="bi bi-bell-fill me-2"></i>
                    <strong class="me-auto">New Notification</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?= htmlspecialchars($notif_alert_msg) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="glow-shape"></div>
    <div id="particles"></div>
    <div id="overlay"></div>

    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="bi bi-phone me-2"></i>ZYROID
        </a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none"><i
                class="bi bi-x-lg fs-4"></i></button>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
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
                <a class="nav-link" href="orders.php">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button id="sidebarToggle" class="btn btn-outline-light me-2">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="h3 text-light d-inline-block">Dashboard Overview</h2>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="notifications.php"
                    class="btn btn-outline-light position-relative border-0 rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if ($total_notif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size: 0.6rem;">
                            <?= $total_notif ?>
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center border-0"
                        type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-success rounded-circle me-2 d-flex align-items-center justify-content-center"
                            style="width: 30px; height: 30px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary rounded-0"
                        aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item text-light" href="profile_admin.php"><i
                                    class="bi bi-person me-2"></i>Profile</a></li>
                        <li>
                            <hr class="dropdown-divider bg-light">
                        </li>
                        <li>
                            <form method="POST" style="display: inline;">
                                <button type="submit" class="dropdown-item text-danger" name="logout"><i
                                        class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-left-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div style="min-width: 0;">
                                <div class="text-success small fw-bold text-uppercase mb-1">Total Revenue</div>
                                <?php
                                $total_rev_query = "SELECT SUM(amount) AS total_revenue FROM tb_orders WHERE status = 1";
                                $rev_res = mysqli_query($con, $total_rev_query);
                                $rev_data = mysqli_fetch_assoc($rev_res);
                                $revenue = $rev_data['total_revenue'] ?? 0;
                                $text_color_class = ($revenue >= 50) ? 'text-success' : 'text-danger';
                                echo '<div class="h4 mb-0 fw-bold ' . $text_color_class . ' text-break">$' . number_format($revenue, 2) . '</div>';
                                ?>
                            </div>
                            <i class="bi bi-currency-dollar fs-1 text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-left-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-info small fw-bold text-uppercase mb-1">Total Orders</div>
                                <div class="h4 mb-0 fw-bold text-light"><?= $order_row; ?></div>
                            </div>
                            <i class="bi bi-bag-check fs-1 text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-left-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-warning small fw-bold text-uppercase mb-1">Users</div>
                                <div class="h4 mb-0 fw-bold text-light"><?= $users_row; ?></div>
                            </div>
                            <i class="bi bi-people fs-1 text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-left-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div style="min-width: 0;">
                                <div class="text-danger small fw-bold text-uppercase mb-1">Stock Items</div>
                                <?php
                                $total_stock_query = "SELECT SUM(stocks) AS total_inventory FROM tb_products;";
                                $st_res = mysqli_query($con, $total_stock_query);
                                $stock_data = mysqli_fetch_assoc($st_res);
                                $inventory_count = $stock_data['total_inventory'] ?? 0;
                                $stock_text_color = ($inventory_count >= 5) ? 'text-success' : 'text-danger';
                                echo '<div class="h4 mb-0 fw-bold ' . $stock_text_color . ' text-break">' . number_format($inventory_count) . '</div>';
                                ?>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 text-secondary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent border-bottom border-secondary py-3">
                        <h6 class="m-0 fw-bold text-primary"><i class="bi bi-graph-up me-2"></i>Sales Overview</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 400px; width: 100%;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div
                        class="card-header bg-transparent border-bottom border-secondary py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-white">Recent Orders</h6>
                        <button class="btn btn-sm btn-outline-success"><a href="orders.php"
                                class="text-white text-decoration-none">View All</a></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Order ID</th>
                                        <th>Product</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($data = mysqli_fetch_assoc($recent_res)) {
                                        ?>
                                        <tr>
                                            <td class="ps-4 text-white">#<?= $data['order_id'] ?></td>
                                            <td><?= $data['Product'] ?></td>
                                            <td><?= $data['Date'] ?></td>
                                            <td><?= $data['amount'] ?></td>
                                            <td>
                                                <?php if ($data['status'] == 1): ?>
                                                    <span
                                                        class="badge rounded-pill bg-success bg-opacity-25 text-success">Actve</span>
                                                <?php else: ?>
                                                    <span
                                                        class="badge rounded-pill bg-danger bg-opacity-25 text-danger">Deactive</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        const overlay = document.getElementById('overlay');

        if (toggleBtn) toggleBtn.addEventListener('click', () => { sidebar.classList.add('active'); overlay.classList.add('active'); });

        const closeSidebar = () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        };

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

        const ctx = document.getElementById('salesChart').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(0, 148, 68, 0.4)');
        gradient.addColorStop(1, 'rgba(0, 148, 68, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Sales ($)',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#009444',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#009444',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#aaa' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#aaa' }
                    }
                }
            }
        });
    </script>
</body>

</html>