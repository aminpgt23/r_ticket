<?php
require 'config.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

$nip = $_SESSION['nip'];
$endpoint = $data['endpoint'];
$auth = $data['keys']['auth'];
$p256dh = $data['keys']['p256dh'];

$conn = getConnection();

// Cek jika endpoint sudah ada
$check = $conn->prepare("SELECT id FROM push_subscribers WHERE endpoint=?");
$check->bind_param("s", $endpoint);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    $stmt = $conn->prepare("
        INSERT INTO push_subscribers (nip, endpoint, auth_key, p256dh_key)
        VALUES (?,?,?,?)
    ");
    $stmt->bind_param("ssss", $nip, $endpoint, $auth, $p256dh);
    $stmt->execute();
}

echo json_encode(["success" => true]);
