<?php
    session_start();
    include('../db_config.php');

    if (!isset($_SESSION['user']) || !isset($_SESSION['email'])) {
        header("location: login.php");
        exit();
    }

    if(isset($_POST['logout'])){
        if(isset($_SESSION['email'])) { mysqli_query($con, "UPDATE tb_users SET token=NULL WHERE email='".$_SESSION['email']."'"); }
        session_destroy();
        header("location: login.php");
        exit();
    }

    $email_sess = $_SESSION['email'];
    $query = "SELECT * FROM tb_users WHERE email = '$email_sess'";
    $res = mysqli_query($con, $query);
    
    if(mysqli_num_rows($res) > 0){
        $user_data = mysqli_fetch_assoc($res);
        
        if(!isset($_SESSION['token']) || $user_data['token'] != $_SESSION['token']){
            session_destroy();
            header("location: login.php");
            exit();
        }
        
        $user_name = $user_data['user'];
        $user_email = $user_data['email'];
        $user_img = $user_data['user_images'];
        
        $ord_q = "SELECT COUNT(*) as total_orders, SUM(amount) as total_spent FROM tb_orders WHERE email = '$user_email'";
        $ord_res = mysqli_query($con, $ord_q);
        $ord_data = mysqli_fetch_assoc($ord_res);

        $total_orders = $ord_data['total_orders'] ? $ord_data['total_orders'] : 0;
        $total_spent = $ord_data['total_spent'] ? number_format($ord_data['total_spent'], 2) : '0.00';
    } else {
        $user_name = "User";
        $total_orders = 0;
        $total_spent = 0;
    }

    $wish_q = "SELECT COUNT(*) as count FROM tb_wishlist WHERE email = '$email_sess'";
    $wish_res = mysqli_query($con, $wish_q);
    $wish_data = mysqli_fetch_assoc($wish_res);
    $wishlist_count = $wish_data['count'];

    $user_initial = strtoupper(substr($user_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Zyroid</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #009444; --bg-dark: #1a1a1a; --text-main: #ffffff; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.08); --transition: all 0.3s ease-in-out; --font-head: 'Rajdhani', sans-serif; --font-body: 'Poppins', sans-serif; }
        body { background: var(--bg-dark); background-image: radial-gradient(circle at 50% 0%, #1e3c2e 0%, #1a1a1a 40%); font-family: var(--font-body); color: var(--text-main); overflow-x: hidden; min-height: 100vh; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .btn-theme, .nav-link, .stat-num { font-family: var(--font-head); text-transform: uppercase; letter-spacing: 0.5px; }

        .navbar-custom { padding: 15px 0; background: transparent; position: absolute; width: 100%; z-index: 1000; transition: 0.4s; }
        .navbar-custom.scrolled { background: rgba(26, 26, 26, 0.95); backdrop-filter: blur(10px); padding: 10px 0; border-bottom: 1px solid var(--glass-border); position: fixed; top: 0; }
        .nav-link { color: rgba(255, 255, 255, 0.8) !important; font-weight: 600; position: relative; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .nav-link::after { content: ''; position: absolute; bottom: 5px; left: 0; width: 0%; height: 2px; background: var(--primary); transition: width 0.3s ease-in-out; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .dropdown-menu { background: rgba(10, 10, 10, 0.95); border: 1px solid rgba(255, 255, 255, 0.15); margin-top: 10px; }
        .dropdown-item { color: rgba(255, 255, 255, 0.8); padding: 10px 20px; transition: 0.2s; }
        .dropdown-item:hover { background: var(--primary); color: white; padding-left: 25px; }
        .custom-toggler { border: none; background: transparent; padding: 5px; display: flex; flex-direction: column; gap: 6px; width: 40px; cursor: pointer; }
        .custom-toggler span { display: block; width: 100%; height: 3px; background: white; border-radius: 5px; transition: 0.4s; }
        .custom-toggler.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); background: var(--primary); }
        .custom-toggler.open span:nth-child(2) { opacity: 0; }
        .custom-toggler.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); background: var(--primary); }
        @media (max-width: 991px) { .navbar-collapse { background: rgba(26, 26, 26, 0.98); backdrop-filter: blur(15px); padding: 20px; border-radius: 15px; margin-top: 15px; border: 1px solid rgba(255, 255, 255, 0.1); } }

        .dashboard-container { padding-top: 130px; padding-bottom: 50px; }
        .dash-sidebar { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px 20px; height: 100%; backdrop-filter: blur(20px); }
        .dash-link { display: flex; align-items: center; color: rgba(255,255,255,0.6); text-decoration: none; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; font-weight: 500; }
        .dash-link i { margin-right: 12px; font-size: 1.2rem; }
        .dash-link:hover, .dash-link.active { background: rgba(0, 148, 68, 0.15); color: var(--primary); border-left: 3px solid var(--primary); }
        .dash-link.logout:hover { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border-left-color: #ff4d4d; }

        .stat-card { background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; position: relative; overflow: hidden; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .stat-icon { font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; display: block; }
        .stat-num { font-size: 2.5rem; font-weight: 700; color: white; margin-bottom: 5px; display: block; }

        @media (max-width: 768px) {
            .dashboard-container { padding-top: 80px; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand text-white fw-bold fs-3" href="../index.php">
                Zyroid<span style="color: var(--primary)">.</span>
            </a>

            <button class="navbar-toggler custom-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#nav">
                <span></span><span></span><span></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto gap-3 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Mobiles</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="category.php?cat=all">All Mobiles</a></li>
                            <?php
                            $nav_cat_qr = "SELECT * FROM tb_categories";
                            $nav_cat_res = mysqli_query($con, $nav_cat_qr);
                            if ($nav_cat_res) {
                                while ($nav_row = mysqli_fetch_assoc($nav_cat_res)) {
                                    echo '<li><a class="dropdown-item" href="category.php?cat=' . urlencode($nav_row['cat_name']) . '">' . $nav_row['cat_name'] . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>

                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>

                    <?php if (isset($_SESSION['user'])) { ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex align-items-center gap-2 dropdown-toggle"
                                data-bs-toggle="dropdown">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                    style="width: 35px; height: 35px;">
                                    <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
                                </div>
                                <span><?php echo $_SESSION['user']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary">
                                </li>
                                <li>
                                    <form method="post">
                                        <button type="submit" name="logout"
                                            class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn-theme ms-2">Sign In</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <div class="row">
            
            <div class="col-lg-3 mb-4">
                <div class="dash-sidebar">
                    <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                        <?php if(!empty($user_img)): ?>
                            <img src="user_images/<?= $user_img ?>" class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover; border: 2px solid #009444;">
                        <?php else: ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?= $user_initial; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5 class="text-white mb-0"><?php echo $user_name; ?></h5>
                        <small class="text-white-50"><?php echo $user_email; ?></small>
                    </div>

                    <nav class="nav flex-column">
                        <a href="user-overview.php" class="dash-link active"><i class="bi bi-grid-fill"></i> Overview</a>
                        <a href="my-orders.php" class="dash-link"><i class="bi bi-box-seam"></i> My Orders</a>
                        <a href="wishlist.php" class="dash-link"><i class="bi bi-heart"></i> Wishlist</a>
                        <a href="user_profile.php" class="dash-link"><i class="bi bi-person-circle"></i> My Profile</a>
                        
                        <form method="post" class="w-100">
                            <button type="submit" name="logout" class="dash-link logout mt-4 w-100 border-0 text-start bg-transparent">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">Dashboard Overview</h2>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <i class="bi bi-bag-check stat-icon"></i>
                            <h2 class="stat-num"><?php echo $total_orders; ?></h2>
                            <span class="text-white-50">Total Orders Placed</span>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card">
                            <i class="bi bi-wallet2 stat-icon text-warning"></i>
                            <h2 class="stat-num">$<?php echo $total_spent; ?></h2>
                            <span class="text-white-50">Total Amount Spent</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-4 rounded-4" style="background: rgba(0, 148, 68, 0.1); border: 1px solid rgba(0, 148, 68, 0.2);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3">🚀</div>
                        <div>
                            <h4 class="text-white mb-1">Welcome back, <?php echo $user_name; ?>!</h4>
                            <p class="text-white-50 mb-0">Check your latest orders and update your profile details.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        const toggler = document.querySelector('.custom-toggler');
        if(toggler) toggler.addEventListener('click', () => toggler.classList.toggle('open'));
    </script>
</body>
</html>