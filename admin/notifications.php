<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_email'])) {
    header("location: index.php");
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_res = mysqli_query($con, "SELECT COUNT(*) as total FROM tb_notifications WHERE type IN ('order', 'comment', 'user', 'product_add', 'product_edit', 'product_delete', 'user_delete')");
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $limit);

$user_notifs = [];

$res = mysqli_query($con, "SELECT * FROM tb_notifications WHERE type IN ('order', 'comment', 'user', 'product_add', 'product_edit', 'product_delete', 'user_delete') ORDER BY id DESC LIMIT $offset, $limit");
while ($row = mysqli_fetch_assoc($res)) {
    $icon = 'bi-bell';
    $color = 'primary';
    $link = '#';

    if ($row['type'] == 'order') {
        $icon = 'bi-cart-check-fill';
        $color = 'success';
        $link = 'orders.php';
    } elseif ($row['type'] == 'comment') {
        $icon = 'bi-chat-quote-fill';
        $color = 'info';
        $link = 'comments.php';
    } elseif ($row['type'] == 'user') {
        $icon = 'bi-person-plus-fill';
        $color = 'warning';
        $link = 'customers.php';
    } elseif ($row['type'] == 'product_add') {
        $icon = 'bi-plus-circle-fill';
        $color = 'success';
        $link = 'latest_products.php';
    } elseif ($row['type'] == 'product_edit') {
        $icon = 'bi-pencil-fill';
        $color = 'info';
        $link = 'latest_products.php';
    } elseif ($row['type'] == 'product_delete') {
        $icon = 'bi-trash-fill';
        $color = 'danger';
        $link = 'latest_products.php';
    } elseif ($row['type'] == 'user_delete') {
        $icon = 'bi-person-x-fill';
        $color = 'danger';
        $link = 'customers.php';
    }

    $notification_data = [
        'id' => $row['id'],
        'type' => $row['type'],
        'msg' => $row['message'],
        'time' => $row['created_at'],
        'link' => $link,
        'icon' => $icon,
        'color' => $color,
        'is_read' => $row['is_read']
    ];

    $user_notifs[] = $notification_data;
}

mysqli_query($con, "UPDATE tb_notifications SET is_read = 1 WHERE is_read = 2 AND type IN ('order', 'comment', 'user', 'product_add', 'product_edit', 'product_delete', 'user_delete')");
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Zyroid Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
            transition: 0.2s;
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: all 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
        }

        .notif-item {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
            transition: 0.3s;
            display: flex;
            gap: 15px;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .notif-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .notif-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        #sidebarToggle { display: none; }

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
    <div id="overlay"></div>
    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="bi bi-phone me-2"></i>ZYROID
        </a>
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
                <?php $cat_pages = ['latest_products.php', 'iphone_products.php', 'android_products.php', 'gaming_products.php', 'accessories.php', 'hot_deals.php', 'edit_products.php']; ?>
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
                <a class="nav-link <?php echo ($current_page == 'customer_review.php') ? 'active' : ''; ?>" href="customer_review.php">
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i
                        class="bi bi-list"></i></button>
                <h2 class="h3 fw-bold mb-0">Notifications</h2>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                        <h6 class="m-0 fw-bold text-white"><i class="bi bi-people-fill me-2"></i>User Activity</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($user_notifs) > 0): ?>
                            <?php foreach ($user_notifs as $n): ?>
                                <div class="notif-item" id="notif-<?= $n['id'] ?>"
                                    style="<?= ($n['is_read'] == 2) ? 'background: rgba(255,255,255,0.05);' : '' ?>">
                                    <div class="notif-icon bg-<?= $n['color'] ?> bg-opacity-25 text-<?= $n['color'] ?>">
                                        <i class="bi <?= $n['icon'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <a href="<?= $n['link'] ?>" class="text-decoration-none">
                                            <h6 class="mb-1 fw-bold text-white"><?= $n['msg'] ?></h6>
                                        </a>
                                        <small class="text-white-50" style="font-size: 0.75rem;"><?= $n['time'] ?></small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger delete-notif" data-id="<?= $n['id'] ?>">
                                        <i class="bi bi-trash" style="color: #ff4757;"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-5 text-center text-white-50">No user activity.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="?page=<?= $page - 1 ?>">Previous</a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link <?= ($i == $page) ? 'bg-success border-success' : 'bg-transparent border-secondary' ?> text-white" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link bg-transparent border-secondary text-white" href="?page=<?= $page + 1 ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
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

        function showAlert(message, type = 'success') {
            const container = document.querySelector('.main-content');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-floating bg-${type} text-white border-0`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                ${message}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.prepend(alertDiv);
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertDiv);
                if (bsAlert) bsAlert.close();
            }, 3000);
        }

        window.addEventListener('load', () => {
            document.querySelectorAll('.alert-floating').forEach(alert => {
                setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), 3000);
            });
        });

        document.querySelectorAll('.delete-notif').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const item = document.getElementById(`notif-${id}`);

                if (this.disabled) return;

                if (confirm('Are you sure you want to delete this notification?')) {
                    this.disabled = true;
                    fetch(`notification_delete.php?id=${id}&ajax=1`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('Notification Deleted Successfully');
                                gsap.to(item, {
                                    x: 50,
                                    opacity: 0,
                                    height: 0,
                                    paddingTop: 0,
                                    paddingBottom: 0,
                                    marginBottom: 0,
                                    borderBottomWidth: 0,
                                    duration: 0.4,
                                    ease: "power3.in",
                                    onComplete: () => {
                                        const parentContainer = item.parentElement;
                                        item.remove();
                                        if (parentContainer.querySelectorAll('.notif-item').length === 0) {
                                            parentContainer.innerHTML = `
                                            <div class="p-5 text-center text-white-50">
                                                <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                                No recent notifications found.
                                            </div>
                                        `;
                                        }
                                    }
                                });
                            } else {
                                alert('Failed to delete notification');
                                this.disabled = false;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            this.disabled = false;
                        });
                }
            });
        });
    </script>
</body>

</html>