<?php
require 'config.php';

header('Content-Type: application/json');
error_reporting(0); // ⬅️ PENTING
ini_set('display_errors', 0);

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(["success" => false, "msg" => "Invalid JSON"]);
    exit;
}

$token = $data['token'] ?? null;
$nip   = $_SESSION['nip'] ?? 'admin';

if (!$token) {
    echo json_encode(["success" => false, "msg" => "Token kosong"]);
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare("
    INSERT INTO fcm_tokens (nip, token)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE token = VALUES(token)
");

$stmt->bind_param("ss", $nip, $token);
$stmt->execute();

echo json_encode([
    "success" => true,
    "token"   => substr($token, 0, 25) . "..."
]);
