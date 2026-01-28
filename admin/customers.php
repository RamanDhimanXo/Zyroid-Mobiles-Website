<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_email'])) {
    header("location: index.php");
    exit();
}

if (isset($_GET['uid']) && isset($_GET['val'])) {
    $uid = (int) $_GET['uid'];
    $val = (int) $_GET['val'];

    if (($val === 0 || $val === 1) && $uid > 0) {
        $stmt = mysqli_prepare($con, "UPDATE tb_users SET user_status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ii", $val, $uid);
        mysqli_stmt_execute($stmt);
    }
    header("Location: customers.php");
    exit();
}

$search = "";
$where_clause = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $where_clause = " WHERE user LIKE '%$search%' OR email LIKE '%$search%' ";
}

$limit = 7;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_qr = "SELECT COUNT(*) as total FROM tb_users $where_clause";
$count_res = mysqli_query($con, $count_qr);
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $limit);

$qr = "SELECT * FROM tb_users $where_clause ORDER BY id DESC LIMIT $offset, $limit";
$res = mysqli_query($con, $qr);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Zyroid Theme</title>
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
            font-family: var(--font-head);
        }

        ul {
            list-style-type: none;
            margin-bottom: 5px;
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

        .submenu .nav-link.active {
            color: var(--primary-color);
            background: #252525;
            border-left: 4px solid var(--primary-color);
            font-weight: bold;
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
            border-color: rgba(255, 255, 255, 0.05);
        }

        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #fff;
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

        #sidebarToggle { display: none; }
        #overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.3s; }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            #sidebarToggle { display: inline-block; }
            #overlay { display: block; }
            #overlay.active { opacity: 1; visibility: visible; }
        }

        .pagination {
            padding-left: 30px;
            position: absolute;
            bottom: 30px;
            right: 30px
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-head);
        }
    </style>
</head>

<body>

    <div class="glow-shape"></div>
    <div id="particles"></div>
    <div id="overlay"></div>
    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="bi bi-phone me-2"></i>ZYROID</a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none"><i class="bi bi-x-lg fs-4"></i></button>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a class="nav-link <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center"
                    href="#customerSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'true' : 'false'; ?>"
                    aria-controls="customerSubmenu">
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
                <?php $cat_pages = ['latest_products.php', 'iphone_products.php', 'android_products.php', 'gaming_products.php', 'accessories.php', 'edit_products.php']; ?>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-floating bg-success text-white border-0"
                role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-floating bg-danger text-white border-0"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i
                        class="bi bi-list"></i></button>
                <div>
                    <h2 class="h3 fw-bold mb-1">Users List</h2>
                    <p class="text-white-50 mb-0">Manage registered users</p>
                </div>
            </div>
        </div>

        <form method="GET" class="d-flex gap-2 mb-4">
            <input type="text" name="search" class="form-control bg-transparent text-white border-secondary"
                placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary px-4">Search</button>
            <?php if ($search): ?>
                <a href="customers.php" class="btn btn-outline-light">Clear</a>
            <?php endif; ?>
        </form>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0 table-hover">
                        <thead>
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Contact</th>
                                <th>Registered</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($data = mysqli_fetch_assoc($res)) {
                                $stats_query = "SELECT COUNT(*) as total_orders, SUM(amount) as total_spent FROM tb_orders WHERE email = '" . mysqli_real_escape_string($con, $data['email']) . "'";
                                $stats_res = mysqli_query($con, $stats_query);
                                $stats_data = mysqli_fetch_assoc($stats_res);

                                $total_orders = $stats_data['total_orders'] ?? 0;
                                $total_spent = $stats_data['total_spent'] ?? 0;
                                $user_status = $data['user_status'] ?? 1;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($data['user_images'])): ?>
                                                <img src="../admin/user_images/<?= htmlspecialchars($data['user_images']) ?>"
                                                    class="avatar me-3" alt="user">
                                            <?php else: ?>
                                                <div class="avatar bg-primary me-3">
                                                    <?= strtoupper(substr($data['user'], 0, 1)) ?></div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-white"><?= htmlspecialchars($data["user"]) ?></div>
                                                <div class="small text-white-50"><?= htmlspecialchars($data["email"]) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-white-50"><?= htmlspecialchars($data["phone"]) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($data["registor_time"]) ?></td>
                                    <td><?= $total_orders ?></td>
                                    <td class="text-success fw-bold">$<?= number_format($total_spent, 2) ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                onclick="window.location.href='customers.php?uid=<?= $data['id'] ?>&val=<?= ($user_status == 1) ? 0 : 1 ?>'"
                                                <?= ($user_status == 1) ? 'checked' : '' ?> style="cursor: pointer;">
                                            <label
                                                class="form-check-label small <?= ($user_status == 1) ? 'text-success' : 'text-secondary' ?>"><?= ($user_status == 1) ? 'Active' : 'Deactive' ?></label>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="customers-detail.php?det=<?= $data['id'] ?>"
                                            class="btn btn-sm btn-outline-light text-decoration-none"><i
                                                class="bi bi-eye"></i></a>
                                        <a href="customers-delete.php?did=<?= $data['id'] ?>"
                                            class="btn btn-sm btn-outline-danger text-decoration-none"
                                            onclick="return confirm('Are you sure you want to delete this user?')"><i
                                                class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-transparent border-top border-secondary border-opacity-25">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end mb-0">
                                <?php
                                $search_param = !empty($search) ? '&search=' . urlencode($search) : '';
                                if ($page > 1) {
                                    $prev = $page - 1;
                                    echo '<li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="customers.php?page=' . $prev . $search_param . '">Previous</a></li>';
                                }

                                for ($i = 1; $i <= $total_pages; $i++) {
                                    $active = ($i == $page) ? 'active' : '';
                                    $bg = ($i == $page) ? 'bg-success border-success' : 'bg-transparent border-secondary';
                                    echo '<li class="page-item ' . $active . '"><a class="page-link ' . $bg . ' text-white" href="customers.php?page=' . $i . $search_param . '" >' . $i . '</a></li>';
                                }

                                if ($page < $total_pages) {
                                    $next = $page + 1;
                                    echo '<li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="customers.php?page=' . $next . $search_param . '">Next</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
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

        window.addEventListener('load', () => {
            const alerts = document.querySelectorAll('.alert-floating');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 3000);
            });
        });

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
    </script>
</body>

</html>