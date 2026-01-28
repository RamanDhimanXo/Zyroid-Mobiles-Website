<?php 
    session_start();
    include('../db_config.php');

    class user_profile{
        public $name;
        public $email;
        
        function __construct($name, $email){
            $this->name = $name;
            $this->email = $email;
        }
    }

    $obj = null; 
    $user_data = []; 

    if(isset($_SESSION['user']) && isset($_SESSION['email'])) {
        $user_sess = $_SESSION['user'];
        $email_sess = $_SESSION['email'];

        $sel = "SELECT * FROM tb_users WHERE `email`= '$email_sess'";
        $res = mysqli_query($con, $sel);

        if(mysqli_num_rows($res) > 0){
            $user_data = mysqli_fetch_assoc($res);
            
            if(!isset($_SESSION['token']) || $user_data['token'] != $_SESSION['token']){
                session_destroy();
                header("location: login.php");
                exit();
            }
            $obj = new user_profile($user_data['user'], $user_data['email']);
        }
    } else {
        header("location: login.php");
        exit();
    }

    if(isset($_POST['logout'])){
        if(isset($_SESSION['email'])) { mysqli_query($con, "UPDATE tb_users SET token=NULL WHERE email='".$_SESSION['email']."'"); }
        session_destroy();
        header("location: login.php");
        exit();
    }

    $msg = "";
    if(isset($_POST['update_btn'])) {
        $name_update = $_POST['full_name'];
        $phone_update = $_POST['phone_num'];
        $id_update = $user_data['id'];

        $image_query = "";
        if($_FILES['profile_img']['name'] != "") {
            $img_name = $_FILES['profile_img']['name'];
            $path = "../admin/user_images/"; 
            move_uploaded_file($_FILES['profile_img']['tmp_name'],$path.$img_name);
            $image_query = ", `user_images`='$img_name'";
        }

        $upd = "UPDATE `tb_users` SET `user`='$name_update', `phone`='$phone_update' $image_query WHERE `id`='$id_update'";
        
        if(mysqli_query($con, $upd)) {
            $msg = "<div class='alert alert-success'>Profile Updated Successfully!</div>";
            $_SESSION['user'] = $name_update;
            $obj->name = $name_update; 
            $res = mysqli_query($con, $sel);
            $user_data = mysqli_fetch_assoc($res);
        } else {
            $msg = "<div class='alert alert-danger'>Update Failed!</div>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Zyroid</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #009444; --bg-dark: #1a1a1a; --text-main: #ffffff; --glass: rgba(255, 255, 255, 0.03); --glass-border: rgba(255, 255, 255, 0.08); --transition: all 0.3s ease-in-out; --font-head: 'Rajdhani', sans-serif; --font-body: 'Poppins', sans-serif; }
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
        .dash-link { display: flex; align-items: center; color: rgba(255,255,255,0.6); text-decoration: none; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; transition: 0.3s; font-weight: 500; }
        .dash-link i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        .dash-link:hover, .dash-link.active { background: rgba(0, 148, 68, 0.15); color: var(--primary); border-left: 3px solid var(--primary); }
        .dash-link.logout:hover { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border-left-color: #ff4d4d; }

        .profile-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; }
        .avatar-box { width: 120px; height: 120px; position: relative; margin: 0 auto 20px; }
        .avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid var(--primary); padding: 3px; }
        .avatar-initial { width: 100%; height: 100%; background: #2c3e50; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: var(--primary); border: 3px solid var(--primary); }
        
        .form-control { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 10px; }
        .form-control:focus { background: rgba(255,255,255,0.1); border-color: var(--primary); color: white; box-shadow: none; }
        .form-label { color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 8px; }
        
        .btn-theme { background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: 0.3s; }
        .btn-theme:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,148,68,0.3); }

        @media (max-width: 768px) {
            .profile-card { padding: 20px; }
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
                        <?php if(!empty($user_data['user_images'])): ?>
                            <img src="../admin/user_images/<?= $user_data['user_images'] ?>" class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover; border: 2px solid #009444;">
                        <?php else: ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?= strtoupper(substr($obj->name, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5 class="text-white mb-0"><?= $obj->name; ?></h5>
                        <small class="text-white-50"><?= $obj->email; ?></small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a href="user-overview.php" class="dash-link"><i class="bi bi-grid-fill"></i> Overview</a>
                        <a href="my-orders.php" class="dash-link"><i class="bi bi-box-seam"></i> My Orders</a>
                        <a href="wishlist.php" class="dash-link"><i class="bi bi-heart"></i> Wishlist</a>
                        <a href="user_profile.php" class="dash-link active"><i class="bi bi-person-circle"></i> My Profile</a>
                        
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
                    <h2 class="text-white">Edit Profile</h2>
                </div>

                <?= $msg; ?>

                <div class="profile-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="avatar-box">
                                    <?php if(!empty($user_data['user_images'])): ?>
                                        <img src="../admin/user_images/<?=$user_data['user_images'] ?>" class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-initial"><?= strtoupper(substr($obj->name, 0, 1)); ?></div>
                                    <?php endif; ?>
                                </div>
                                <label class="btn btn-outline-light btn-sm rounded-pill mt-2">
                                    <i class="bi bi-camera me-1"></i> Change Photo
                                    <input type="file" name="profile_img" style="display: none;">
                                </label>
                                <p class="text-white-50 small mt-2">Allowed: JPG, PNG</p>
                            </div>

                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?= $user_data['user']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone_num" class="form-control" value="<?= $user_data['phone']; ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control bg-light text-dark" value="<?= $user_data['email']; ?>" readonly disabled style="cursor: not-allowed;">
                                        <small class="text-white-50">Email cannot be changed.</small>
                                    </div>

                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" name="update_btn" class="btn-theme px-4">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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