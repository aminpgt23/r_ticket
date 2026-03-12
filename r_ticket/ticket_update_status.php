<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    die("Akses ditolak!");
}

$id = $_GET['id'] ?? '';
$status = $_GET['status'] ?? '';

if ($id && in_array($status, ['waiting', 'accepted', 'close'])) {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE ticket SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: index.php");
exit();
