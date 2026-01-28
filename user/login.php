<?php
session_start();
require_once('../db_config.php');

if (isset($_POST['sbt'])) {
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    if (empty($email) || empty($pass)) {
        $_SESSION['error'] = "Please fill all fields";
    } elseif (!preg_match("/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/", $email)) {
        $_SESSION['error'] = "Invalid Email Format";
    } else {
        $stmt = $con->prepare("SELECT * FROM tb_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {

            if ($row['user_status'] == 1) {
                if (password_verify($pass, $row['pass']) || md5($pass) === $row['pass']) {
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['user'] = $row['user'];
                    $_SESSION['email'] = $row['email'];

                    $token = bin2hex(random_bytes(16));
                    try {
                        $stmt_t = $con->prepare("UPDATE tb_users SET token=? WHERE id=?");
                        $stmt_t->bind_param("si", $token, $row['id']);
                        $stmt_t->execute();
                        $_SESSION['token'] = $token;
                        header("Location: ../index.php");
                        exit();
                    } catch (mysqli_sql_exception $e) {
                        $_SESSION['error'] = "Database Error: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = 'Invalid Password';
                }
            } else {
                $_SESSION['error'] = 'Account Deactivated';
            }
        } else {
            $_SESSION['error'] = 'Email not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zyroid</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">
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

        .user-card {
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 50px;
            width: 40%;
            max-width: 900px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transform: scale(0.9);
            position: relative;
            z-index: 2;
        }

        @media (max-width: 576px) {
            .user-card {
                padding: 30px 20px;
            }
        }

        h1,
        h3 {
            font-family: var(--font-head);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-floating>.form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            transition: 0.3s;
        }

        .form-floating>.form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 148, 68, 0.3);
            color: #fff;
        }

        .form-control.is-invalid {
            border-color: #ff3b30 !important;
            box-shadow: 0 0 15px rgba(255, 59, 48, 0.3) !important;
        }

        .form-floating>label {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
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

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .custom-toast {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            border-left: 4px solid var(--primary);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(100%);
            opacity: 0;
            transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .custom-toast.error {
            border-left-color: #ff3b30;
        }

        .custom-toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .scanner-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            opacity: 0.5;
            animation: scan 3s linear infinite;
            pointer-events: none;
        }

        @keyframes scan {
            0% {
                top: 0;
            }

            100% {
                top: 100%;
            }
        }

        .side-image {
            background: url('https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1000&auto=format&fit=crop') center/cover;
            border-radius: 15px;
            min-height: 300px;
            opacity: 0.7;
            border: 1px solid var(--glass-border);
        }
    </style>
</head>

<body>
    <div class="toast-container" id="toastArea"></div>
    <div class="user-card" id="userCard">
        <div class="scanner-line"></div>
        <div class="row align-items-center justify-content-center g-5 ">
            <div class="col-lg-12">
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-white">User Login</h3>
                    <p class="text-white-50 small">Enter credentials to access dashboard</p>
                </div>

                <form method="POST" id="loginForm" novalidate>
                    <div class="form-floating mb-4">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com"
                            required>
                        <label for="email">Email Address</label>
                    </div>
                    <div class="form-floating mb-5">
                        <input type="password" class="form-control" id="pass" name="pass" placeholder="Password"
                            required>
                        <label for="pass">Password</label>
                    </div>

                    <button type="submit" name="sbt" class="btn-zyroid" id="loginBtn">
                        <span id="btnText">Access Dashboard</span>
                    </button>
                </form>

                <div class="text-center mt-5 d-flex justify-content-between">
                    <a href="forget_pass.php" class="text-white-50 text-decoration-none small hover-link">Forgot
                        Password?</a>
                        <a href="../index.php" class="text-success text-decoration-none small fw-bold">Back to
                            Website</a>
                        </div>
                    </div>
                    <div class="d-flex mt-3 justify-content-center align-items-center">
                        <a href="registor.php" class="text-white-50 text-decoration-none small hover-link">Create Account</a>
                    </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.to("#userCard", { opacity: 1, scale: 1, duration: 1, ease: "power3.out" });

            <?php if (isset($_SESSION['error'])): ?>
                showToast("<?php echo $_SESSION['error']; ?>", "error");
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                showToast("<?php echo $_SESSION['success']; ?>", "success");
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        });

        const card = document.getElementById('userCard');
        document.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth / 2 - e.pageX) / 40;
            const y = (window.innerHeight / 2 - e.pageY) / 40;
            gsap.to(card, { rotationY: x, rotationX: -y, duration: 0.5, ease: "power2.out" });
        });

        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('pass');
        const btn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');

        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        form.addEventListener('submit', function (e) {
            let isValid = true;

            emailInput.classList.remove('is-invalid');
            passInput.classList.remove('is-invalid');

            if (!emailRegex.test(emailInput.value)) {
                e.preventDefault();
                isValid = false;
                emailInput.classList.add('is-invalid');
                showToast("Invalid Email Format (e.g., admin@zyroid.com)", "error");
            }

            if (passInput.value.trim() === "") {
                e.preventDefault();
                isValid = false;
                passInput.classList.add('is-invalid');
                showToast("Password cannot be empty", "error");
            }

            if (isValid) {
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
                btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';
            } else {
                gsap.fromTo(card, { x: -10 }, { x: 10, duration: 0.1, repeat: 5, yoyo: true });
            }
        });

        function showToast(message, type = "success") {
            const container = document.getElementById('toastArea');
            const toast = document.createElement('div');

            let icon = type === 'error' ? '<i class="bi bi-exclamation-triangle-fill text-danger"></i>' : '<i class="bi bi-check-circle-fill text-success"></i>';
            let borderClass = type === 'error' ? 'error' : '';

            toast.className = `custom-toast ${borderClass}`;
            toast.innerHTML = `
                ${icon}
                <div>
                    <strong class="d-block text-uppercase small" style="letter-spacing:1px;">${type === 'error' ? 'Error' : 'Success'}</strong>
                    <span class="small text-white-50">${message}</span>
                </div>
            `;

            container.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 100);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>

</body>

</html>