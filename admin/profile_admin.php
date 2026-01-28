<?php
    session_start();
    include('../db_config.php');
    $current_page = basename($_SERVER['PHP_SELF']);
    $email_sess = $_SESSION['admin_email'];
    $qr = "SELECT * FROM `tb_admin` WHERE email='$email_sess'";
    $res = mysqli_query($con,$qr);
    $data = mysqli_fetch_assoc($res);
    if(isset($_POST['update'])){
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $upd = "UPDATE `tb_admin` SET `fullname`='$name',`email`='$email',`phone`='$phone' WHERE email='$email_sess'";
        $res2 = mysqli_query($con,$upd);
        if($res2){
            $_SESSION['admin_email'] = $email; 
            echo "<script>alert('Update Your Data Successfully'); window.location.href='profile_admin.php';</script>";
        }else{
            echo"<script>alert('Something Went Wrong');</script>";
        }
    }
    
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Zyroid Theme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #009444 ;
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

        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background: var(--bg-surface); border-right: 1px solid rgba(255, 255, 255, 0.05); z-index: 1050; overflow-y: auto; transition: all 0.3s ease; transform: translateX(0); white-space: nowrap; overflow-x: hidden; }
        
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }

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
        ul{
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
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border-radius: 12px;
        }

        .avatar-xl {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #26282c;
            border: 4px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #fff;
            margin: 0 auto;
        }

        .nav-pills .nav-link {
            color: rgba(255,255,255,0.7);
            background: transparent;
            border: 1px solid transparent;
            margin-bottom: 5px;
            text-align: left;
            padding: 12px 20px;
            border-radius: 8px;
        }
        
        .nav-pills .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 148, 68, 0.4);
        }

        .nav-pills .nav-link i { margin-right: 10px; }

        .form-label { color: rgba(255,255,255,0.7); font-size: 0.9rem; }
        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #fff;
            padding: 0.7rem 1rem;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 148, 68, 0.25);
            color: #fff;
        }

        .timeline-item {
            padding-left: 20px;
            border-left: 2px solid rgba(255,255,255,0.1);
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            width: 10px; height: 10px;
            background: var(--primary-color);
            border-radius: 50%;
            position: absolute;
            left: -6px; top: 5px;
        }

        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background: #007a38; }

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
        
        #sidebarToggle i { transition: transform 0.3s ease; }
        #sidebarToggle.active i { transform: rotate(90deg); }
        
        #sidebarToggle { display: none; }
        #overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 999; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .main-content { animation: fadeInUp 0.6s ease-out forwards; }

        .glow-shape {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(111, 66, 193, 0.15) 0%, rgba(0,0,0,0) 70%);
            z-index: 0; pointer-events: none;
            animation: pulseGlow 8s infinite alternate;
        }
        #particles { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        .particle { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.05); animation: floatUp linear infinite; }
        .main-content { position: relative; z-index: 1; }
        @keyframes pulseGlow { 0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.4; } 100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.7; } }
        @keyframes floatUp { 0% { transform: translateY(100vh) scale(0); opacity: 0; } 20% { opacity: 1; } 100% { transform: translateY(-10vh) scale(1); opacity: 0; } }
        
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-head); }
        
        @media (max-width: 991.98px) {
            #sidebarToggle { display: inline-block; }
            #overlay { display: block; }
            #overlay.active { opacity: 1; visibility: visible; }
        }

        h1, h2, h3, h4, h5, h6 { font-family: var(--font-head); }
    </style>
</head>

<body>

    <div class="glow-shape"></div>
    <div id="particles"></div>
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
                <a class="nav-link <?php echo ($current_page == 'orders.php' || $current_page == 'order_edit.php') ? 'active' : ''; ?>" href="orders.php">
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
                <h2 class="h3 fw-bold mb-0">Admin Profile</h2>
            </div>
            <span class="text-white-50">Last login: Today, 10:45 AM</span>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-4 col-xl-3">
                
                <div class="card text-center p-4 mb-4">
                    <div class="avatar-xl mb-3">
                         <?php if($data['fullname']){ echo ucfirst(substr($data['fullname'], 0, 1)); } ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo $data['fullname'];?></h5>
                    <p class="text-white-50 small mb-3"><?php echo $data['email'];?></p>
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 py-2">Role: Owner</span>
                </div>

                <div class="card p-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="v-pills-edit-tab" data-bs-toggle="pill" data-bs-target="#v-pills-edit" type="button"><i class="bi bi-pencil-square"></i>Edit Profile</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="card p-4 h-100">
                    <div class="tab-content" id="v-pills-tabContent">
                        
                        <div class="tab-pane fade show active" id="v-pills-edit" role="tabpanel">
                            <h5 class="fw-bold text-primary mb-4">Personal Details</h5>
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo $data['fullname'];?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo $data['email'];?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo $data['phone'];?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Bio / Notes</label>
                                        <textarea class="form-control" rows="3">Main administrator account for the mobile e-commerce platform.</textarea>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary" name="update">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function reset(){
            confirm('your sure to reset this');
        }

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