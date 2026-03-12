<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$id = $_POST['id'] ?? null;
$sqmpest = $_POST['sqmpest'] ?? '';

if (!$id) {
    header('Location: ticket.php');
    exit;
}

$stmt = $conn->prepare("UPDATE ticket SET sqmpest = ? WHERE id = ? AND eksekutor = ?");
$stmt->bind_param('sis', $sqmpest, $id, $_SESSION['nip']);
$stmt->execute();
$conn->close();

header('Location: ./index.php');
exit;
?>
