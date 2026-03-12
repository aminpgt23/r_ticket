<?php
// login.php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = $_POST['nip'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($nip && $password) {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nip, `name`, pass, `admin`, fullname, account FROM users WHERE nip = ? AND status = 1");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['pass'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nip'] = $user['nip'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['admin'] = $user['admin'];
                $_SESSION['account'] = $user['account'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'NIP atau password salah!';
            }
        } else {
            $error = 'NIP atau password salah!';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Harap isi semua field!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - R-Ticket</title>

<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#aecbf7ff">
<link rel="shortcut icon" href="logo.jpg">

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- BOOTSTRAP ICONS (WAJIB) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<!-- <script src="firebase-config.js"></script> -->
<!-- <button id="enableNotif">Aktifkan Notifikasi</button>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

<script>
let swReg = null;

// =========================
// REGISTER SERVICE WORKER
// =========================
if ("serviceWorker" in navigator) {
  navigator.serviceWorker
    .register("/firebase-messaging-sw.js")
    .then(reg => {
      console.log("✅ SW READY");
      swReg = reg;

      // Firebase config
      firebase.initializeApp({
        apiKey: "AIzaSyBtt_HQzC55eVnmsIyGIXYtK5rMXm7Eh2Q",
        projectId: "rticket-812d3",
        messagingSenderId: "1004822054480",
        appId: "1:1004822054480:web:ad43f9bae03c48bf6b7355"
      });

      console.log("✅ Firebase initialized");
    })
    .catch(err => {
      console.error("❌ SW REGISTER ERROR:", err);
    });
}

// =========================
// INIT FCM (GLOBAL FUNCTION)
// =========================
async function initFCM(reg) {
  try {
    console.log("🔔 Init FCM start");

    if (!reg) {
      console.error("❌ ServiceWorker belum siap");
      return;
    }

    console.log("Notification permission BEFORE:", Notification.permission);

    const permission = await Notification.requestPermission();
    console.log("Notification permission AFTER:", permission);

    if (permission !== "granted") {
      console.warn("❌ Permission not granted");
      return;
    }

    const messaging = firebase.messaging();

    const token = await messaging.getToken({
      vapidKey: "BEq07VU6lTu1ke2KClKQlTClCY_iNFW-W9iq7yftdhXsNLi6_21BMNo4owa8HLBlvVtxqpq4WagH5feub9Ep1Hs",
      serviceWorkerRegistration: reg
    });

    if (!token) {
      console.error("❌ TOKEN NULL");
      return;
    }

    console.log("🔥 FCM TOKEN:", token);

    const res = await fetch("save_fcm_token.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ token })
    });

    console.log("📡 Save token status:", res.status);

    const json = await res.json();
    console.log("✅ SAVE TOKEN RESPONSE:", json);

  } catch (err) {
    console.error("❌ INIT FCM ERROR:", err);
  }
}

// =========================
// BUTTON HANDLER
// =========================
document.getElementById("enableNotif").addEventListener("click", () => {
  console.log("🖱 Enable notif clicked");
  initFCM(swReg);
});
</script> -->


<style>
:root {
    --brand: #0d6efd;
}

/* ===== BASE ===== */
body {
    min-height: 100vh;
    background: linear-gradient(180deg,#e8f0ff,#f4f6f9,#eef3ff);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow-x: hidden;
}

/* ===== WAVES ===== */
.wave {
    position: fixed;
    width: 100%;
    left: 0;
    z-index: -1;
    background-size: cover;
}
.wave.top {
    top: 0;
    height: 140px;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 320' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%230d6efd' fill-opacity='0.18' d='M0,160L48,176C96,192,192,224,288,224C384,224,480,192,576,170.7C672,149,768,139,864,160L1440,128L1440,0Z'/%3E%3C/svg%3E");
}
.wave.bottom {
    bottom: 0;
    height: 160px;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 320' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%230d6efd' fill-opacity='0.12' d='M0,64L48,80C96,96,192,128,288,144C384,160,480,160,576,170.7L1440,64L1440,320Z'/%3E%3C/svg%3E");
}

/* ===== CARD ===== */
.login-card {
    width: 100%;
    max-width: 380px;
    border-radius: 26px;
    padding: 28px 24px;
    backdrop-filter: blur(14px);
}

/* ===== TEXT ===== */
.title {
    font-size: 22px;
    font-weight: 700;
    text-align: center;
}
.subtitle {
    font-size: 13px;
    text-align: center;
    color: #6c757d;
    margin-bottom: 22px;
}

/* ===== FORM ===== */
.form-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
}

/* 🔥 FIX INPUT HITAM IOS/ANDROID */
.form-control {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 14px;
    border: 1px solid #ced4da;
}

.form-control:focus {
    background-color: #ffffff !important;
    color: #212529 !important;
    box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
}

/* ===== PASSWORD ICON ===== */
.password-wrap {
    position: relative;
}
.password-wrap .form-control {
    padding-right: 46px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border: none;
    background: transparent;
    color: #6c757d;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.toggle-password:hover {
    color: var(--brand);
}

/* ===== BUTTON ===== */
.btn-login {
    border-radius: 16px;
    padding: 12px;
    font-weight: 600;
}

.footer {
    font-size: 12px;
    text-align: center;
    margin-top: 16px;
}
</style>
</head>

<body>

<div class="wave top"></div>
<div class="wave bottom"></div>

<div class="login-card">

    <div class="title">R-Ticket</div>
    <div class="subtitle">Silakan login untuk melanjutkan</div>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" class="form-control" required>
                <button type="button" class="toggle-password" onclick="togglePassword()">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button class="btn w-100 btn-login text-light" style="background-color: #85b0f1ff">Login</button>

        <!-- ADD TO HOME SCREEN -->
        <button type="button" id="installBtn" class="btn  w-100 mt-3 d-none">
            <i class="bi bi-plus-square"></i> Add to Home Screen
        </button>
    </form>

    <div class="footer text-muted">
        Belum punya akun? <i style=" text-decoration: none;">Hubungi Team Barcode</i>
    </div>

</div>

<script>
/* TOGGLE PASSWORD */
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.querySelector('.toggle-password i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

/* ADD TO HOME SCREEN */
let deferredPrompt;
const installBtn = document.getElementById('installBtn');

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    installBtn.classList.remove('d-none');
    installBtn.onclick = async () => {
        deferredPrompt.prompt();
        deferredPrompt = null;
    };
});

/* IOS */
if (/iphone|ipad|ipod/i.test(navigator.userAgent) && !window.navigator.standalone) {
    installBtn.classList.remove('d-none');
    installBtn.innerHTML = '<i class="bi bi-share"></i> Add to Home Screen';
    installBtn.onclick = () => {
        alert('Tap Share → Add to Home Screen');
    };
}
</script>

</body>
</html>
