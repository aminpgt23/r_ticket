<?php
require 'config.php';

// ===============================
// HEADER JSON
// ===============================
header('Content-Type: application/json; charset=utf-8');

// ===============================
// TANGKAP ERROR PHP -> buang
// ===============================
ob_start();

$response = [
    "success" => true,
    "count" => 0,
    "items" => [],
    "error" => null
];

try {

    session_start();

    // Pastikan user login
    if (!isset($_SESSION['nip'])) {
        throw new Exception("User not authenticated.");
    }

    // Koneksi database
    $conn = getConnection();

    // ===============================
    // QUERY NOTIFIKASI
    // ===============================
    $sql = "
        SELECT 
            request_id,
            fullname,
            permintaan,
            problem_location,
            date,
            status,
            eksekutor
        FROM ticket
        WHERE status IN ('waiting','pending')
          AND (eksekutor IS NULL OR eksekutor = '')
        ORDER BY date DESC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("SQL ERROR: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $response["items"][] = $row;
    }

    $response["count"] = count($response["items"]);

} catch (Exception $e) {
    $response["success"] = false;
    $response["error"] = $e->getMessage();
}

// ===============================
// HAPUS OUTPUT ERROR <br><b>
// ===============================
ob_end_clean();

// ===============================
// KIRIM JSON VALID
// ===============================
echo json_encode($response);
exit;
