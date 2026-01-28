<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

$id = isset($_GET['det']) ? $_GET['det'] : null;

$data = null;
if ($id) {
    $stmt = $con->prepare("SELECT * FROM tb_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
}

if (!$data) {
    echo "<script>alert('User not found'); window.location='customers.php';</script>";
    exit();
}

$stats_query = "SELECT SUM(amount) as total_spent, COUNT(*) as total_orders FROM tb_orders WHERE email = ?";
$stmt_stats = $con->prepare($stats_query);
$stmt_stats->bind_param("s", $data['email']);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - Zyroid Theme</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #009444;
            --bg-body: #1a1a1a;
            --text-main: #ffffff;
            --bg-surface: rgb(37 37 37);
            --border-color: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.08);
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-body);
            font-family: var(--font-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            z-index: 1040;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid var(--glass-border);
            text-decoration: none;
            display: block;
            font-family: var(--font-head);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
            padding: 16px 2.2rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: 0.2s;
            font-family: var(--font-head);
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .nav-link i.bi-chevron-down {
            font-size: 0.8rem;
            transition: transform 0.3s;
        }

        .nav-link[aria-expanded="true"] i.bi-chevron-down {
            transform: rotate(180deg);
        }

        .submenu {
            background: rgba(0, 0, 0, 0.2);
            padding-left: 0;
            list-style: none;
        }

        .submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.9rem;
            margin: 0 0 5px 30px;
        }

        ul { list-style-type: none; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; padding: 15px; }
            #overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
                backdrop-filter: blur(2px);
            }
            #overlay.active { display: block; }
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: margin-left 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
        }

        .avatar-lg {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), #004d23);
            color: #fff;
            border: 4px solid rgba(255, 255, 255, 0.1);
            margin: 0 auto;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 2px;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon { width: 30px; color: var(--primary-color); }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: #e0e0e0;
            border-color: var(--border-color);
        }
        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--border-color);
        }
        .table-custom td {
            vertical-align: middle;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background-color: #007a38; }
    </style>
</head>

<body>

    <div id="overlay"></div>

    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="bi bi-phone me-2"></i>ZYROID</a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none">
            <i class="bi bi-x-lg fs-4"></i>
        </button>

        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
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
                    <ul class="submenu flex-column">
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
                <a class="nav-link <?php echo ($current_page == 'orders.php' || $current_page == 'order_edit.php') ? 'active' : ''; ?>"
                    href="orders.php">
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
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>"
                    href="settings.php">
                    <i class="bi bi-gear me-2"></i> <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light d-lg-none me-3"><i class="bi bi-list"></i></button>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="customers.php" class="text-white-50 text-decoration-none">Users</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Details</li>
                        </ol>
                    </nav>
                    <h2 class="h3 fw-bold mb-0">Customer Profile</h2>
                </div>
            </div>
            <a href="customers.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-2"></i>Back</a>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="avatar-lg">
                                <?php echo strtoupper(substr($data['user'], 0, 1)); ?>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($data['user']); ?></h4>
                        <p class="text-white-50 small mb-3">ID: #CUST-<?= $data['id']; ?></p>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">Active Customer</span>
                        
                        <div class="mt-4 text-start">
                            <div class="info-row">
                                <div class="info-icon"><i class="bi bi-envelope"></i></div>
                                <div>
                                    <div class="stat-label">Email Address</div>
                                    <div><?= htmlspecialchars($data['email']); ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="bi bi-telephone"></i></div>
                                <div>
                                    <div class="stat-label">Phone Number</div>
                                    <div><?= htmlspecialchars($data['phone']); ?></div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="bi bi-calendar-check"></i></div>
                                <div>
                                    <div class="stat-label">Joined Date</div>
                                    <div><?= htmlspecialchars($data['registor_time']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="card p-3 h-100">
                            <div class="stat-label">Total Spent</div>
                            <div class="h4 fw-bold text-success mb-0">$<?= number_format($stats['total_spent'] ?? 0, 2); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 h-100">
                            <div class="stat-label">Total Orders</div>
                            <div class="h4 fw-bold text-white mb-0"><?= $stats['total_orders'] ?? 0; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Order History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0 table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $u_email = $data['email'];
                                    $limit = 5;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $offset = ($page - 1) * $limit;

                                    $stmt_c = $con->prepare("SELECT COUNT(*) as total FROM tb_orders WHERE email = ?");
                                    $stmt_c->bind_param("s", $u_email);
                                    $stmt_c->execute();
                                    $total_records = $stmt_c->get_result()->fetch_assoc()['total'];
                                    $total_pages = ceil($total_records / $limit);

                                    $stmt_o = $con->prepare("SELECT * FROM tb_orders WHERE email = ? ORDER BY id DESC LIMIT ?, ?");
                                    $stmt_o->bind_param("sii", $u_email, $offset, $limit);
                                    $stmt_o->execute();
                                    $res_orders = $stmt_o->get_result();

                                    if ($res_orders->num_rows > 0) {
                                        while ($ord = $res_orders->fetch_assoc()) { ?>
                                            <tr>
                                                <td class="ps-4 text-primary fw-bold">#<?= $ord['order_id'] ?></td>
                                                <td class="small text-white-50"><?= $ord['Date'] ?></td>
                                                <td><?= htmlspecialchars($ord['Product']) ?></td>
                                                <td>
                                                    <?php if ($ord['status'] == 1): ?>
                                                        <span class="badge bg-success bg-opacity-25 text-success">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning bg-opacity-25 text-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold">$<?= number_format($ord['amount'], 2) ?></td>
                                                <td class="text-end pe-4">
                                                    <a href="generate_invoice.php?id=<?= $ord['order_id'] ?>" target="_blank" class="btn btn-sm btn-outline-light" title="Download Invoice"><i class="bi bi-download"></i></a>
                                                </td>
                                            </tr>
                                    <?php }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center py-4 text-white-50">No orders found for this user.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer border-top border-secondary border-opacity-25 bg-transparent p-3">
                        <nav>
                            <ul class="pagination justify-content-end mb-0">
                                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link bg-transparent border-secondary text-white" href="?det=<?= $id ?>&page=<?= $page-1 ?>">Previous</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                                        <a class="page-link <?php echo ($page == $i) ? 'bg-success border-success' : 'bg-transparent border-secondary text-white'; ?>" 
                                           href="?det=<?= $id ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                                    <a class="page-link bg-transparent border-secondary text-white" href="?det=<?= $id ?>&page=<?= $page+1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        const toggleSidebar = () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        };

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>