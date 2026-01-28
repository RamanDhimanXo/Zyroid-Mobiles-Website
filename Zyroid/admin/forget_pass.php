<?php
    session_start();
    include('../db_config.php');
    $show_reset = false;
    $email_val = "";

    if(isset($_POST['check_email'])){
        $email = $_POST['email'];
        $qr = "SELECT * FROM `tb_admin` WHERE `email` = '$email'";
        $res = mysqli_query($con,$qr);
        if(mysqli_num_rows($res) > 0){
            $show_reset = true;
            $email_val = $email;
        }else{
            echo "<script>window.onload = function() { showToast('Email Not Found in Database', 'error'); };</script>";
        }
    }
    if(isset($_POST['update_pass'])){
        $email = $_POST['email_hidden'];
        $pass = md5($_POST['new_pass']);
        $con_pass = $_POST['con_pass'];
        if($_POST['new_pass'] == $con_pass){
            $upd = "UPDATE `tb_admin` SET `pass` = '$pass' WHERE `email` = '$email'";
            mysqli_query($con, $upd);
            $_SESSION['success'] = "Password Updated Successfully";
            header("Location: index.php");
            exit();
        }else{
            echo "<script>window.onload = function() { showToast('Passwords do not match', 'error'); };</script>";
            $show_reset = true;
            $email_val = $email;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery - Zyroid Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            perspective: 1000px;
        }

        .admin-card {
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 50px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
            opacity: 0; 
            transform: scale(0.9);
            position: relative;
            z-index: 2;
        }

        h3 { font-family: var(--font-head); text-transform: uppercase; letter-spacing: 1px; }

        .form-floating > .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            transition: 0.3s;
        }
        .form-floating > .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 148, 68, 0.3);
            color: #fff;
        }
        
        .form-control.is-invalid {
            border-color: #ff3b30 !important;
            box-shadow: 0 0 15px rgba(255, 59, 48, 0.3) !important;
        }

        .form-floating > label { color: rgba(255, 255, 255, 0.5); }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
            opacity: 1;
        }

        .btn-zyroid {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            text-transform: uppercase;
            font-family: var(--font-head);
            letter-spacing: 1px;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-zyroid:hover {
            background: #007a38;
            box-shadow: 0 0 25px rgba(0, 148, 68, 0.4);
            transform: translateY(-2px);
        }

        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .custom-toast {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            border-left: 4px solid var(--primary);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(100%);
            opacity: 0;
            transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .custom-toast.error { border-left-color: #ff3b30; }
        .custom-toast.show { transform: translateX(0); opacity: 1; }

        .scanner-line {
            position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            opacity: 0.5; animation: scan 3s linear infinite; pointer-events: none;
        }
        @keyframes scan { 0% { top: 0; } 100% { top: 100%; } }
    </style>
</head>
<body>

    <div class="toast-container" id="toastArea"></div>

    <div class="admin-card" id="adminCard">
        <div class="scanner-line"></div>
        
        <div class="text-center mb-5">
            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: rgba(0,148,68,0.1); border-radius: 50%; border: 1px solid rgba(0,148,68,0.3);">
                <i class="bi bi-shield-lock-fill fs-2 text-success"></i>
            </div>
            <h3 class="fw-bold text-white mb-1">Recovery Mode</h3>
            <p class="text-white-50 small">Secure Admin Account Retrieval</p>
        </div>

        <form id="recoveryForm" method="POST" novalidate>
            <?php if(!$show_reset){ ?>
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Admin Email Address</label>
                </div>

                <button type="submit" name="check_email" class="btn-zyroid" id="checkBtn">
                    <span id="btnText">Verify Identity</span>
                </button>
            <?php } else { ?>
                <input type="hidden" name="email_hidden" value="<?php echo $email_val; ?>">
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="new_pass" name="new_pass" placeholder="New Password" required>
                    <label for="new_pass">New Password</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="con_pass" name="con_pass" placeholder="Confirm Password" required>
                    <label for="con_pass">Confirm Password</label>
                </div>
                
                <button type="submit" name="update_pass" class="btn-zyroid" id="updateBtn">
                    <span id="btnText">Update Credentials</span>
                </button>
            <?php } ?>
        </form>

        <div class="mt-4 text-center">
            <a href="index.php" class="text-white-50 text-decoration-none small hover-link" style="transition:0.3s">
                <i class="bi bi-arrow-left me-1"></i> Return to Login
            </a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.to("#adminCard", { opacity: 1, scale: 1, duration: 1, ease: "back.out(1.2)" });
        });

        const card = document.getElementById('adminCard');
        document.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth / 2 - e.pageX) / 30;
            const y = (window.innerHeight / 2 - e.pageY) / 30;
            gsap.to(card, { rotationY: x, rotationX: -y, duration: 0.5, ease: "power2.out" });
        });

        function showToast(message, type = "success") {
            const container = document.getElementById('toastArea');
            const toast = document.createElement('div');
            let icon = type === 'error' ? '<i class="bi bi-exclamation-triangle-fill text-danger"></i>' : '<i class="bi bi-check-circle-fill text-success"></i>';
            let borderClass = type === 'error' ? 'error' : '';

            toast.className = `custom-toast ${borderClass}`;
            toast.innerHTML = `${icon}<div><strong class="d-block text-uppercase small" style="letter-spacing:1px;">${type === 'error' ? 'Error' : 'Success'}</strong><span class="small text-white-50">${message}</span></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 4000);
        }

        const form = document.getElementById('recoveryForm');
        
        if(form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                const emailInput = document.getElementById('email');
                if(emailInput) {
                    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                    emailInput.classList.remove('is-invalid');
                    
                    if (!emailRegex.test(emailInput.value)) {
                        e.preventDefault();
                        isValid = false;
                        emailInput.classList.add('is-invalid');
                        showToast("Invalid Email Format", "error");
                    }
                }

                const passInput = document.getElementById('new_pass');
                const conPassInput = document.getElementById('con_pass');
                
                if(passInput && conPassInput) {
                    passInput.classList.remove('is-invalid');
                    conPassInput.classList.remove('is-invalid');

                    if(passInput.value.length < 6) {
                        e.preventDefault();
                        isValid = false;
                        passInput.classList.add('is-invalid');
                        showToast("Password must be at least 6 characters", "error");
                    } else if (passInput.value !== conPassInput.value) {
                        e.preventDefault();
                        isValid = false;
                        conPassInput.classList.add('is-invalid');
                        showToast("Passwords do not match", "error");
                    }
                }

                if (!isValid) {
                    gsap.fromTo(card, { x: -10 }, { x: 10, duration: 0.1, repeat: 5, yoyo: true });
                } else {
                    const btn = document.querySelector('.btn-zyroid');
                    const txt = document.getElementById('btnText');
                    btn.style.opacity = '0.8';
                    btn.style.pointerEvents = 'none';
                    txt.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                }
            });
        }

        document.querySelector('.hover-link').addEventListener('mouseenter', function() {
            gsap.to(this, { color: '#009444', x: -5, duration: 0.3 });
        });
        document.querySelector('.hover-link').addEventListener('mouseleave', function() {
            gsap.to(this, { color: 'rgba(255,255,255,0.5)', x: 0, duration: 0.3 });
        });
    </script>

</body>
</html>