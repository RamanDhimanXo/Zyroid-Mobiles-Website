<?php
    session_start();
    include('../db_config.php');
    $current_page = basename($_SERVER['PHP_SELF']);

    if(!isset($_SESSION['admin_email'])){
        header("location: index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Zyroid Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
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
            margin-left: -40px;
        }
        
        .nav-item{
            margin-left: 40px;

        }
        
        
        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        ul { list-style-type: none; padding: 0; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: all 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border-radius: 12px;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 148, 68, 0.25);
            color: #fff;
        }

        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background: #007a38; }

        #sidebarToggle { display: none; }
        #overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            #sidebarToggle { display: inline-block; }
            #overlay { display: block; }
            #overlay.active { opacity: 1; visibility: visible; }
        }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-head); }
    </style>
</head>
<body>


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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i class="bi bi-list"></i></button>
                <h2 class="h3 fw-bold mb-0">General Settings</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card p-4">
                    <form method="POST">
                        <h5 class="mb-4 text-success">Store Configuration</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Store Name</label>
                            <input disabled type="text" class="form-control" name="site_name" value="Zyroid Mobiles">
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Support Email</label>
                                <input disabled type="email" class="form-control" name="support_email" value="support@zyroid.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Support Phone</label>
                                <input disabled type="text" class="form-control" name="support_phone" value="+1 234 567 890">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <select class="form-select" disabled name="currency">
                                <option value="$" selected>USD ($)</option>
                                <option value="€">EUR (€)</option>
                                <option value="£">GBP (£)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Shipping Fee ($)</label>
                            <input disabled type="number" class="form-control" name="shipping_fee" value="0.00">
                            <div class="form-text text-white-50">Set to 0 for free shipping.</div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input disabled class="form-check-input" type="checkbox" id="maintenanceMode">
                            <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                        </div>

                    </form>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="card p-4 mb-4">
                    <h5 class="mb-3 text-success">System Info</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2 d-flex justify-content-between"><span>PHP Version:</span> <span class="text-white"><?php echo phpversion(); ?></span></li>
                        <li class="mb-2 d-flex justify-content-between"><span>Server:</span> <span class="text-white"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></li>
                        <li class="mb-2 d-flex justify-content-between"><span>Database:</span> <span class="text-white">MySQL</span></li> 
                    </ul>
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
    </script>
</body>
</html>