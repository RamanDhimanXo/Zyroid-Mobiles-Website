<?php
session_start();
include('../db_config.php');

$pro = "SELECT * FROM tb_products WHERE en_des_pro = 1 ORDER BY id DESC";
$res2 = mysqli_query($con, $pro);

$cat_qr = "SELECT * FROM tb_categories";
$cat_res = mysqli_query($con, $cat_qr);

class user_profile
{
    public $name;
    public $email;
    function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
}

$obj = null;

if (isset($_POST['logout'])) {
    if (isset($_SESSION['email'])) {
        mysqli_query($con, "UPDATE tb_users SET token=NULL WHERE email='" . $_SESSION['email'] . "'");
    }
    session_destroy();
    header("location: login.php");
    exit();
}

if (isset($_SESSION['user']) && isset($_SESSION['email'])) {
    $user = mysqli_real_escape_string($con, $_SESSION['user']);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $sel = "SELECT * FROM tb_users WHERE `email`= '$email'";
    $res = mysqli_query($con, $sel);
    $row = mysqli_fetch_assoc($res);
    if ($row) {
        if (!isset($_SESSION['token']) || $row['token'] != $_SESSION['token']) {
            session_destroy();
            header("location: login.php");
            exit();
        }
        $obj = new user_profile($user, $email);
    }
}

$cart_count = 0;
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $c_res = mysqli_query($con, "SELECT * FROM tb_cart WHERE email='$user_email'");
    $cart_count = mysqli_num_rows($c_res);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Mobiles - Zyroid</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary: #009444;
            --bg-dark: #1a1a1a;
            --text-main: #ffffff;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s ease-in-out;
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
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6,
        .navbar-brand, .btn-theme, .nav-link, .dropdown-item, .price-tag {
            font-family: var(--font-head);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .navbar-custom {
            padding: 15px 0;
            background: transparent;
            position: absolute;
            width: 100%;
            z-index: 1000;
            transition: 0.4s;
        }

        .navbar-custom.scrolled {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            padding: 10px 0;
            border-bottom: 1px solid var(--glass-border);
            position: fixed;
            top: 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 600;
            position: relative;
        }

        .nav-link:hover, .nav-link.active { color: #fff !important; }

        .nav-link::after {
            content: ''; position: absolute; bottom: 5px; left: 0;
            width: 0%; height: 2px; background: var(--primary);
            transition: width 0.3s ease-in-out;
        }

        .nav-link:hover::after, .nav-link.active::after { width: 100%; }

        .dropdown-menu {
            background: rgba(10, 10, 10, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.15);
            margin-top: 10px;
        }

        .dropdown-item { color: rgba(255, 255, 255, 0.8); padding: 10px 20px; transition: 0.2s; }
        .dropdown-item:hover { background: var(--primary); color: white; padding-left: 25px; }

        .custom-toggler { border: none; background: transparent; padding: 5px; display: flex; flex-direction: column; gap: 6px; width: 40px; cursor: pointer; }
        .custom-toggler span { display: block; width: 100%; height: 3px; background: white; border-radius: 5px; transition: 0.4s; }
        .custom-toggler.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); background: var(--primary); }
        .custom-toggler.open span:nth-child(2) { opacity: 0; }
        .custom-toggler.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); background: var(--primary); }

        @media (max-width: 991px) {
            .navbar-collapse {
                background: rgba(26, 26, 26, 0.98);
                backdrop-filter: blur(15px);
                padding: 20px;
                border-radius: 15px;
                margin-top: 15px;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        .btn-theme {
            background: var(--primary); color: #fff; border: none;
            padding: 10px 30px; border-radius: 50px; font-weight: 700;
            text-decoration: none; display: inline-block; transition: all 0.3s ease;
        }

        .page-header {
            padding-top: 150px;
            padding-bottom: 40px;
            position: relative;
        }
        
        .toolbar-wrapper {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 15px;
            backdrop-filter: blur(10px);
            margin-top: 30px;
        }

        .filter-scroll {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 5px;
            scrollbar-width: none;
        }
        .filter-scroll::-webkit-scrollbar { display: none; }

        .filter-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            padding: 8px 24px;
            border-radius: 50px;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 148, 68, 0.3);
        }

        .search-input-group {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 5px 15px;
            transition: 0.3s;
        }
        
        .search-input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 148, 68, 0.2);
        }

        .search-input {
            background: transparent;
            border: none;
            color: white;
            padding: 5px 10px;
            outline: none;
            width: 100%;
        }

        .product-card-v2 {
            display: block;
            text-decoration: none;
            background: linear-gradient(145deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            opacity: 0; 
            transform: translateY(20px);
        }

        .product-card-v2.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .product-card-v2:hover {
            border-color: rgba(0, 148, 68, 0.5);
            background: linear-gradient(145deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
            transform: translateY(-8px);
        }

        .img-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            background: var(--primary);
            opacity: 0;
            filter: blur(60px);
            border-radius: 50%;
            transition: 0.5s;
            z-index: 0;
        }

        .product-card-v2:hover .img-glow {
            opacity: 0.15;
        }

        .card-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
        }

        .badge-pill {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ccc;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .img-wrapper {
            height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            position: relative;
            z-index: 2;
            transition: transform 0.4s ease;
        }

        .product-card-v2:hover .img-wrapper {
            transform: scale(1.08);
        }

        .img-wrapper img {
            max-height: 100%;
            max-width: 100%;
            filter: drop-shadow(0 15px 15px rgba(0,0,0,0.5));
            object-fit: contain;
        }

        .card-info {
            position: relative;
            z-index: 2;
        }

        .card-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .price-text {
            color: var(--primary);
            font-size: 1.4rem;
            font-weight: 700;
            font-family: var(--font-head);
        }

        .btn-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }

        .product-card-v2:hover .btn-arrow {
            background: var(--primary);
            border-color: var(--primary);
            transform: rotate(-45deg);
        }

        .floating-cart {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px;
            background: var(--primary); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff; box-shadow: 0 10px 30px rgba(0, 148, 68, 0.4);
            z-index: 1000; transition: transform 0.3s; text-decoration: none;
        }
        .floating-cart:hover { transform: scale(1.1); color: white; }
        .cart-count {
            position: absolute; top: 0; right: 0; background: red; font-size: 12px;
            width: 20px; height: 20px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-family: var(--font-body); font-weight: bold;
        }

        footer { border-top: 1px solid var(--glass-border); padding-top: 60px; margin-top: auto; background: rgba(0, 0, 0, 0.3); }
        .footer-link { color: rgba(255, 255, 255, 0.5); text-decoration: none; display: block; margin-bottom: 10px; transition: 0.2s; font-family: var(--font-body); }
        .footer-link:hover { color: var(--primary); padding-left: 5px; }

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
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">Mobiles</a>
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
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li>
                                    <form method="post">
                                        <button type="submit" name="logout" class="dropdown-item text-danger">Logout</button>
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

    <section class="container page-header">
        <div class="row align-items-end">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold text-white mb-2" id="pageTitle" style="opacity: 0; transform: translateY(-20px); transition: 0.5s;">
                    All Smartphones
                </h1>
                <p class="text-white-50 m-0" id="pageSubtitle" style="opacity: 0; transition: 0.5s; transition-delay: 0.1s;">
                    Discover the latest technology designed for the future.
                </p>
            </div>
        </div>

        <div class="toolbar-wrapper">
            <div class="row g-3 align-items-center">
                <div class="col-md-4 order-md-2">
                    <div class="search-input-group d-flex align-items-center">
                        <i class="bi bi-search text-white-50"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search devices...">
                    </div>
                </div>
                <div class="col-md-8 order-md-1">
                    <div class="filter-scroll" id="filterContainer">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <?php
                        mysqli_data_seek($cat_res, 0);
                        while ($row_cat = mysqli_fetch_assoc($cat_res)) {
                            $catName = htmlspecialchars($row_cat['cat_name']);
                            echo '<button class="filter-btn" data-filter="' . strtolower($catName) . '">' . $catName . '</button>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="row g-4" id="productGrid">
            <?php
            if (mysqli_num_rows($res2) > 0) {
                while ($data = mysqli_fetch_assoc($res2)) {
                    $brandClass = strtolower($data['category']);
                    $img_arr = explode(',', $data['product_image']);
                    $show_img = trim($img_arr[0]);
                    ?>
                    <div class="col-md-6 col-lg-3 product-item" data-brand="<?php echo $brandClass; ?>">
                        <a href="product.php?id=<?= $data['id'] ?>" class="product-card-v2">
                            <div class="img-glow"></div>
                            
                            <div class="card-header-top">
                                <span class="badge-pill"><?= $data['category'] ?></span>
                            </div>

                            <div class="img-wrapper">
                                <img src="../admin/product_images/<?= $show_img ?>" alt="<?= $data['product_name'] ?>">
                            </div>

                            <div class="card-info">
                                <h5 class="card-title"><?= $data['product_name'] ?></h5>
                                <div class="card-bottom">
                                    <div class="price-text">$<?= $data['product_price'] ?></div>
                                    <div class="btn-arrow">
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12 text-center text-white-50'>No products found.</div>";
            }
            ?>
        </div>
        <div id="noResults" class="text-center text-white-50 py-5" style="display: none;">
            <i class="bi bi-emoji-frown fs-1 d-block mb-3 opacity-50"></i>
            <h4>No products found matching your criteria.</h4>
        </div>
    </section>

    <footer>
        <div class="container pb-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white fw-bold fs-3 mb-3 d-block" href="../index.php">Zyroid<span
                            style="color: var(--primary)">.</span></a>
                    <p class="text-white-50 small">Your trusted partner for latest smartphones.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold text-white mb-3">Mobiles</h6>
                    <a href="category.php?cat=iphones" class="footer-link">iPhones</a>
                    <a href="category.php?cat=android" class="footer-link">Android</a>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold text-white mb-3">Help</h6>
                    <a href="my-orders.php" class="footer-link">Track Order</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold text-white mb-3">Offers</h6>
                    <div class="input-group">
                        <input type="email" class="form-control bg-transparent border-secondary text-white"
                            placeholder="Email">
                        <button class="btn btn-success">Join</button>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary border-opacity-10 text-white-50 small">&copy;
                2025 Zyroid Mobiles. All rights reserved.</div>
        </div>
    </footer>

    <a href="cart.php" class="floating-cart">
        <i class="bi bi-bag"></i>
        <span class="cart-count" id="cartBadge"><?php echo $cart_count; ?></span>
    </a>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            window.addEventListener('scroll', () => {
                const nav = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            });

            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) {
                    $('.navbar-custom').addClass('scrolled');
                } else {
                    $('.navbar-custom').removeClass('scrolled');
                }
            });

            $('.custom-toggler').click(function () {
                $(this).toggleClass('open');
            });

            $('#pageTitle, #pageSubtitle').css('opacity', '1').css('transform', 'translateY(0)');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            $('.product-card-v2').each(function () {
                observer.observe(this);
            });

            let currentCategory = 'all';
            let searchTerm = '';

            function applyFilters() {
                let visibleCount = 0;
                $('.product-item').each(function () {
                    const $item = $(this);
                    const itemBrand = $item.data('brand');
                    const itemName = $item.find('.card-title').text().toLowerCase();

                    const matchesCategory = (currentCategory === 'all' || itemBrand === currentCategory);
                    const matchesSearch = itemName.includes(searchTerm);

                    if (matchesCategory && matchesSearch) {
                        $item.stop().fadeIn(300).find('.product-card-v2').addClass('visible');
                        visibleCount++;
                    } else {
                        $item.stop().fadeOut(300);
                    }
                });

                if (visibleCount === 0) {
                    $('#noResults').fadeIn();
                } else {
                    $('#noResults').hide();
                }
            }

            function filterItems(category) {
                currentCategory = category;
                const url = new URL(window.location);
                url.searchParams.set('cat', category);
                window.history.pushState({}, '', url);

                let displayCat = decodeURIComponent(category).toUpperCase();
                if (category === 'all') $('#pageTitle').text("All Smartphones");
                else $('#pageTitle').text(displayCat + " Devices");

                $('.filter-btn').removeClass('active');
                $(`.filter-btn[data-filter="${category}"]`).addClass('active');

                applyFilters();
            }

            $('#searchInput').on('input', function() {
                searchTerm = $(this).val().toLowerCase();
                applyFilters();
            });

            $('.filter-btn').click(function () {
                const filterValue = $(this).data('filter');
                filterItems(filterValue);
            });

            if (catParam) {
                filterItems(catParam.toLowerCase());
            } else {
                setTimeout(() => {
                    $('.product-card-v2').addClass('visible');
                }, 100);
            }
        });
    </script>
</body>
</html>