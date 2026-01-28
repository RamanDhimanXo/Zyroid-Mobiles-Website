<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $con->prepare("SELECT * FROM tb_products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        $_SESSION['error'] = "Product not found!";
        header("Location: latest_products.php");
        exit();
    }
} else {
    header("Location: latest_products.php");
    exit();
}

$stmt_cat = $con->prepare("SELECT * FROM tb_categories");
$stmt_cat->execute();
$cat_list_res = $stmt_cat->get_result();

$stmt_old = $con->prepare("SELECT en_des_pro FROM tb_products WHERE id = ?");
$stmt_old->bind_param("i", $id);
$stmt_old->execute();
$old_status = $stmt_old->get_result()->fetch_assoc()['en_des_pro'];

if (isset($_POST['update'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['product_price'];
    $stocks = $_POST['stocks'];
    $desc = $_POST['product_des'];
    $color = $_POST['product_color'];
    $storage = $_POST['device_storage'];
    $status = $_POST['en_des_pro'];

    if (isset($_FILES['image']['name']) && !empty(array_filter($_FILES['image']['name']))) {
        $uploaded_images = [];
        $target = "product_images/";
        foreach ($_FILES['image']['name'] as $i => $filename) {
            $tmpname = $_FILES['image']['tmp_name'][$i];
            if ($filename != "") {
                $target_file = $target . basename($filename);
                if (move_uploaded_file($tmpname, $target_file)) {
                    $uploaded_images[] = $filename;
                }
            }
        }
        $image = implode(',', $uploaded_images);
        $stmt_u = $con->prepare("UPDATE tb_products SET product_name=?, category=?, product_price=?, stocks=?, product_des=?, product_color=?, device_storage=?, en_des_pro=?, product_image=? WHERE id=?");
        $stmt_u->bind_param("ssdisssisi", $product_name, $category, $price, $stocks, $desc, $color, $storage, $status, $image, $id);
    } else {
        $stmt_u = $con->prepare("UPDATE tb_products SET product_name=?, category=?, product_price=?, stocks=?, product_des=?, product_color=?, device_storage=?, en_des_pro=? WHERE id=?");
        $stmt_u->bind_param("ssdisssii", $product_name, $category, $price, $stocks, $desc, $color, $storage, $status, $id);
    }

    if ($stmt_u->execute()) {
        $notif_date = date('Y-m-d');
        if ($old_status != $status) {
            $status_text = ($status == 1) ? 'enabled' : 'disabled';
            $notif_msg = "Product '$product_name' has been $status_text.";
        } else {
            $notif_msg = "Product '$product_name' has been updated.";
        }
        $stmt_n = $con->prepare("INSERT INTO tb_notifications (type, source_id, is_read, created_at, message) VALUES (?, ?, '2', ?, ?)");
        $notif_type = 'product_edit';
        $stmt_n->bind_param("ssss", $notif_type, $id, $notif_date, $notif_msg);
        $stmt_n->execute();

        $_SESSION['success'] = "Product Updated Successfully";
        header("Location: latest_products.php");
        exit();
    } else {
        $_SESSION['error'] = "Update Failed: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Zyroid Theme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #009444;
            --bg-body: #1a1a1a;
            --text-main: #ffffff;
            --bg-surface: rgb(37 37 37);
            --border-color: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.08);
            --font-head: 'Rajdhani', sans-serif;
            --font-body: 'Poppins', sans-serif;
        }

        body {
            background: var(--bg-body);
            font-family: var(--font-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            z-index: 1040;
            transition: transform 0.3s ease;
            overflow-y: auto;
            white-space: nowrap;
        }

        .sidebar-brand {
            padding: 1.5rem;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid var(--glass-border);
            text-decoration: none;
            display: block;
            font-family: var(--font-head);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 5px;
            padding: 16px 2.2rem;
            display: flex;
            align-items: center;
            transition: 0.2s;
            text-decoration: none;
            font-family: var(--font-head);
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
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 15px; }
            #overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
                backdrop-filter: blur(2px);
            }
            #overlay.active { display: block; }
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff;
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            box-shadow: none;
            color: #fff;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 10px 25px;
        }
        .btn-primary:hover { background-color: #007a38; }
        .btn-danger-soft {
            background: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
            transition: 0.3s;
        }
        .btn-danger-soft:hover {
            background: #dc3545;
            color: #fff;
        }
        .img-preview-box {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid var(--glass-border);
            background: #2a2a2a;
        }
        .alert-floating {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 2000;
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
    </style>
</head>

<body>

    <div id="overlay"></div>

    <nav id="sidebar" class="sidebar">
        <a href="dashboard.php" class="sidebar-brand"><i class="bi bi-phone me-2"></i>ZYROID</a>
        <button id="sidebarClose" class="btn btn-link text-white position-absolute top-0 end-0 mt-3 me-3 d-lg-none">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a class="nav-link <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'active' : 'collapsed'; ?> d-flex gap-1 align-items-center"
                    href="#customerSubmenu" data-bs-toggle="collapse" role="button"
                    aria-expanded="<?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'true' : 'false'; ?>"
                    aria-controls="customerSubmenu">
                    <i class="bi bi-people me-2"></i> <span>Users</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['customers.php', 'customers-detail.php']) ? 'show' : ''; ?>" id="customerSubmenu">
                    <ul class="submenu flex-column list-unstyled ps-4 bg-black bg-opacity-10">
                        <li class="nav-item">
                            <a class="nav-link py-2 <?php echo ($current_page == 'customers.php' || $current_page == 'customers-detail.php') ? 'active' : ''; ?>" href="customers.php">Users List</a>
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
                    <ul class="submenu flex-column list-unstyled ps-4 bg-black bg-opacity-10">
                        <li class="nav-item"><a class="nav-link py-2 <?php echo ($current_page == 'latest_products.php') ? 'active' : ''; ?>" href="latest_products.php">Latest Products</a></li>
                        <li class="nav-item"><a class="nav-link py-2 <?php echo ($current_page == 'iphone_products.php') ? 'active' : ''; ?>" href="iphone_products.php">iPhone Products</a></li>
                        <li class="nav-item"><a class="nav-link py-2 <?php echo ($current_page == 'android_products.php') ? 'active' : ''; ?>" href="android_products.php">Android Products</a></li>
                        <li class="nav-item"><a class="nav-link py-2 <?php echo ($current_page == 'gaming_products.php') ? 'active' : ''; ?>" href="gaming_products.php">Gaming Products</a></li>
                        <li class="nav-item"><a class="nav-link py-2 <?php echo ($current_page == 'accessories.php') ? 'active' : ''; ?>" href="accessories.php">Accessories</a></li>
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
            <div class="alert alert-success alert-floating fade show"><i class="bi bi-check-circle me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-floating fade show"><i class="bi bi-exclamation-triangle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light d-lg-none me-3"><i class="bi bi-list"></i></button>
                <div>
                    <h2 class="h4 fw-bold mb-0 text-white">Edit Product</h2>
                    <p class="text-white-50 small mb-0">Manage product details and visuals</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="latest_products.php" class="btn btn-outline-light">Back</a>
            </div>
        </div>

        <div class="card p-3 p-md-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-white-50">Product Name</label>
                                <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($data['product_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php while ($cat = $cat_list_res->fetch_assoc()) {
                                        $selected = ($data['category'] == $cat['cat_name']) ? 'selected' : '';
                                        echo "<option class='text-dark' value='" . $cat['cat_name'] . "' $selected>" . $cat['cat_name'] . "</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Price ($)</label>
                                <input type="number" step="0.01" name="product_price" class="form-control" value="<?php echo $data['product_price']; ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50">Description</label>
                                <textarea name="product_des" class="form-control" rows="5"><?php echo $data['product_des']; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-white-50">Status</label>
                                <select name="en_des_pro" class="form-select">
                                    <option class="text-dark" value="1" <?php echo ($data['en_des_pro'] == '1') ? 'selected' : ''; ?>>Active</option>
                                    <option class="text-dark" value="0" <?php echo ($data['en_des_pro'] == '0') ? 'selected' : ''; ?>>Deactivated</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-12">
                                <label class="form-label text-white-50">Stocks</label>
                                <input type="number" name="stocks" class="form-control" value="<?php echo $data['stocks']; ?>" required>
                            </div>
                            <div class="col-md-6 col-lg-12">
                                <label class="form-label text-white-50">Storage</label>
                                <select name="device_storage" class="form-select">
                                    <option class="text-dark" value="1TB" <?php echo ($data['device_storage'] == '1TB') ? 'selected' : ''; ?>>1TB</option>
                                    <option class="text-dark" value="512GB" <?php echo ($data['device_storage'] == '512GB') ? 'selected' : ''; ?>>512GB</option>
                                    <option class="text-dark" value="256GB" <?php echo ($data['device_storage'] == '256GB') ? 'selected' : ''; ?>>256GB</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50">Color</label>
                                <input type="text" name="product_color" class="form-control" value="<?php echo $data['product_color']; ?>">
                            </div>
                            
                            <div class="col-12 mt-3">
                                <label class="form-label text-white-50">Images</label>
                                <input type="file" name="image[]" id="fileInput" class="form-control mb-2" multiple accept="image/*">
                                <div class="d-flex flex-wrap gap-2" id="imagePreview">
                                    <?php if (!empty($data['product_image'])):
                                        $imgs = explode(',', $data['product_image']);
                                        foreach ($imgs as $img): ?>
                                            <img src="product_images/<?php echo trim($img); ?>" class="img-preview-box">
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end border-top border-secondary pt-3">
                        <button type="submit" name="update" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => bootstrap.Alert.getOrCreateInstance(el).close());
        }, 3000);

        document.getElementById('fileInput').addEventListener('change', function(e) {
            const container = document.getElementById('imagePreview');
            container.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('img-preview-box');
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
</body>
</html>