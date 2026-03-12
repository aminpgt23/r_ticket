<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    die("Akses ditolak!");
}

// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');
// Ambil data dari POST
$id          = $_POST['id'] ?? '';
$status      = $_POST['status'] ?? '';
$kategori    = $_POST['kategori'] ?? '';
$eksekutor   = $_POST['eksekutor'] ?? '';
$waktu_mulai = $_POST['waktu_mulai'] ?? '';

if (!empty($id)) {
    $conn = getConnection();

    $fields = [];
    $params = [];
    $types  = '';

    // Update status
    if (in_array($status, ['waiting', 'accepted', 'close'])) {
        $fields[] = "status = ?";
        $params[] = $status;
        $types   .= 's';
    }

    if (in_array($kategori, ['Hardware', 'Software', 'Network', 'Data', 'Administration', 'Documentation', 'Budgeting' , 'Purchase Requestion', 'Inventory Control'])) {
        $fields[] = "kategori = ?";
        $params[] = $kategori;
        $types   .= 's';
    }

    // Update eksekutor
    if (!empty($eksekutor)) {
        $fields[] = "eksekutor = ?";
        $params[] = $eksekutor;
        $types   .= 's';
    }

    // Update waktu pengerjaan
    if (!empty($waktu_mulai)) {
        $fields[] = "waktu_pengerjaan = ?";
        $params[] = date('Y-m-d H:i:s', strtotime($waktu_mulai));
        $types   .= 's';
    }

    if (!empty($fields)) {
        $sql = "UPDATE ticket SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;
        $types   .= 'i';

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->close();
}

header("Location: index.php");
exit();
