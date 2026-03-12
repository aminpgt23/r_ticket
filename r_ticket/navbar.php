<!-- Navbar -->
<nav class="navbar-top">
    <div class="navbar-left">
        <a href="index.php" class="brand">
            <img src="logo2.png" alt="logo"> R-Ticket
        </a>
        <div class="desktop-menu">
            <a href="index.php" class="nav-link spa-link">Dashboard</a>
            <a href="ticket_form.php" class="nav-link spa-link">Buat Tiket</a>
            <a href="chart.php" class="nav-link spa-link">Chart</a>
            <?php if(isAdmin()): ?>
            <a href="users.php" class="nav-link spa-link">User</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="navbar-right">
        <div class="nav-item dropdown">
            <a href="#" class="nav-link profile">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                <?php if(isAdmin()): ?><span class="badge">Admin</span><?php endif; ?>
            </a>
            <div class="dropdown-menu">
                <a href="profile.php"> <i class="bi bi-person-circle"></i> Profile</a>
                <a href="https://script.google.com/macros/s/AKfycbw2e4T-WguurYI7e-8xcceYPHdpFAyAOln2DVbqwfdHxaKpDIN-Bov3FNzObDvu0TC7/exec"> <i class="bi bi-people"></i> Proyek</a>
                <a href="logout.php"> <i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
        <div class="nav-item dropdown">
            <a href="#" class="nav-link notif">
                <i class="bi bi-bell-fill"></i>
                <span class="notif-count" id="notifCount">0</span>
            </a>
            <div class="dropdown-menu notif-menu" id="notifList">

                <div class="notif-top">
                    <span>Notifikasi</span>
                    <button id="enableNotif" class="btn btn-sm btn-secondary text-light" style="font-size:10px; border-radius:20px;">Aktifkan Notifikasi</button>
                    <button class="notif-close" onclick="toggleNotif(false)">✕</button>
                </div>

                <ul id="notifItems" class="notif-items">
                    <!-- item notif via JS -->
                </ul>

            </div>

        </div>
    </div>
</nav>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="mobile-bottom-bar">
            <!-- SCAN FLOATING -->

    <a href="index.php" class="bottom-bar-link spa-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-house-fill"></i><span>Home</span>
    </a>
    <a href="ticket_form.php" class="bottom-bar-link spa-link <?= $current_page == 'ticket_form.php' ? 'active' : '' ?>">
        <i class="bi bi-pencil-square"></i><span>+ Tiket</span>
    </a>
    <!-- <a href="#" class="bottom-bar-link scan-btn spa-link">
        <i class="bi bi-arrow-up"></i>
    </a> -->
    <a href="chart.php" class="bottom-bar-link spa-link <?= $current_page == 'chart.php' ? 'active' : '' ?>">
        <i class="bi bi-bar-chart"></i><span>Chart</span>
    </a>
    <?php if(isAdmin()): ?>
    <a href="users.php" class="bottom-bar-link spa-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
        <i class="bi bi-people-fill"></i><span>User</span>
    </a>
    <?php endif; ?>
</div>

<!-- MOBILE UI KIT LOADING -->
<div id="ui-loader" aria-hidden="true">
  <div class="ui-loader-card">
    <div class="ui-spinner"></div>
    <div class="ui-loader-text">Loading</div>
  </div>
</div>


<p style="margin-bottom: 60px;"></p>
<audio id="notifSound">
    <source src="notif.mp3" type="audio/mp3">
</audio>
<style>
/* ======================================
   MOBILE UI KIT LOADER (iOS + Android)
====================================== */
#ui-loader {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(17, 24, 39, 0.45); /* iOS safe */
    transition: opacity .2s ease;
}

#ui-loader.active {
    display: flex;
}

/* Card */
.ui-loader-card {
    width: 140px;
    padding: 18px 14px 16px;
    background: #ffffff;
    border-radius: 22px;
    box-shadow:
        0 18px 40px rgba(0,0,0,.22),
        0 6px 14px rgba(0,0,0,.12);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    animation: iosPop .25s cubic-bezier(.4,0,.2,1);
}

/* Spinner (iOS native feel) */
.ui-spinner {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 3px solid rgba(0,0,0,.12);
    border-top-color: #0ea5e9;
    animation: spin 1s linear infinite;
}

/* Text */
.ui-loader-text {
    font-size: 13px;
    font-weight: 500;
    letter-spacing: .3px;
    color: #111827;
}

/* Animations */
@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes iosPop {
    from {
        transform: scale(.92);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* iOS Safari fixes */
@supports (-webkit-touch-callout: none) {
    #ui-loader {
        background: rgba(0,0,0,.55);
    }
}

/* Dark mode auto */
@media (prefers-color-scheme: dark) {
    .ui-loader-card {
        background: #1f2937;
    }
    .ui-loader-text {
        color: #f9fafb;
    }
    .ui-spinner {
        border-color: rgba(255,255,255,.2);
        border-top-color: #38bdf8;
    }
}

.mobile-bottom-bar {
    position: fixed;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 28px);
    max-width: 420px;
    height: 64px;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(20px);
    border-radius: 22px;
    box-shadow: 0 12px 30px rgba(0,0,0,.2);
    display: none;
    justify-content: space-around;
    padding-left: 20px;
    padding-right: 20px;
    align-items: center;
    z-index: 900;
}


.scan-btn {
    position: absolute;
    top: -28px; /* naik ke atas */
    left: 52%;
    transform: translateX(-50%);
    width: 62px;
    height: 62px;
    background: linear-gradient(135deg, #0ea5e9, #aecbf7ff);
    border-radius: 50%;
    border: 4px solid #fff;
    /* box-shadow: 0 12px 24px rgba(0,0,0,.25); */
    color: #fff !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    text-decoration: none;
}

.scan-btn i {
    font-size: 22px;
    margin-bottom: -2px;
}

.scan-btn span {
    font-size: 9px;
}

/* Efek tekan */
.scan-btn:active {
    transform: translateX(-50%) scale(.92);
}
</style>
<link rel="stylesheet" href="navbar.css">
<script>
/* ======================================
   SAFE MOBILE UI KIT LOADER
   (Redirect & Real Navigation Only)
====================================== */
(function () {
    const loader = document.getElementById('ui-loader');
    if (!loader) return;

    let loadingActive = false;

    window.showLoading = () => {
        if (loadingActive) return;
        loadingActive = true;
        loader.classList.add('active');
        loader.setAttribute('aria-hidden', 'false');
    };

    window.hideLoading = () => {
        loadingActive = false;
        loader.classList.remove('active');
        loader.setAttribute('aria-hidden', 'true');
    };

    /* =========================
       1️⃣ REAL PAGE REDIRECT
    ========================= */
    window.addEventListener('beforeunload', () => {
        showLoading();
    });

    /* =========================
       2️⃣ REAL LINK NAVIGATION
    ========================= */
    document.addEventListener('click', e => {
        const a = e.target.closest('a');
        if (!a) return;

        const href = a.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:')) return;

        // Abaikan
        if (
            a.target === '_blank' ||
            a.hasAttribute('download') ||
            a.hasAttribute('data-no-loader')
        ) return;

        // Hanya jika pindah halaman
        const current = location.pathname + location.search;
        const target = new URL(href, location.origin).pathname + new URL(href, location.origin).search;

        if (current !== target) {
            showLoading();
        }
    }, true);

    /* =========================
       3️⃣ FORM SUBMIT
    ========================= */
    document.addEventListener('submit', () => {
        showLoading();
    }, true);

})();
</script>


<script>

    let lastNotifCount = 0;

    function loadNotifications() {
        fetch("/r_ticket/check_notification.php")
            .then(res => res.json())
            .then(data => {

                // console.log("DEBUG JSON:", data); // ← CEK JSON

                const notifCount = data.count;
                const notifList = data.items;

                // Update badge count
                const badge = document.getElementById("notifCount");
                if (notifCount > 0) {
                    badge.style.display = "inline";
                    badge.textContent = notifCount;
                } else {
                    badge.style.display = "none";
                }

                // Notifikasi baru → bunyi
                if (notifCount > lastNotifCount && lastNotifCount !== 0) {
                    document.getElementById("notifSound").play();
                }
                lastNotifCount = notifCount;

                        let html = "";

                notifList.forEach(item => {
                    html += `
                        <li>
                            <a class="notif-card" href="ticket_detail.php?id=${item.request_id}">
                                
                                <div class="notif-header">
                                    <span class="notif-req">#${item.request_id}</span>
                                    <span class="notif-date">${item.date}</span>
                                </div>

                                <div class="notif-body">
                                    <div class="notif-line"><i class="bi bi-person-badge-fill"></i>  ${item.fullname}</div>
                                    <div class="notif-line"><i class="bi bi-chat-square-text-fill"></i> ${item.permintaan}</div>
                                    <div class="notif-line"><i class="bi bi-geo-alt-fill"></i> ${item.problem_location}</div>
                                </div>

                            </a>
                        </li>
                    `;
                });

                document.getElementById("notifItems").innerHTML =
                    html || `<li><div class="no-notif">Tidak ada notifikasi</div></li>`;

            })
            .catch(err => console.error("Error notif:", err));
    }

    setInterval(loadNotifications, 3000);
    loadNotifications();
    function toggleNotif(show) {
        const notif = document.getElementById('notifList');
        notif.style.display = show ? 'block' : 'none';
    }

    document.querySelector('.nav-link.notif').addEventListener('click', function(e){
        e.preventDefault();
        toggleNotif(true);
    });
  
</script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>

<script>
let swReg = null;

/* =========================
   REGISTER SERVICE WORKER
========================= */
if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/firebase-messaging-sw.js')
    .then(reg => {
      console.log('✅ SW READY');
      swReg = reg;

      // 🔥 Firebase INIT (SETELAH REGISTER — SESUAI KEINGINAN KAMU)
      firebase.initializeApp({
        apiKey: "AIzaSyBtt_HQzC55eVnmsIyGIXYtK5rMXm7Eh2Q",
        projectId: "rticket-812d3",
        messagingSenderId: "1004822054480",
        appId: "1:1004822054480:web:ad43f9bae03c48bf6b7355"
      });

      console.log('✅ Firebase initialized');
    })
    .catch(err => console.error('❌ SW ERROR', err));
}

/* =========================
   INIT FCM
========================= */
async function initFCM(reg) {
  try {
    if (!reg) {
      console.warn('⏳ Waiting SW...');
      reg = await navigator.serviceWorker.ready;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
      console.warn('❌ Permission denied');
      return;
    }

    const messaging = firebase.messaging();

    const token = await messaging.getToken({
      vapidKey: "BEq07VU6lTu1ke2KClKQlTClCY_iNFW-W9iq7yftdhXsNLi6_21BMNo4owa8HLBlvVtxqpq4WagH5feub9Ep1Hs",
      serviceWorkerRegistration: reg
    });

    if (!token) {
      console.error('❌ TOKEN NULL');
      return;
    }

    console.log('🔥 FCM TOKEN:', token);

    const res = await fetch('save_fcm_token.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token })
    });

    console.log('📡 Save token:', await res.text());

  } catch (err) {
    console.error('❌ INIT FCM ERROR', err);
  }
}

/* =========================
   BUTTON
========================= */
document.getElementById('enableNotif').onclick = () => {
  initFCM(swReg);
};
</script>