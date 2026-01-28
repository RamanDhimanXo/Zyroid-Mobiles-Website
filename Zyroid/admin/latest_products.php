<?php
session_start();
include('../db_config.php');
$current_page = basename($_SERVER['PHP_SELF']);

$limit = 7;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = "";
$search_condition = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $search_condition = " WHERE product_name LIKE '%$search%' ";
}

$count_qr = "SELECT COUNT(*) AS total FROM tb_products $search_condition";
$count_res = mysqli_query($con, $count_qr);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

$qr = "SELECT * FROM tb_products $search_condition ORDER BY id DESC LIMIT $offset, $limit";
$res = mysqli_query($con, $qr);

$cat_list_qr = mysqli_query($con, "SELECT * FROM tb_categories");

if (isset($_POST['sub'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $cat = $_POST['category'];
    $desc = $_POST['description'];
    $stocks = $_POST['stocks'];
    $color = $_POST['product_color'];
    $storage = $_POST['device_storage'];
    $status = $_POST['en_des_pro'];

    $uploaded_images = [];
    $url = "product_images/";

    $error = false;

    if (empty($name)) {
        $_SESSION['error'] = "Product Name is required";
        $error = true;
    } elseif (!preg_match("/^[a-zA-Z0-9\s\-\.]+$/", $name)) {
        $_SESSION['error'] = "Product Name contains invalid characters";
        $error = true;
    } elseif (empty($price)) {
        $_SESSION['error'] = "Price is required";
        $error = true;
    } elseif (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
        $_SESSION['error'] = "Invalid Price Format (e.g., 10.99)";
        $error = true;
    } elseif (empty($stocks)) {
        $_SESSION['error'] = "Stock quantity is required";
        $error = true;
    } elseif (!preg_match("/^[0-9]+$/", $stocks)) {
        $_SESSION['error'] = "Stocks must be a valid integer";
        $error = true;
    }

    if ($error) {
        header("Location: latest_products.php");
        exit();
    } else {
        $stmt_check = mysqli_prepare($con, "SELECT id FROM tb_products WHERE product_name=?");
        mysqli_stmt_bind_param($stmt_check, "s", $name);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $_SESSION['error'] = "Product already exists";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            if (isset($_FILES['pic']['name']) && count($_FILES['pic']['name']) > 0) {
                $total_files = count($_FILES['pic']['name']);

                for ($i = 0; $i < $total_files; $i++) {
                    $filename = $_FILES['pic']['name'][$i];
                    $tmpname = $_FILES['pic']['tmp_name'][$i];

                    if ($filename != "") {
                        $target_file = $url . basename($filename);
                        if (move_uploaded_file($tmpname, $target_file)) {
                            $uploaded_images[] = $filename;
                        }
                    }
                }
            }

            $pic_string = implode(',', $uploaded_images);

            $nwpro = "INSERT INTO tb_products(product_name, product_price, category, product_des, stocks, product_color, device_storage, product_image, en_des_pro) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $nwpro);

            mysqli_stmt_bind_param($stmt, "sdssisssi", $name, $price, $cat, $desc, $stocks, $color, $storage, $pic_string, $status);

            if (mysqli_stmt_execute($stmt)) {
                $new_product_id = mysqli_insert_id($con);
                $_SESSION['success'] = "Product created successfully";

                $notif_msg = "New product '" . $name . "' has been added.";
                $notif_msg = mysqli_real_escape_string($con, $notif_msg);
                $notif_date = date('Y-m-d');
                $notif_type = 'product_add';
                $notif_q = "INSERT INTO `tb_notifications` (`type`, `source_id`, `is_read`, `created_at`, `message`) VALUES ('$notif_type', '$new_product_id', '2', '$notif_date', '$notif_msg')";
                mysqli_query($con, $notif_q);
            } else {
                $_SESSION['error'] = "Product not created";
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: latest_products.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Products - Zyroid Theme</title>
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

        ul {
            list-style-type: none;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            transition: all 0.3s ease;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }

        .table-custom {
            --bs-table-bg: transparent;
            color: #e0e0e0;
        }

        .product-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-control,
        .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #ffffffff;
        }

        .modal-content {
            background: var(--bg-body);
            border: 1px solid rgba(255, 255, 255, 0.05);
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

        h1, h2, h3, h4, h5, h6 { font-family: var(--font-head); }

        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.5s ease-out forwards;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .preview-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.2s;
        }
        .preview-img:hover { transform: scale(1.05); border-color: var(--primary-color); }
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
                <?php $cat_pages = ['latest_products.php', 'iphone_products.php', 'android_products.php', 'gaming_products.php', 'accessories.php', 'edit_products.php']; ?>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show alert-floating bg-success text-white border-0"
                role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-floating bg-danger text-white border-0"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-outline-light me-2"><i
                        class="bi bi-list"></i></button>
                <div>
                    <h2 class="h3 fw-bold mb-1">Latest Arrivals</h2>
                    <p class="text-white-50 mb-0">Recently added inventory</p>
                </div>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg me-2"></i>Add Product
            </button>
        </div>

        <form method="GET" class="d-flex gap-2 mb-4">
            <input type="text" name="search" class="form-control" placeholder="Search product name..."
                value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary px-4">Search</button>
            <?php if ($search): ?>
                <a href="latest_products.php" class="btn btn-outline-light">Clear</a>
            <?php endif; ?>
        </form>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Status</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($res)) {
                            $img_array = explode(',', $data['product_image']);
                            $thumb = isset($img_array[0]) ? trim($img_array[0]) : '';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="product_images/<?= $thumb ?>" class="product-img me-3">
                                        <div class="fw-bold text-white"><?= $data['product_name'] ?></div>
                                    </div>
                                </td>
                                <td><span
                                        class="badge bg-dark text-success border border-success"><?= $data['category'] ?></span>
                                </td>
                                <td class="text-white fw-bold"><?= $data['product_price'] ?></td>
                                <td>
                                    <div class="text-success small fw-bold">Remainings: <?= $data['stocks'] ?></div>
                                </td>
                                <td>
                                    <?php if (isset($data['en_des_pro']) && $data['en_des_pro'] == 1): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger">Deactivated</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_products.php?id=<?= $data['id'] ?>"
                                        class="btn btn-sm btn-outline-light me-1"><i class="bi bi-pencil"></i></a>
                                    <a href="delete_products.php?did=<?= $data['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div
                class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25">
                <div class="text-white-50 small">Showing <?php echo mysqli_num_rows($res); ?> products</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php if ($page <= 1)
                            echo 'disabled'; ?>">
                            <a class="page-link bg-transparent border-secondary text-white"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Prev</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if ($page == $i)
                                echo 'active'; ?>">
                                <a class="page-link <?php echo ($page == $i) ? 'bg-success border-success' : 'bg-transparent border-secondary text-white'; ?>"
                                    href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($page >= $total_pages)
                            echo 'disabled'; ?>">
                            <a class="page-link bg-transparent border-secondary text-white"
                                href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Product Name</label>
                                <input type="text" class="form-control" name="product_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="" class="text-dark">Select Category</option>
                                    <?php
                                    mysqli_data_seek($cat_list_qr, 0);
                                    while ($cat = mysqli_fetch_assoc($cat_list_qr)) {
                                        echo "<option class='text-dark' value='" . $cat['cat_name'] . "'>" . $cat['cat_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Price</label>
                                <input type="text" class="form-control" name="price" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Stock</label>
                                <input type="number" class="form-control" name="stocks" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Status</label>
                                <select name="en_des_pro" class="form-select" required>
                                    <option value="1" class="text-dark">Active</option>
                                    <option value="0" class="text-dark">Deactivated</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Color</label>
                                <select name="product_color" class="form-select">
                                    <option value="black" class="text-dark">Black</option>
                                    <option value="white" class="text-dark">White</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50">Storage</label>
                                <select name="device_storage" class="form-select" required>
                                    <option value="1TB" class="text-dark">1TB</option>
                                    <option value="512GB" class="text-dark">512GB</option>
                                    <option value="256GB" class="text-dark">256GB</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50">Product Images (Select Multiple)</label>
                                <input type="file" class="form-control" name="pic[]" id="productImages" accept="image/*" multiple required>
                                <small class="text-white-50">Hold Ctrl/Cmd to select multiple files</small>
                                <div id="imagePreview" class="d-flex gap-2 mt-3 flex-wrap"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="submit" class="btn btn-primary px-5" name="sub">Publish Product</button>
                    </div>
                </form>
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

        window.addEventListener('load', () => {
            const alerts = document.querySelectorAll('.alert-floating');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 3000);
            });
        });

        const imageInput = document.getElementById('productImages');
        const previewContainer = document.getElementById('imagePreview');

        if(imageInput) {
            imageInput.addEventListener('change', function(event) {
                previewContainer.innerHTML = '';
                const files = event.target.files;

                if (files) {
                    Array.from(files).forEach(file => {
                        if (file.type.match('image.*')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'preview-img';
                                previewContainer.appendChild(img);
                            }
                            reader.readAsDataURL(file);
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>