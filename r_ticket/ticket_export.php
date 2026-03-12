<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$result = $conn->query("SELECT request_id, fullname, permintaan, problem_location, problem_device, status, status_pekerjaan, eksekutor, date FROM ticket ORDER BY date DESC");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ticket_export.xls");

echo "Request ID\tNama\tPermintaan\tLokasi\tDevice\tStatus\tProgress\tAssignee\tTanggal\n";
while ($row = $result->fetch_assoc()) {
    echo implode("\t", $row) . "\n";
}

$conn->close();
exit();
