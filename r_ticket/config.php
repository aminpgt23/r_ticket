<?php
// config.php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_NAME', 'public');
define('DB_PORT', 3306);

// Koneksi Database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    return $conn;
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function generateRequestId() {
    return 'REQ -' . strtoupper(substr(uniqid(), -9));
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d/m/Y H:i', strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'waiting' => '<span class="badge bg-warning">Menunggu</span>',
        'accepted' => '<span class="badge bg-info">Diterima</span>',
        'close' => '<span class="badge bg-success">Selesai</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

function getStatusPekerjaanBadge($status) {
    $badges = [
        'on progress' => '<span class="badge bg-primary">On Progress</span>',
        'complete' => '<span class="badge bg-success">Complete</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">-</span>';
}
?>