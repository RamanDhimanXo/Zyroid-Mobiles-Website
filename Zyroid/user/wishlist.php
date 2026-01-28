<?php
    session_start();
    include('../db_config.php');
    
    if (!isset($_SESSION['user']) || !isset($_SESSION['email'])) {
        header("location: login.php");
        exit();
    }

    $user_name = $_SESSION['user'];
    $user_email = $_SESSION['email'];
    
    $check_tok = mysqli_query($con, "SELECT token FROM tb_users WHERE email='$user_email'");
    $tok_row = mysqli_fetch_assoc($check_tok);
    if(!isset($_SESSION['token']) || $tok_row['token'] != $_SESSION['token']){
        session_destroy();
        header("location: login.php");
        exit();
    }

    if(isset($_GET['remove'])){
        $w_id = $_GET['remove'];
        $rem = mysqli_query($con, "DELETE FROM tb_wishlist WHERE id='$w_id' AND email='$user_email'");
        if($rem){
            $_SESSION['wish_msg'] = "Product removed from wishlist successfully";
            header("Location: wishlist.php");
            exit();
        }
    }

    $query = "SELECT w.id as w_id, p.* FROM tb_wishlist w JOIN tb_products p ON w.wishlist = p.id WHERE w.email='$user_email' ORDER BY w.id DESC";
    $result = mysqli_query($con, $query);

    $u_qr = "SELECT user_images FROM tb_users WHERE email='$user_email'";
    $u_res = mysqli_query($con, $u_qr);
    $u_data = mysqli_fetch_assoc($u_res);
    $user_img = $u_data['user_images'];
    $first_letter = strtoupper(substr($user_name, 0, 1));
    
    if (isset($_POST['logout'])) {
        mysqli_query($con, "UPDATE tb_users SET token=NULL WHERE email='$user_email'");
        session_destroy();
        header("location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Zyroid</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #009444; --bg-dark: #1a1a1a; --text-main: #ffffff; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.08); --font-head: 'Rajdhani', sans-serif; --font-body: 'Poppins', sans-serif; }
        body { background: var(--bg-dark); background-image: radial-gradient(circle at 50% 0%, #1e3c2e 0%, #1a1a1a 40%); font-family: var(--font-body); color: var(--text-main); overflow-x: hidden; min-height: 100vh; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .btn-theme, .nav-link { font-family: var(--font-head); text-transform: uppercase; letter-spacing: 0.5px; }
        
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
        .dash-sidebar { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px 20px; height: 100%; }
        .user-profile { text-align: center; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px; }
        
        .dash-link { display: flex; align-items: center; color: rgba(255,255,255,0.6); text-decoration: none; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; font-weight: 500; }
        .dash-link i { margin-right: 12px; font-size: 1.2rem; }
        .dash-link:hover, .dash-link.active { background: rgba(0, 148, 68, 0.15); color: var(--primary); border-left: 3px solid var(--primary); }
        .dash-link.logout:hover { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border-left-color: #ff4d4d; }

        .wish-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 15px; padding: 15px; transition: 0.3s; position: relative; }
        .wish-card:hover { border-color: var(--primary); transform: translateY(-5px); background: rgba(255,255,255,0.05); }
        .wish-img { height: 150px; object-fit: contain; width: 100%; margin-bottom: 15px; }
        .btn-remove { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.5); color: #ff4d4d; border: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .btn-remove:hover { background: #ff4d4d; color: white; }
        .toast-custom { position: fixed; top: 100px; right: 20px; z-index: 9999; background: rgba(0, 148, 68, 0.95); backdrop-filter: blur(5px); color: white; border-radius: 10px; padding: 15px 25px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); transform: translateX(150%); transition: 0.5s ease-out; display: flex; align-items: center; gap: 15px; }
        .toast-custom.show { transform: translateX(0); }
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

    <?php if (isset($_SESSION['wish_msg'])): ?>
        <div class="toast-custom show" id="wishToast">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div>
                <h6 class="mb-0 fw-bold">Notification</h6>
                <small><?php echo $_SESSION['wish_msg']; ?></small>
            </div>
        </div>
        <?php unset($_SESSION['wish_msg']); ?>
    <?php endif; ?>

    <div class="container dashboard-container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="dash-sidebar">
                    <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                        <?php if(!empty($user_img)): ?>
                            <img src="user_images/<?= $user_img ?>" class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover; border: 2px solid #009444;">
                        <?php else: ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?= $first_letter; ?>
                            </div>
                        <?php endif; ?>
                        <h5 class="text-white mb-0"><?php echo $user_name; ?></h5>
                        <small class="text-white-50"><?php echo $user_email; ?></small>
                    </div>
                    <nav class="nav flex-column">
                        <a href="user-overview.php" class="dash-link"><i class="bi bi-grid-fill"></i> Overview</a>
                        <a href="my-orders.php" class="dash-link"><i class="bi bi-box-seam"></i> My Orders</a>
                        <a href="wishlist.php" class="dash-link active"><i class="bi bi-heart"></i> Wishlist</a>
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
                <h2 class="text-white mb-4">My Wishlist</h2>
                
                <div class="row g-4">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-4 col-6">
                            <div class="wish-card">
                                <a href="wishlist.php?remove=<?= $row['w_id'] ?>" class="btn-remove" onclick="return confirm('Remove from wishlist?')"><i class="bi bi-x-lg"></i></a>
                                <a href="product.php?id=<?= $row['id'] ?>" class="text-decoration-none text-white">
                                    <img src="../admin/product_images/<?php echo trim(explode(',', $row['product_image'])[0]); ?>" class="wish-img" alt="<?= $row['product_name'] ?>">
                                    <h6 class="text-truncate mb-1"><?= $row['product_name'] ?></h6>
                                    <div class="text-success fw-bold">$<?= $row['product_price'] ?></div>
                                </a>
                                <a href="product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-light w-100 mt-3 rounded-pill">View Product</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-heart text-white-50" style="font-size: 3rem;"></i>
                            <h4 class="text-white mt-3">Your wishlist is empty</h4>
                            <a href="category.php" class="btn btn-outline-success rounded-pill mt-3 px-4">Explore Products</a>
                        </div>
                    <?php endif; ?>
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

        const toast = document.getElementById('wishToast');
        if(toast) {
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }
    </script>
</body>
</html>