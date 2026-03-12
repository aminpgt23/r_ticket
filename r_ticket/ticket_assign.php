<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    die("Akses ditolak!");
}

$id = $_GET['id'] ?? '';
$assignee = $_GET['eksekutor'] ?? '';

if ($id && $assignee) {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE ticket SET eksekutor = ? WHERE id = ?");
    $stmt->bind_param('si', $assignee, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: index.php");
exit();
