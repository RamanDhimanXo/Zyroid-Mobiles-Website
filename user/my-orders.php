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

    $query = "SELECT * FROM tb_orders WHERE email='$user_email' ORDER BY id DESC";
    $result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Zyroid</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #009444;
            --bg-dark: #1a1a1a;
            --text-main: #ffffff;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --bs-table-bg: transparent !important;
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-dark);
            background-image: radial-gradient(circle at 50% 0%, #1e3c2e 0%, #1a1a1a 40%);
            font-family: var(--font-body);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6, .navbar-brand, .btn-theme, .nav-link {
            font-family: var(--font-head);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
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

        .dashboard-container {
            padding-top: 130px;
            padding-bottom: 50px;
        }

        .dash-sidebar {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px 20px;
            height: 100%;
        }
        
        .user-profile {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 20px;
        }
        
        .dash-link {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .dash-link i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        
        .dash-link:hover, .dash-link.active {
            background: rgba(0, 148, 68, 0.15);
            color: var(--primary);
            padding-left: 20px;
        }

        .dash-link.logout:hover {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
        }

        .card-custom {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .table-custom {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.8);
            min-width: 800px;
        }
        
        .table-custom th {
            background: rgba(0, 148, 68, 0.1);
            border-bottom: 1px solid var(--glass-border);
            color: var(--primary);
            font-weight: 700;
            padding: 20px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            border-top: none;
        }
        
        .table-custom td {
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: middle;
            background: transparent !important;
            transition: background 0.3s;
        }
        
        .table-custom tr {
            transition: transform 0.2s, background 0.2s;
        }

        .table-custom tbody tr:hover td {
            background: rgba(255, 255, 255, 0.02) !important;
        }

        .order-row {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .order-row.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background: rgba(0, 148, 68, 0.15);
            color: #009444;
            border: 1px solid rgba(0, 148, 68, 0.3);
            box-shadow: 0 0 10px rgba(0, 148, 68, 0.1);
        }
        
        .status-inactive {
            background: rgba(255, 77, 77, 0.15);
            color: #ff4d4d;
            border: 1px solid rgba(255, 77, 77, 0.3);
            box-shadow: 0 0 10px rgba(255, 77, 77, 0.1);
        }

        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 320px;
            border-left: 4px solid var(--primary);
            background: #1a1a1a;
            color: white;
        }

        .btn-cancel {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            transform: scale(1.1);
            background-color: #dc3545;
            color: white !important;
            border-color: #dc3545;
        }
        
        .btn-cancel:hover i {
            color: white !important;
        }

        @media (max-width: 991px) {
            .dash-sidebar { margin-bottom: 20px; }
            .table-responsive { border-radius: 20px; }
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

    <?php if(isset($_SESSION['order_msg'])): ?>
        <div class="alert alert-dismissible fade show alert-floating shadow-lg" role="alert" id="autoDismissAlert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                <div>
                    <strong>Success!</strong><br>
                    <small><?php echo $_SESSION['order_msg']; ?></small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['order_msg']); ?>
    <?php endif; ?>

    <div class="container dashboard-container">
        <div class="row">
            
            <div class="col-lg-3 mb-4">
                <div class="dash-sidebar h-100">
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
                        <a href="my-orders.php" class="dash-link active"><i class="bi bi-box-seam"></i> My Orders</a>
                        <a href="wishlist.php" class="dash-link"><i class="bi bi-heart"></i> Wishlist</a>
                        <a href="user_profile.php" class="dash-link"><i class="bi bi-person-circle"></i> My Profile</a>
                        
                        <form method="post" class="w-100 mt-auto">
                            <button type="submit" name="logout" class="dash-link logout w-100 border-0 text-start bg-transparent">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h2 class="text-white mb-0">Order History</h2>
                        <p class="text-white-50 mb-0 small">Manage and track your recent purchases</p>
                    </div>
                    <div class="badge bg-dark border border-secondary text-white-50 px-3 py-2 rounded-pill">
                        Total Orders: <?php echo mysqli_num_rows($result); ?>
                    </div>
                </div>

                <div class="card-custom">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product Details</th>
                                    <th>Shipping Info</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="order-row">
                                    <td>
                                        <span class="text-white fw-bold font-monospace">#<?php echo $row['order_id']; ?></span>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded bg-dark p-2 me-3 border border-secondary">
                                                <i class="bi bi-box-seam text-success"></i>
                                            </div>
                                            <span class="text-white fw-medium"><?php echo $row['Product']; ?></span>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="small text-white-50" style="line-height: 1.4;">
                                            <?php if(!empty($row['shipping_address'])): ?>
                                                <span class="text-white d-block mb-1"><?php echo htmlspecialchars($row['shipping_name']); ?></span>
                                                <?php echo htmlspecialchars($row['shipping_city']); ?>, <?php echo htmlspecialchars($row['shipping_state']); ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Not provided</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-white-50 small"><?php echo $row['Date']; ?></td>
                                    
                                    <td class="text-success fw-bold font-monospace fs-5">$<?php echo number_format($row['amount'], 2); ?></td>
                                    
                                    <td>
                                        <?php if($row['status'] == 1){?>
                                            <span class="badge-status status-active">
                                                Active
                                            </span>
                                        <?php }else{ ?>
                                            <span class="badge-status status-inactive">Cancelled</span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <a href="order_cancel.php?dd=<?=$row['order_id']; ?>" 
                                           class="btn btn-outline-danger btn-sm btn-cancel mx-auto" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-title="Cancel Order"
                                           onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center p-5">
                            <div class="mb-3">
                                <i class="bi bi-cart-x text-white-50" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                            <h4 class="text-white">No Orders Found</h4>
                            <p class="text-white-50">It looks like you haven't placed any orders yet.</p>
                            <a href="category.php" class="btn btn-outline-success rounded-pill px-4 mt-2">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const $alert = $('#autoDismissAlert');
            if($alert.length) {
                setTimeout(function() {
                    $alert.slideUp(400, function() {
                        $(this).remove();
                    });
                }, 3500);
            }

            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            const $rows = $('.order-row');
            if ($rows.length > 0) {
                $rows.each(function(index) {
                    const $row = $(this);
                    setTimeout(() => {
                        $row.addClass('visible');
                    }, 100 * index); 
                });
            }

            $('.dash-link').not('.active').hover(
                function() {
                    $(this).css('transform', 'translateX(5px)');
                },
                function() {
                    $(this).css('transform', 'translateX(0)');
                }
            );

            window.addEventListener('scroll', () => {
                const nav = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            });

            const toggler = document.querySelector('.custom-toggler');
            if(toggler) toggler.addEventListener('click', () => toggler.classList.toggle('open'));
        });
    </script>
</body>
</html>