<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_email'])) {
    header("location: index.php");
    exit();
}

if (isset($_GET['del'])) {
    $id = mysqli_real_escape_string($con, $_GET['del']);
    if (mysqli_query($con, "DELETE FROM tb_comments WHERE id='$id'")) {
        $_SESSION['success'] = "Review deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete review";
    }
    header("location: customer_review.php");
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = "";
$where_clause = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $where_clause = " WHERE p.product_name LIKE '%$search%' OR c.user_name LIKE '%$search%' OR c.comment LIKE '%$search%' ";
}

$count_qr = "SELECT COUNT(*) as total FROM tb_comments c LEFT JOIN tb_products p ON c.product_id = p.id $where_clause";
$count_res = mysqli_query($con, $count_qr);
$total_records = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_records / $limit);

$qr = "SELECT c.*, p.product_name, p.product_image FROM tb_comments c 
       LEFT JOIN tb_products p ON c.product_id = p.id 
       $where_clause 
       ORDER BY c.id DESC LIMIT $offset, $limit";
$res = mysqli_query($con, $qr);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Zyroid Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-width: 260px; --primary-color: #009444; --bg-body: #1a1a1a; --bg-surface: rgb(37 37 37); --border-color: rgba(255, 255, 255, 0.05); --font-head: 'Rajdhani', sans-serif; --font-body: 'Poppins', sans-serif; }
        body { background: var(--bg-body); font-family: var(--font-body); color: #fff; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background: var(--bg-surface); border-right: 1px solid var(--border-color); z-index: 1050; transition: all 0.3s ease; white-space: nowrap; overflow-x: hidden; }
        
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }

        .sidebar-brand { padding: 1.5rem; color: #fff; font-size: 1.5rem; text-align: center; font-weight: bold; border-bottom: 1px solid var(--border-color); text-decoration: none; display: block; font-family: var(--font-head); }
        .nav-link { color: rgba(255, 255, 255, 0.6); padding: 16px 2.2rem; display: flex; align-items: center; text-decoration: none; font-family: var(--font-head); transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, 0.05); border-left: 4px solid var(--primary-color); }
        .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        .main-content { margin-left: var(--sidebar-width); padding: 25px; position: relative; z-index: 1; transition: all 0.3s ease; }
        .card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .table-custom { --bs-table-bg: transparent; color: #e0e0e0; }
        .product-img { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); }
        .alert-floating { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.5s ease-out; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .glow-shape { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(0, 148, 68, 0.1) 0%, rgba(0,0,0,0) 70%); z-index: 0; pointer-events: none; }
        #particles { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        .particle { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.05); animation: floatUp linear infinite; }
        
        #sidebarToggle { display: none; }
        #overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.4s; }
        
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            #sidebarToggle { display: inline-block; }
            #overlay { display: block; }
            #overlay.active { opacity: 1; visibility: visible; }
        }

        @keyframes floatUp { 0% { transform: translateY(100vh) scale(0); opacity: 0; } 20% { opacity: 1; } 100% { transform: translateY(-10vh) scale(1); opacity: 0; } }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-head); }
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
                <a class="nav-link <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center" href="#customerSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'true' : 'false'; ?>" aria-controls="customerSubmenu">
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
            <div class="alert alert-success alert-dismissible fade show alert-floating bg-success text-white border-0">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <button id="sidebarToggle" class="btn btn-outline-light me-2"><i class="bi bi-list"></i></button>
            <div>
                <h2 class="h3 fw-bold mb-1">Customer Reviews</h2>
                <p class="text-white-50 mb-0">Manage and moderate user feedback</p>
            </div>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Search product or user..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary px-4">Search</button>
            </form>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-custom mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Product</th>
                            <th>User</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($res) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($res)): 
                                $img_array = explode(',', $row['product_image']);
                                $thumb = isset($img_array[0]) ? trim($img_array[0]) : '';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="product_images/<?php echo $thumb; ?>" class="product-img me-3" onerror="this.src='https://via.placeholder.com/45'">
                                        <span class="fw-bold text-white"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                </td>
                                <td class="text-white-50 small" style="max-width: 400px;">
                                    <?php echo htmlspecialchars($row['comment']); ?>
                                </td>
                                <td><span class="text-white-50"><?php echo $row['date']; ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="customer_review.php?del=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this review?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-white-50">No reviews found matching your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-3">
                    <nav>
                        <ul class="pagination justify-content-end mb-0">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link <?php echo ($page == $i) ? 'bg-success border-success' : 'bg-transparent border-secondary text-white'; ?>" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
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
    </script>
</body>
</html>