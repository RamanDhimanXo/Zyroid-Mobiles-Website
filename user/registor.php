<?php
session_start();
require_once('../db_config.php');

if (isset($_POST['sbt'])) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $cpass = $_POST['con_pass'] ?? '';

    if (empty($name) || empty($phone) || empty($email) || empty($pass) || empty($cpass)) {
        $_SESSION['error'] = "All fields are required";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $_SESSION['error'] = "Name must contain only letters";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        $_SESSION['error'] = "Mobile number must be 10-15 digits";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
    } elseif (strlen($pass) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters";
    } elseif ($pass !== $cpass) {
        $_SESSION['error'] = "Passwords do not match";
    } else {
        $stmt = $con->prepare("SELECT id FROM tb_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $check = $stmt->get_result();

        if ($check->num_rows > 0) {
            $_SESSION['error'] = 'User already exists';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $con->prepare("INSERT INTO tb_users (user, phone, email, pass, user_status) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $phone, $email, $hash);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Account created successfully! Please login.';
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Zyroid</title>

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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            perspective: 1000px;
            padding: 40px 0;
        }

        .user-card {
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px 50px;
            width: 100%;
            max-width: 600px;
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

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .custom-toast {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            border-left: 4px solid #ff3b30;
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
            transition: 0.5s;
        }

        .custom-toast.show {
            transform: translateX(0);
            opacity: 1;
        }
    </style>
</head>

<body>

    <div class="toast-container" id="toastArea"></div>

    <div class="user-card" id="userCard">
        <div class="scanner-line"></div>

        <div class="text-center mb-5">
            <h3 class="fw-bold text-white mb-1">Create User Identity</h3>
            <p class="text-white-50 small">Register new user account</p>
        </div>

        <form id="regForm" method="POST" novalidate>
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="fullname" name="name" placeholder="Full Name"
                            required>
                        <label for="fullname">Full Name</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email">Email Address</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" required>
                        <label for="phone">Phone Number</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="pass" name="pass" placeholder="Password"
                            required>
                        <label for="pass">Password</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="con_pass" name="con_pass" placeholder="Confirm"
                            required>
                        <label for="con_pass">Confirm Password</label>
                    </div>
                </div>
            </div>

            <div class="form-check mt-3 mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small text-white-50" for="terms">
                    I acknowledge the data security protocols
                </label>
            </div>

            <button type="submit" name="sbt" class="btn-zyroid" id="regBtn">
                <span id="btnText">Establish Identity</span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-white-50 small mb-2">Already have credentials?
                <a href="login.php" class="text-success text-decoration-none fw-bold hover-link">Login Here</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.to("#userCard", { opacity: 1, scale: 1, duration: 1, ease: "back.out(1.2)" });

            <?php if (isset($_SESSION['error'])): ?>
                showToast("<?php echo $_SESSION['error']; ?>");
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });

        const card = document.getElementById('userCard');
        document.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth / 2 - e.pageX) / 40;
            const y = (window.innerHeight / 2 - e.pageY) / 40;
            gsap.to(card, { rotationY: x, rotationX: -y, duration: 0.5, ease: "power2.out" });
        });

        function showToast(message) {
            const container = document.getElementById('toastArea');
            const toast = document.createElement('div');
            toast.className = `custom-toast`;
            toast.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger"></i><div><strong class="d-block text-uppercase small">Error</strong><span class="small text-white-50">${message}</span></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 4000);
        }

        const form = document.getElementById('regForm');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            const name = document.getElementById('fullname');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const pass = document.getElementById('pass');
            const conPass = document.getElementById('con_pass');
            const terms = document.getElementById('terms');

            const nameRegex = /^[a-zA-Z\s]+$/;
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            const phoneRegex = /^[0-9]{10,15}$/;
            const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            [name, email, phone, pass, conPass].forEach(el => el.classList.remove('is-invalid'));

            if (!nameRegex.test(name.value)) { isValid = false; name.classList.add('is-invalid'); showToast("Name must contain only letters"); }
            else if (!emailRegex.test(email.value)) { isValid = false; email.classList.add('is-invalid'); showToast("Invalid Email Format"); }
            else if (!phoneRegex.test(phone.value)) { isValid = false; phone.classList.add('is-invalid'); showToast("Phone must be 10-15 digits"); }
            else if (!passRegex.test(pass.value)) { isValid = false; pass.classList.add('is-invalid'); showToast("Weak Password (Use Upper, Lower, Number, Symbol)"); }
            else if (pass.value !== conPass.value) { isValid = false; conPass.classList.add('is-invalid'); showToast("Passwords do not match"); }
            else if (!terms.checked) { isValid = false; showToast("Please accept the terms"); }

            if (!isValid) {
                e.preventDefault();
                gsap.fromTo(card, { x: -10 }, { x: 10, duration: 0.1, repeat: 5, yoyo: true });
            } else {
                const btn = document.getElementById('regBtn');
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
                document.getElementById('btnText').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registering...';
            }
        });
    </script>

</body>

</html>