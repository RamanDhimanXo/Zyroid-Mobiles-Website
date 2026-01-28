<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content & Support - Zyroid Theme</title>
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

        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background: var(--bg-surface); border-right: 1px solid var(--border-color); z-index: 1000; overflow-y: auto; transition: all 0.3s ease; transform: translateX(0); white-space: nowrap; overflow-x: hidden; }

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
            margin-bottom: 24px;
        }

        .banner-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            height: 160px;
        }

        .banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .banner-card:hover .banner-img {
            transform: scale(1.05);
        }

        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .banner-card:hover .banner-overlay {
            opacity: 1;
        }

        .ticket-priority-high {
            border-left: 3px solid #dc3545;
        }

        .ticket-priority-med {
            border-left: 3px solid #ffc107;
        }

        .ticket-priority-low {
            border-left: 3px solid #0dcaf0;
        }

        .form-control,
        .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #fff;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            color: #fff;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.6);
            border: none;
            padding: 10px 20px;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: transparent;
            border-bottom: 2px solid var(--primary-color);
        }

        .nav-tabs .nav-link:hover {
            color: #fff;
        }

        .table {
            min-width: 800px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: #007a38;
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
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
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
                    <h2 class="h3 fw-bold mb-1">Content & Support</h2>
                    <p class="text-white-50 mb-0">Manage website banners and customer inquiries</p>
                </div>
            </div>
            <button class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create New</button>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="card-header bg-transparent p-0">
                <ul class="nav nav-tabs px-3 pt-2" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners"
                            type="button"><i class="bi bi-images me-2"></i>Banners & Ads</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets"
                            type="button"><i class="bi bi-ticket-perforated me-2"></i>Support Tickets</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq"
                            type="button"><i class="bi bi-question-circle me-2"></i>FAQs</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade show active" id="banners" role="tabpanel">
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Homepage Sliders</h5>
                            <button class="btn btn-sm btn-outline-light">Upload New</button>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 col-lg-4">
                                <div class="banner-card">
                                    <img src="https://via.placeholder.com/400x200/009444/ffffff?text=Summer+Sale"
                                        class="banner-img" alt="Banner">
                                    <div class="banner-overlay">
                                        <button class="btn btn-sm btn-light me-2"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-white-50">Main Hero Slider</small>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="banner-card">
                                    <img src="https://via.placeholder.com/400x200/6f42c1/ffffff?text=New+iPhone+15"
                                        class="banner-img" alt="Banner">
                                    <div class="banner-overlay">
                                        <button class="btn btn-sm btn-light me-2"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-white-50">Product Launch</small>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="banner-card d-flex flex-column align-items-center justify-content-center border-dashed"
                                    style="border-style: dashed; background: rgba(255,255,255,0.02);">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-white-50"></i>
                                    <span class="text-white-50 mt-2 small">Upload Image</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tickets" role="tabpanel">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <h5 class="fw-bold mb-0">Recent Inquiries</h5>
                            <div class="input-group w-auto">
                                <input type="text" class="form-control form-control-sm" placeholder="Search ticket...">
                                <button class="btn btn-outline-light btn-sm"><i class="bi bi-search"></i></button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark bg-transparent table-hover align-middle mb-0">
                                <thead class="text-white-50 small text-uppercase">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Subject</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="ticket-priority-high">
                                        <td>#TKT-902</td>
                                        <td><span class="fw-bold text-white">Item received damaged</span></td>
                                        <td>John Doe</td>
                                        <td><span class="badge bg-warning text-dark">Open</span></td>
                                        <td><span class="text-danger small fw-bold">High</span></td>
                                        <td class="text-end"><button class="btn btn-sm btn-primary">Reply</button></td>
                                    </tr>
                                    <tr class="ticket-priority-med">
                                        <td>#TKT-885</td>
                                        <td><span class="fw-bold text-white">Shipping delay inquiry</span></td>
                                        <td>Sarah Smith</td>
                                        <td><span class="badge bg-info">In Progress</span></td>
                                        <td><span class="text-warning small fw-bold">Medium</span></td>
                                        <td class="text-end"><button class="btn btn-sm btn-outline-light">View</button>
                                        </td>
                                    </tr>
                                    <tr class="ticket-priority-low">
                                        <td>#TKT-821</td>
                                        <td><span class="fw-bold text-white">How to return item?</span></td>
                                        <td>Mike Ross</td>
                                        <td><span class="badge bg-success">Closed</span></td>
                                        <td><span class="text-info small fw-bold">Low</span></td>
                                        <td class="text-end"><button class="btn btn-sm btn-outline-light">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="faq" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-4 mb-4 mb-lg-0">
                                <div class="card bg-black bg-opacity-25 border-secondary border-opacity-25 p-3">
                                    <h6 class="fw-bold mb-3">Add New FAQ</h6>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label small">Question</label>
                                            <input type="text" class="form-control"
                                                placeholder="e.g. How to track order?">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small">Answer</label>
                                            <textarea class="form-control" rows="4"
                                                placeholder="Enter answer here..."></textarea>
                                        </div>
                                        <button class="btn btn-primary w-100">Add FAQ</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <h6 class="fw-bold mb-3">Active FAQs</h6>
                                <div class="accordion" id="faqAccordion">

                                    <div class="accordion-item bg-transparent border-secondary border-opacity-25 mb-2">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button collapsed bg-white bg-opacity-10 text-white shadow-none"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                How do I track my order?
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse"
                                            data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-white-50 small">
                                                You can track your order by logging into your account and visiting the
                                                "My Orders" section. Alternatively, use the tracking ID sent to your
                                                email.
                                                <div class="mt-2 text-end">
                                                    <a href="#"
                                                        class="text-primary text-decoration-none small me-2">Edit</a>
                                                    <a href="#"
                                                        class="text-danger text-decoration-none small">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item bg-transparent border-secondary border-opacity-25">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button collapsed bg-white bg-opacity-10 text-white shadow-none"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                                What is the return policy?
                                            </button>
                                        </h2>
                                        <div id="collapseTwo" class="accordion-collapse collapse"
                                            data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-white-50 small">
                                                We accept returns within 30 days of purchase. Items must be in original
                                                condition with tags attached.
                                                <div class="mt-2 text-end">
                                                    <a href="#"
                                                        class="text-primary text-decoration-none small me-2">Edit</a>
                                                    <a href="#"
                                                        class="text-danger text-decoration-none small">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

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