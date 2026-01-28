<?php
session_start();
include( 'db_config.php' );

class user_profile
 {
    public $name;
    public $email;

    function __construct( $name, $email )
 {
        $this->name = $name;
        $this->email = $email;
    }
}

$obj = null;

if ( isset( $_SESSION[ 'user' ] ) && isset( $_SESSION[ 'email' ] ) ) {
    $user = $_SESSION[ 'user' ];
    $email = $_SESSION[ 'email' ];

    $stmt = $con->prepare( 'SELECT * FROM tb_users WHERE email = ?' );
    $stmt->bind_param( 's', $email );
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ( $row ) {
        if ( !isset( $_SESSION[ 'token' ] ) || $row[ 'token' ] != $_SESSION[ 'token' ] ) {
            session_destroy();
            header( 'location: user/login.php' );
            exit();
        }
        $obj = new user_profile( $user, $email );
    }

    if ( isset( $_POST[ 'logout' ] ) ) {
        $email = $_SESSION[ 'email' ];
        $stmt = $con->prepare( 'UPDATE tb_users SET token=NULL WHERE email=?' );
        $stmt->bind_param( 's', $email );
        $stmt->execute();
        session_destroy();
        header( 'location: user/login.php' );
        exit();
    }
}

if ( isset( $_POST[ 'add_to_cart_home' ] ) ) {
    if ( isset( $_SESSION[ 'email' ] ) ) {
        $user_email = $_SESSION[ 'email' ];
        $p_id = $_POST[ 'product_id' ];

        $stmt_p = $con->prepare( 'SELECT * FROM tb_products WHERE id=?' );
        $stmt_p->bind_param( 'i', $p_id );
        $stmt_p->execute();
        $res_p = $stmt_p->get_result();

        if ( $res_p->num_rows > 0 ) {
            $p_data = $res_p->fetch_assoc();
            $p_name = $p_data[ 'product_name' ];
            $p_price = $p_data[ 'product_price' ];

            $img_arr = explode( ',', $p_data[ 'product_image' ] );
            $p_img = trim( $img_arr[ 0 ] );

            $stmt_c = $con->prepare( 'SELECT * FROM tb_cart WHERE email=? AND product_id=?' );
            $stmt_c->bind_param( 'si', $user_email, $p_id );
            $stmt_c->execute();
            $check_res = $stmt_c->get_result();

            if ( $check_res->num_rows > 0 ) {
                $c_row = $check_res->fetch_assoc();
                $n_qty = $c_row[ 'quantity' ] + 1;
                $stmt_u = $con->prepare( 'UPDATE tb_cart SET quantity=? WHERE id=?' );
                $stmt_u->bind_param( 'ii', $n_qty, $c_row[ 'id' ] );
                $stmt_u->execute();
            } else {
                $stmt_i = $con->prepare( 'INSERT INTO tb_cart (email, product_id, product_name, product_price, product_image, quantity) VALUES (?, ?, ?, ?, ?, 1)' );
                $stmt_i->bind_param( 'sisss', $user_email, $p_id, $p_name, $p_price, $p_img );
                $stmt_i->execute();
            }
            $_SESSION[ 'cart_success' ] = 'Product added to cart!';
            header( 'Location: index.php' );
            exit();
        }
    } else {
        echo "<script>alert('Please login to add items'); window.location='user/login.php';</script>";
        exit();
    }
}

$cart_count = 0;
if ( isset( $_SESSION[ 'email' ] ) ) {
    $user_email = $_SESSION[ 'email' ];
    $c_res = mysqli_query( $con, "SELECT * FROM tb_cart WHERE email='$user_email'" );
    $cart_count = mysqli_num_rows( $c_res );
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Zyroid - The Mobile Expert</title>

    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
    <link
        href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700;900&display=swap'
        rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css' />

    <style>
    :root {
        --primary: #009444;
        --bg-dark: #1a1a1a;
        --text-main: #ffffff;
        --glass: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.08);
        --font-head: 'Rajdhani', sans-serif;
        --font-body: 'Poppins', sans-serif;
    }

    body {
        background: var(--bg-dark);
        background-image: radial-gradient(circle at 50% 0%, #1e3c2e 0%, #1a1a1a 40%);
        font-family: var(--font-body);
        color: var(--text-main);
        overflow-x: hidden;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    .navbar-brand,
    .nav-link,
    .dropdown-item {
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

    .nav-link:hover,
    .nav-link.active {
        color: #fff !important;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 5px;
        left: 0;
        width: 0%;
        height: 2px;
        background: var(--primary);
        transition: 0.3s;
    }

    .nav-link:hover::after,
    .nav-link.active::after {
        width: 100%;
    }

    .dropdown-menu {
        background: rgba(10, 10, 10, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.15);
        margin-top: 10px;
    }

    .dropdown-item {
        color: rgba(255, 255, 255, 0.8);
        padding: 10px 20px;
        transition: 0.2s;
    }

    .dropdown-item:hover {
        background: var(--primary);
        color: white;
        padding-left: 25px;
    }

    .custom-toggler {
        border: none;
        background: transparent;
        padding: 5px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 40px;
        cursor: pointer;
    }

    .custom-toggler span {
        display: block;
        width: 100%;
        height: 3px;
        background: white;
        border-radius: 5px;
        transition: 0.4s;
    }

    .custom-toggler.open span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
        background: var(--primary);
    }

    .custom-toggler.open span:nth-child(2) {
        opacity: 0;
    }

    .custom-toggler.open span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
        background: var(--primary);
    }

    @media (max-width: 991px) {
        .navbar-collapse {
            background: rgba(26, 26, 26, 0.98);
            padding: 20px;
            border-radius: 15px;
            margin-top: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    }

    .btn-theme {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 10px 30px;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
        transition: 0.3s;
    }

    .btn-theme:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 148, 68, 0.4);
        color: white;
    }

    .hero-v2 {
        position: relative;
        min-height: 100vh;
        width: 100%;
        overflow: hidden;
        background: radial-gradient(circle at 70% 50%, #1a2520 0%, #000 70%);
        display: flex;
        align-items: center;
    }

    .hero-v2-grid {
        position: absolute;
        width: 100%;
        height: 100%;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 50px 50px;
        opacity: 0.5;
        z-index: 1;
    }

    .hero-v2-item {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.8s ease-in-out, visibility 0.8s;
        z-index: 2;
        padding-top: 80px;
        display: flex;
        align-items: center;
    }

    .hero-v2-item.active {
        opacity: 1;
        visibility: visible;
        z-index: 5;
    }

    .hero-text-card {
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 20px;
        transform: translateX(-50px);
        opacity: 0;
        transition: 0.8s ease-out;
        position: relative;
        overflow: hidden;
    }

    .hero-v2-item.active .hero-text-card {
        transform: translateX(0);
        opacity: 1;
    }

    .hero-text-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary);
    }

    .badge-tech {
        font-size: 0.8rem;
        letter-spacing: 2px;
        color: var(--primary);
        margin-bottom: 10px;
        display: block;
        font-weight: 700;
    }

    .hero-title-v2 {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 15px;
        text-transform: uppercase;
        color: #fff;
    }

    .hero-desc-v2 {
        color: #aaa;
        font-family: var(--font-body);
        font-size: 1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .specs-row {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 20px;
    }

    .spec-item {
        text-align: center;
        color: #fff;
    }

    .spec-item i {
        color: var(--primary);
        font-size: 1.2rem;
        margin-bottom: 5px;
        display: block;
    }

    .spec-item span {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
    }

    .hero-img-container {
        position: relative;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        perspective: 1000px;
    }

    .spotlight-glow {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, var(--primary) 0%, transparent 60%);
        opacity: 0.2;
        filter: blur(50px);
        z-index: -1;
        transition: 1s;
    }

    .hero-img-v2 {
        max-height: 480px;
        max-width: 100%;
        z-index: 10;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.5));
        transform: translateY(50px) scale(0.9);
        opacity: 0;
        transition: 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .hero-v2-item.active .hero-img-v2 {
        transform: translateY(0) scale(1);
        opacity: 1;
        animation: floatV2 6s ease-in-out infinite;
    }

    @keyframes floatV2 {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .hero-controls {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 20;
        display: flex;
        gap: 20px;
    }

    .control-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
        backdrop-filter: blur(5px);
    }

    .control-btn:hover {
        background: var(--primary);
        border-color: var(--primary);
    }

    @media(max-width: 991px) {
        .hero-v2 {
            height: auto;
            padding: 100px 0 50px 0;
        }

        .hero-title-v2 {
            font-size: 2.5rem;
        }

        .hero-img-container {
            height: 300px;
            margin-top: 30px;
        }

        .hero-img-v2 {
            max-height: 280px;
        }

        .hero-text-card {
            padding: 25px;
        }
    }

    .reveal-up {
        opacity: 0;
        transform: translateY(50px);
        transition: 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .reveal-up.active {
        opacity: 1;
        transform: translateY(0);
    }

    .reveal-delay-1 {
        transition-delay: 0.1s;
    }

    .reveal-delay-2 {
        transition-delay: 0.2s;
    }

    .feature-box {
        background: var(--glass);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: 0.3s;
        height: 100%;
    }

    .feature-box:hover {
        border-color: var(--primary);
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.05);
    }

    .f-icon {
        width: 70px;
        height: 70px;
        background: rgba(0, 148, 68, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: var(--primary);
        font-size: 1.8rem;
        transition: 0.5s;
    }

    .feature-box:hover .f-icon {
        transform: rotateY(180deg);
        background: var(--primary);
        color: white;
    }

    .review-card {
        background: var(--glass);
        border-radius: 20px;
        padding: 30px;
        border: 1px solid var(--glass-border);
    }

    .user-pic {
        width: 50px;
        height: 50px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #000;
    }

    .pro-card {
        background: var(--glass);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 25px;
        transition: 0.3s;
        height: 100%;
        position: relative;
    }

    .pro-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary);
    }

    .floating-cart {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        z-index: 999;
        box-shadow: 0 0 20px rgba(0, 148, 68, 0.5);
        text-decoration: none;
    }

    .cart-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #1a1a1a;
    }

    .hot-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: #ff3b30;
        color: white;
        padding: 5px 15px;
        font-weight: bold;
        border-radius: 5px;
        z-index: 10;
        box-shadow: 0 0 15px rgba(255, 59, 48, 0.5);
        font-family: var(--font-head);
        letter-spacing: 1px;
    }

    .hot-slider-nav {
        color: var(--primary);
        background: rgba(255, 255, 255, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        backdrop-filter: blur(5px);
    }

    .hot-slider-nav::after {
        font-size: 18px;
        font-weight: bold;
    }

    .hot-slider-nav:hover {
        background: var(--primary);
        color: white;
    }

    .btn-view-pro {
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: rgba(255, 255, 255, 0.8);
        border-radius: 50px;
        padding: 6px 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-view-pro:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 0 15px rgba(0, 148, 68, 0.4);
        transform: translateY(-2px);
    }

    footer {
        border-top: 1px solid var(--glass-border);
        padding-top: 60px;
        margin-top: 100px;
        background: rgba(0, 0, 0, 0.3);
    }
    </style>
</head>

<body>

    <nav class='navbar navbar-expand-lg navbar-custom'>
        <div class='container'>
            <a class='navbar-brand text-white fw-bold fs-3' href='index.php'>
                Zyroid<span style='color: var(--primary)'>.</span>
            </a>

            <button class='navbar-toggler custom-toggler border-0' type='button' data-bs-toggle='collapse'
                data-bs-target='#nav'>
                <span></span><span></span><span></span>
            </button>

            <div class='collapse navbar-collapse' id='nav'>
                <ul class='navbar-nav ms-auto gap-3 align-items-center'>
                    <li class='nav-item'><a class='nav-link active' href='index.php'>Home</a></li>

                    <li class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' href='#' data-bs-toggle='dropdown'>Mobiles</a>
                        <ul class='dropdown-menu'>
                            <li><a class='dropdown-item' href='user/category.php?cat=all'>All Mobiles</a></li>
                            <?php
$nav_cat_qr = 'SELECT * FROM tb_categories';
$nav_cat_res = mysqli_query( $con, $nav_cat_qr );
if ( $nav_cat_res ) {
    while ( $nav_row = mysqli_fetch_assoc( $nav_cat_res ) ) {
        echo '<li><a class="dropdown-item" href="user/category.php?cat=' . urlencode( $nav_row[ 'cat_name' ] ) . '">' . $nav_row[ 'cat_name' ] . '</a></li>';
    }
}
?>
                            <li>
                        </ul>
                    </li>

                    <li class='nav-item'><a class='nav-link' href='user/cart.php'>Cart</a></li>

                    <?php if ( $obj != null ) {
    ?>
                    <li class='nav-item dropdown'>
                        <a href='#' class='nav-link d-flex align-items-center gap-2 dropdown-toggle'
                            data-bs-toggle='dropdown'>
                            <div class='bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold'
                                style='width: 35px; height: 35px;'>
                                <?php echo strtoupper( substr( $obj->name, 0, 1 ) );
    ?>
                            </div>
                            <span><?php echo $obj->name;
    ?></span>
                        </a>
                        <ul class='dropdown-menu dropdown-menu-end'>
                            <li><a class='dropdown-item' href='user/user_profile.php'>Profile</a></li>
                            <li>
                                <hr class='dropdown-divider bg-secondary'>
                            </li>
                            <li>
                                <form method='post'>
                                    <button type='submit' name='logout'
                                        class='dropdown-item text-danger'>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <?php } else {
        ?>
                    <li class='nav-item'>
                        <a href='user/login.php' class='btn-theme ms-2'>Sign In</a>
                    </li>
                    <?php }
        ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ( isset( $_SESSION[ 'cart_success' ] ) ): ?>
    <div class='alert alert-success position-fixed top-0 end-0 m-4 text-white border-0 d-flex align-items-center shadow-lg'
        style='z-index: 9999; background: var(--primary); border-radius: 10px;' id='autoAlert'>
        <i class='bi bi-check-circle-fill me-2'></i> <?php echo $_SESSION[ 'cart_success' ];
        ?>
        <button type='button' class='btn-close btn-close-white ms-3' onclick='this.parentElement.remove()'></button>
    </div>
    <?php unset( $_SESSION[ 'cart_success' ] );
        ?>
    <?php endif;
        ?>

    <section class='hero-v2' id='home'>
        <div class='hero-v2-grid'></div>

        <?php
        $hero_qr = 'SELECT * FROM tb_products WHERE en_des_pro = 1 ORDER BY id DESC LIMIT 3';
        $hero_res = mysqli_query( $con, $hero_qr );
        $i = 0;

        $buzzwords = [
            [ 'label' => 'FLAGSHIP KILLER', 'spec1' => '5G Ready', 'spec2' => 'AI Chip' ],
            [ 'label' => 'VISIONARY', 'spec1' => '120Hz', 'spec2' => 'OLED' ],
            [ 'label' => 'TITANIUM', 'spec1' => 'Pro Cam', 'spec2' => 'Fast Chg' ]
        ];

        if ( mysqli_num_rows( $hero_res ) > 0 ) {
            while ( $slide = mysqli_fetch_assoc( $hero_res ) ) {
                $activeClass = ( $i == 0 ) ? 'active' : '';
                $img_arr = explode( ',', $slide[ 'product_image' ] );
                $thumb = trim( $img_arr[ 0 ] );
                $desc = !empty( $slide[ 'product_des' ] ) ? substr( $slide[ 'product_des' ], 0, 100 ) . '...' : 'Experience the future of technology.';

                $currentBuzz = isset( $buzzwords[ $i ] ) ? $buzzwords[ $i ] : $buzzwords[ 0 ];
                ?>

        <div class="hero-v2-item <?php echo $activeClass; ?>" data-index="<?php echo $i; ?>">
            <div class='container position-relative' style='z-index: 5;'>
                <div class='row align-items-center'>
                    <div class='col-lg-6 order-2 order-lg-1'>
                        <div class='hero-text-card'>
                            <span class='badge-tech'><i class='bi bi-cpu'></i>
                                <?php echo $currentBuzz[ 'label' ];
                ?></span>
                            <h1 class='hero-title-v2'><?php echo $slide[ 'product_name' ];
                ?></h1>
                            <p class='hero-desc-v2'><?php echo $desc;
                ?></p>
                            <div class='d-flex gap-3'>
                                <a href="user/product.php?id=<?php echo $slide['id']; ?>" class='btn-theme px-4'>Check
                                    product</a>
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-6 order-1 order-lg-2'>
                        <div class='hero-img-container'>
                            <div class='spotlight-glow'></div>
                            <img src="admin/product_images/<?php echo $thumb; ?>"
                                alt="<?php echo $slide['product_name']; ?>" class='hero-img-v2'>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
                $i++;
            }
        }
        ?>
        <div class='hero-controls'>
            <button class='control-btn' onclick='prevSlide()'><i class='bi bi-arrow-left'></i></button>
            <button class='control-btn' onclick='nextSlide()'><i class='bi bi-arrow-right'></i></button>
        </div>
    </section>

    <section class='py-5'>
        <div class='container'>
            <div class='row g-4'>
                <div class='col-md-4 reveal-up'>
                    <div class='feature-box'>
                        <div class='f-icon'><i class='bi bi-truck'></i></div>
                        <h4 class='text-white'>Fast Delivery</h4>
                        <p class='text-white-50 small mb-0'>Get your device delivered within 24 hours in select cities.
                        </p>
                    </div>
                </div>
                <div class='col-md-4 reveal-up reveal-delay-1'>
                    <div class='feature-box'>
                        <div class='f-icon'><i class='bi bi-shield-check'></i></div>
                        <h4 class='text-white'>Official Warranty</h4>
                        <p class='text-white-50 small mb-0'>1 Year official brand warranty on all devices purchased.</p>
                    </div>
                </div>
                <div class='col-md-4 reveal-up reveal-delay-2'>
                    <div class='feature-box'>
                        <div class='f-icon'><i class='bi bi-arrow-repeat'></i></div>
                        <h4 class='text-white'>7 Days Return</h4>
                        <p class='text-white-50 small mb-0'>Easy returns if you find any manufacturing defect.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class='py-5 position-relative' style='background: rgba(0,0,0,0.2);'>
        <div class='container'>
            <div class='swiper hotDealsSwiper reveal-up'>
                <div class='swiper-wrapper'>
                    <?php
        $hot_qr = 'SELECT * FROM tb_products ORDER BY (product_price * 1) DESC LIMIT 5';
        $hot_res = mysqli_query( $con, $hot_qr );

        if ( mysqli_num_rows( $hot_res ) > 0 ) {
            while( $hot_data = mysqli_fetch_assoc( $hot_res ) ) {
                $h_img_arr = explode( ',', $hot_data[ 'product_image' ] );
                $h_img = trim( $h_img_arr[ 0 ] );
                ?>
                    <div class='swiper-slide'>
                        <div class='row align-items-center g-5'>
                            <div class='col-lg-6'>
                                <div class='position-relative'>
                                    <div class='hot-badge'>HOT DEAL</div>
                                    <img src="admin/product_images/<?php echo $h_img; ?>"
                                        class='img-fluid w-100 rounded-4 shadow-lg border border-secondary border-opacity-25'
                                        style='max-height: 400px; object-fit: contain; background: rgba(255,255,255,0.02);'>
                                </div>
                            </div>
                            <div class='col-lg-6'>
                                <h4 class='text-primary letter-spacing-2 fw-bold text-uppercase mb-2'>Limited Time Offer
                                </h4>
                                <h1 class='display-4 fw-bold text-white mb-3'><?php echo $hot_data[ 'product_name' ];
                ?></h1>
                                <p class='text-white-50 lead mb-4'><?php echo substr( $hot_data[ 'product_des' ], 0, 150 );
                ?>...</p>

                                <div class='d-flex align-items-center gap-4 mb-4'>
                                    <div>
                                        <small class='text-white-50 d-block text-uppercase'
                                            style='font-size: 0.8rem;'>Price Drop</small>
                                        <span class='fs-2 fw-bold text-white'>$<?php echo $hot_data[ 'product_price' ];
                ?></span>
                                    </div>
                                    <div class='vr bg-secondary opacity-50'></div>
                                    <div>
                                    </div>
                                </div>

                                <form method='POST'>
                                    <input type='hidden' name='product_id' value="<?php echo $hot_data['id']; ?>">
                                    <button type='submit' name='add_to_cart_home'
                                        class='btn-theme w-100 py-3 text-uppercase letter-spacing-2'>
                                        Grab This Deal <i class='bi bi-arrow-right ms-2'></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
            }
        }

        ?>
                </div>
                <div class='swiper-button-next hot-slider-nav'></div>
                <div class='swiper-button-prev hot-slider-nav'></div>
            </div>
        </div>
    </section>

    <section class='py-5'>
        <div class='container'>
            <div class='d-flex justify-content-between align-items-end mb-4 reveal-up'>
                <h2 class='text-white mb-0'>Premium Collection</h2>
                <div class='text-white-50 d-none d-md-block'>Swipe <i class='bi bi-arrow-right'></i></div>
            </div>

            <div class='swiper mySwiper reveal-up reveal-delay-1'>
                <div class='swiper-wrapper'>
                    <?php
        $qr4 = 'SELECT * FROM tb_products WHERE en_des_pro = 1 ORDER BY (product_price * 1) DESC LIMIT 6';
        $res4 = mysqli_query( $con, $qr4 );
        while ( $data4 = mysqli_fetch_assoc( $res4 ) ) {
            $img_arr4 = explode( ',', $data4[ 'product_image' ] );
            $thumb4 = trim( $img_arr4[ 0 ] );
            ?>
                    <div class='swiper-slide'>
                        <div class='pro-card w-100 text-center d-flex flex-column justify-content-center'>
                            <div class='mb-3'
                                style='height: 200px; display: flex; align-items: center; justify-content: center;'>
                                <img src="admin/product_images/<?= $thumb4; ?>" class='img-fluid'
                                    style='max-height: 100%;'>
                            </div>
                            <h5 class='fw-bold text-white text-truncate'><?= $data4[ 'product_name' ];
            ?></h5>
                            <div class='d-flex justify-content-center gap-3 align-items-center mt-2'>
                                <span class='text-success fw-bold'>$<?= $data4[ 'product_price' ];
            ?></span>
                                <a href="user/product.php?id=<?= $data4['id']; ?>" class='btn-view-pro'>View</a>
                            </div>
                        </div>
                    </div>
                    <?php }
            ?>
                </div>
            </div>
        </div>
    </section>

    <section class='py-5'>
        <div class='container'>
            <h2 class='text-center text-white mb-5 reveal-up'>What Our Customers Say</h2>
            <div class='row g-4'>
                <?php
            $rev_q = 'SELECT * FROM tb_comments ORDER BY id DESC LIMIT 3';
            $rev_res = mysqli_query( $con, $rev_q );
            $r_delay = 0;
            if ( mysqli_num_rows( $rev_res ) > 0 ) {
                while ( $row_rev = mysqli_fetch_assoc( $rev_res ) ) {
                    $initials = strtoupper( substr( $row_rev[ 'user_name' ], 0, 2 ) );
                    $delay_cls = ( $r_delay > 0 ) ? 'reveal-delay-' . $r_delay : '';
                    ?>
                <div class="col-md-4 reveal-up <?php echo $delay_cls; ?>">
                    <div class='review-card h-100'>
                        <div class='d-flex align-items-center mb-3'>
                            <div class='user-pic me-3'><?php echo $initials;
                    ?></div>
                            <div>
                                <h6 class='text-white mb-0'><?php echo htmlspecialchars( $row_rev[ 'user_name' ] );
                    ?></h6>
                                <small class='text-success'><i class='bi bi-star-fill'></i> <i
                                        class='bi bi-star-fill'></i> <i class='bi bi-star-fill'></i> <i
                                        class='bi bi-star-fill'></i> <i class='bi bi-star-fill'></i></small>
                            </div>
                        </div>
                        <p class='text-white-50 small'>"<?php echo htmlspecialchars($row_rev['comment']); ?>"</p>
                    </div>
                </div>
                <?php
                    $r_delay++;
                }
            } else {
                echo "<div class='col-12 text-center text-white-50'>No reviews yet.</div>";
            }
            ?>
            </div>
        </div>
    </section>

    <footer class='mt-5 pt-5 pb-3 border-top border-secondary border-opacity-25'>
        <div class='container'>
            <div class='row g-4'>
                <div class='col-lg-4'>
                    <a class='navbar-brand text-white fw-bold fs-3' href='#'>Zyroid<span
                            class='text-success'>.</span></a>
                    <p class='text-white-50 mt-2 small'>Your trusted partner for latest premium smartphones.</p>
                </div>
                <div class='col-6 col-lg-2'>
                    <h6 class='fw-bold text-white mb-3'>Shop</h6>
                    <ul class='list-unstyled'>
                        <li><a href='user/category.php?cat=iphones'
                                class='text-white-50 text-decoration-none'>iPhones</a></li>
                        <li><a href='user/category.php?cat=android'
                                class='text-white-50 text-decoration-none'>Android</a></li>
                    </ul>
                </div>
                <div class='col-6 col-lg-2'>
                    <h6 class='fw-bold text-white mb-3'>Support</h6>
                    <ul class='list-unstyled'>
                        <li><a href='user/my-orders.php' class='text-white-50 text-decoration-none'>Track Order</a></li>
                        <li><a href='#' class='text-white-50 text-decoration-none'>Contact Us</a></li>
                    </ul>
                </div>
                <div class='col-lg-4'>
                    <h6 class='fw-bold text-white mb-3'>Newsletter</h6>
                    <div class='input-group'>
                        <input type='text' class='form-control bg-transparent text-white border-secondary'
                            placeholder='Your Email'>
                        <button class='btn btn-success'>Join</button>
                    </div>
                </div>
            </div>
            <div class='text-center mt-5 text-white-50 small'>&copy;
                2025 Zyroid Mobiles. All rights reserved.</div>
        </div>
    </footer>

    <a href='user/cart.php' class='floating-cart'>
        <i class='bi bi-bag'></i>
        <span class='cart-badge'><?php echo $cart_count;
            ?></span>
    </a>

    <script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js'></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        const toggler = document.querySelector('.custom-toggler');
        if (toggler) {
            toggler.addEventListener('click', () => toggler.classList.toggle('open'));
        }

        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-v2-item');
        const totalSlides = slides.length;
        let autoPlayTimer;

        window.switchSlide = function(index) {
            clearInterval(autoPlayTimer);
            startAutoPlay();

            if (slides[currentSlide]) slides[currentSlide].classList.remove('active');

            currentSlide = index;
            if (currentSlide >= totalSlides) currentSlide = 0;
            if (currentSlide < 0) currentSlide = totalSlides - 1;

            if (slides[currentSlide]) slides[currentSlide].classList.add('active');
        }

        window.nextSlide = function() {
            switchSlide(currentSlide + 1);
        }

        window.prevSlide = function() {
            switchSlide(currentSlide - 1);
        }

        function startAutoPlay() {
            autoPlayTimer = setInterval(() => {
                switchSlide(currentSlide + 1);
            }, 5000);
        }

        if (totalSlides > 0) startAutoPlay();

        new Swiper('.hotDealsSwiper', {
            slidesPerView: 1,
            spaceBetween: 50,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false
            },
            breakpoints: {
                640: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                }
            }
        });

        setTimeout(() => {
            const alert = document.getElementById('autoAlert');
            if (alert) alert.remove();
        }, 3000);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));

        document.querySelectorAll('.deal-timer').forEach(timer => {
            let duration = parseInt(timer.getAttribute('data-seconds'));
            const updateTimer = () => {
                const h = Math.floor(duration / 3600);
                const m = Math.floor((duration % 3600) / 60);
                const s = duration % 60;
                timer.textContent =
                    `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                if (duration > 0) duration--;
                else timer.textContent = "EXPIRED";
            };
            updateTimer();
            setInterval(updateTimer, 1000);
        });
    });
    </script>
</body>

</html>