<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
startSecureSession();

// If already logged in, redirect to app
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LedgerGo | Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="icon" href="assets/logo.png">

<style>
    body {
        background: #f4f6fb;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
    }
    .auth-card {
        max-width: 420px;
        width: 100%;
        border: none;
        border-radius: 18px;
        box-shadow: 0 10px 40px rgba(47, 91, 224, 0.12);
    }
    .auth-logo { width: 64px; height: 64px; object-fit: contain; }
    .btn-primary {
        background-color: #2F5BE0;
        border-color: #2F5BE0;
        border-radius: 10px;
    }
    .btn-primary:hover { background-color: #2547b8; border-color: #2547b8; }
    .form-control { border-radius: 10px; padding: 10px 14px; }
    .btn-google {
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }
    #loadingOverlay {
        position: fixed; inset: 0; background: #fff;
        display: flex; align-items: center; justify-content: center;
        z-index: 9999;
    }
</style>
</head>
<body>

<!-- Loading Screen -->
<div id="loadingOverlay">
    <img src="assets/loading.gif" alt="Loading" style="width:80px;">
</div>

<div class="card auth-card p-4 p-md-5" id="authCard" style="opacity:0;">

    <!-- LOGIN VIEW -->
    <div id="loginView">
        <div class="text-center mb-4">
            <img src="assets/logo.png" class="auth-logo mb-2" alt="LedgerGo">
            <h4 class="fw-bold mb-0">LedgerGo</h4>
            <small class="text-muted">Sign in to continue</small>
        </div>

        <div id="loginAlert" class="alert alert-danger d-none py-2" role="alert"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="loginEmail" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="loginPassword" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">Remember Me</label>
                </div>
                <a href="#" class="small text-decoration-none" id="forgotPasswordLink">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn">
                <span class="btn-text">Login</span>
                <span class="spinner-border spinner-border-sm d-none" id="loginSpinner"></span>
            </button>
        </form>

        <div class="text-center my-3 text-muted small">or</div>

        <button class="btn btn-google w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="googleLoginBtn">
            <i class="bi bi-google"></i> Continue with Google
        </button>
    </div>

    <!-- FORGOT PASSWORD VIEW -->
    <div id="forgotView" class="d-none">
        <div class="text-center mb-4">
            <img src="assets/logo.png" class="auth-logo mb-2" alt="LedgerGo">
            <h5 class="fw-bold mb-0">Reset Password</h5>
            <small class="text-muted">Enter your email to receive a reset link</small>
        </div>

        <div id="forgotAlert" class="alert d-none py-2"></div>

        <form id="forgotForm">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="forgotEmail" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mb-2">Send Reset Link</button>
            <button type="button" class="btn btn-light w-100 py-2" id="backToLoginBtn">Back to Login</button>
        </form>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-auth-compat.js"></script>
<script src="config/firebase-config.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>

<script>
window.addEventListener('load', () => {
    gsap.to('#loadingOverlay', { opacity: 0, duration: 0.4, onComplete: () => {
        document.getElementById('loadingOverlay').style.display = 'none';
    }});
    gsap.to('#authCard', { opacity: 1, y: 0, duration: 0.5, delay: 0.2 });
});

function showAlert(elId, message, type = 'danger') {
    const el = document.getElementById(elId);
    el.className = `alert alert-${type} py-2`;
    el.textContent = message;
    el.classList.remove('d-none');
}

function toggleLoading(btnId, spinnerId, isLoading) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    btn.disabled = isLoading;
    spinner.classList.toggle('d-none', !isLoading);
}

// ===== EMAIL/PASSWORD LOGIN =====
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    document.getElementById('loginAlert').classList.add('d-none');

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const remember = document.getElementById('rememberMe').checked;

    toggleLoading('loginBtn', 'loginSpinner', true);

    try {
        const persistence = remember
            ? firebase.auth.Auth.Persistence.LOCAL
            : firebase.auth.Auth.Persistence.SESSION;
        await auth.setPersistence(persistence);

        const cred = await auth.signInWithEmailAndPassword(email, password);
        await syncSessionWithServer(cred.user, remember);
    } catch (err) {
        showAlert('loginAlert', mapFirebaseError(err.code));
        toggleLoading('loginBtn', 'loginSpinner', false);
    }
});

// ===== GOOGLE LOGIN =====
document.getElementById('googleLoginBtn').addEventListener('click', async () => {
    const provider = new firebase.auth.GoogleAuthProvider();
    try {
        const cred = await auth.signInWithPopup(provider);
        await syncSessionWithServer(cred.user, true);
    } catch (err) {
        showAlert('loginAlert', mapFirebaseError(err.code));
    }
});

// ===== SYNC PHP SESSION AFTER FIREBASE LOGIN =====
async function syncSessionWithServer(firebaseUser, remember) {
    const idToken = await firebaseUser.getIdToken();

    const res = await fetch('api/auth/verify-session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idToken, remember })
    });
    const data = await res.json();

    if (data.success) {
        window.location.href = 'index.php';
    } else {
        showAlert('loginAlert', data.message || 'Login failed.');
        toggleLoading('loginBtn', 'loginSpinner', false);
    }
}

// ===== FORGOT PASSWORD =====
document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('loginView').classList.add('d-none');
    document.getElementById('forgotView').classList.remove('d-none');
});

document.getElementById('backToLoginBtn').addEventListener('click', () => {
    document.getElementById('forgotView').classList.add('d-none');
    document.getElementById('loginView').classList.remove('d-none');
});

document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('forgotEmail').value.trim();
    try {
        await auth.sendPasswordResetEmail(email);
        showAlert('forgotAlert', 'Reset link sent! Please check your email.', 'success');
    } catch (err) {
        showAlert('forgotAlert', mapFirebaseError(err.code));
    }
});

// ===== ERROR MAPPING =====
function mapFirebaseError(code) {
    const errors = {
        'auth/invalid-email': 'Invalid email address.',
        'auth/user-disabled': 'This account has been disabled.',
        'auth/user-not-found': 'No account found with this email.',
        'auth/wrong-password': 'Incorrect password.',
        'auth/invalid-credential': 'Invalid email or password.',
        'auth/too-many-requests': 'Too many attempts. Try again later.',
        'auth/popup-closed-by-user': 'Google login was cancelled.',
    };
    return errors[code] || 'Something went wrong. Please try again.';
}
</script>

</body>
</html>