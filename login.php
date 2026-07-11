<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cash Khata - Login</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-blue: #2563eb;
        --dark-blue: #1e40af;
        --light-blue: #eff6ff;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        min-height: 100vh;
        background: var(--light-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-wrapper {
        display: flex;
        width: 100%;
        max-width: 900px;
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(37, 99, 235, 0.12);
    }

    .login-left {
        flex: 1;
        background: var(--primary-blue);
        color: #ffffff;
        padding: 50px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
    }

    .login-left .icon-box {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        margin-bottom: 25px;
    }

    .login-left h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .login-left p {
        font-size: 14px;
        opacity: 0.9;
        line-height: 1.7;
    }

    .login-right {
        flex: 1;
        padding: 50px 40px;
    }

    .login-right h2 {
        font-size: 24px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .login-right p.subtitle {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 30px;
    }

    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .input-group-custom {
        position: relative;
        margin-bottom: 20px;
    }

    .input-group-custom i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .input-group-custom input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
    }

    .input-group-custom input:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-login {
        width: 100%;
        padding: 13px;
        background: var(--primary-blue);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-login:hover {
        background: var(--dark-blue);
        transform: translateY(-1px);
    }

    .btn-login:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .error-box {
        background: #fef2f2;
        color: #dc2626;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 18px;
        display: none;
        align-items: center;
        gap: 8px;
    }

    @media (max-width: 767px) {
        .login-left {
            display: none;
        }
        .login-wrapper {
            border-radius: 16px;
        }
        .login-right {
            padding: 40px 26px;
        }
    }
</style>
</head>
<body>

<div class="login-wrapper" id="loginWrapper">
    <div class="login-left">
        <div class="icon-box"><i class="fa-solid fa-wallet"></i></div>
        <h1>Cash Khata</h1>
        <p>Manage your purchase, sales, stock, customer due, supplier due and daily expenses — all in one simple dashboard.</p>
    </div>

    <div class="login-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to manage your business</p>

        <div class="error-box" id="errorBox">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span id="errorText"></span>
        </div>

        <form id="loginForm">
            <div>
                <label class="form-label">Username</label>
                <div class="input-group-custom">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Enter username" required autocomplete="off">
                </div>
            </div>

            <div>
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span id="loginBtnText">Login</span>
            </button>
        </form>
    </div>
</div>

<!-- GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Entrance animation
    gsap.from("#loginWrapper", {
        y: 30,
        opacity: 0,
        duration: 0.7,
        ease: "power2.out"
    });

    const loginForm = document.getElementById('loginForm');
    const errorBox = document.getElementById('errorBox');
    const errorText = document.getElementById('errorText');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        errorBox.style.display = 'none';
        loginBtn.disabled = true;
        loginBtnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Please wait...';

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        try {
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (data.status === 'success') {
                window.location.href = 'index.php';
            } else {
                errorText.textContent = data.message;
                errorBox.style.display = 'flex';
                gsap.from(errorBox, { x: -10, duration: 0.3, ease: "power2.out" });
                loginBtn.disabled = false;
                loginBtnText.textContent = 'Login';
            }
        } catch (err) {
            errorText.textContent = 'Something went wrong. Please try again.';
            errorBox.style.display = 'flex';
            loginBtn.disabled = false;
            loginBtnText.textContent = 'Login';
        }
    });
</script>

</body>
</html>