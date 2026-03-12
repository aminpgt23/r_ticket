<?php
// user_delete.php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);

if ($id && $id != $_SESSION['user_id']) {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: users.php');
exit();
?>