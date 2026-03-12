<?php
// ticket_form.php
require_once 'config.php';
date_default_timezone_set('Asia/Jakarta');
requireLogin();

$conn = getConnection();
$edit_mode = false;
$ticket = null;
$error = '';
$success = '';

// Edit mode
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM ticket WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($ticket) {
        // Cegah akses jika bukan admin atau pemilik tiket
        if (!isAdmin() && $ticket['nip'] !== $_SESSION['nip']) {
            header('Location: index.php');
            exit();
        }
        $edit_mode = true;
    }
}

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permintaan = trim($_POST['permintaan'] ?? '');
    $problem_location = trim($_POST['problem_location'] ?? '');
    $problem_device = trim($_POST['problem_device'] ?? '');
    // $waktu_pengerjaan = $_POST['waktu_pengerjaan'] ?? null;
    $waktu_pengerjaan = $_POST['waktu_pengerjaan'] ?? null;

if (!empty($waktu_pengerjaan)) {
    $waktu_pengerjaan = date('Y-m-d H:i:s', strtotime($waktu_pengerjaan));
} else {
    $waktu_pengerjaan = null;
}
    $kategori = $_POST['kategori'] ?? '';
    $sqmpest = $_POST['sqmpest'] ?? '';
    $status_pekerjaan = $_POST['status_pekerjaan'] ?? 'pending';
    $eksekutor = $_SESSION['nip']; // Auto eksekutor sesuai login

    // Jika buat tiket sendiri, auto accepted
    $status = 'close';

    if ($permintaan && $problem_location && $problem_device) {
        if ($edit_mode && $ticket) {
            // Update tiket
            $stmt = $conn->prepare("UPDATE ticket 
                SET permintaan=?, problem_location=?, problem_device=?, waktu_pengerjaan=?, kategori=?, sqmpest=?, status_pekerjaan=?, eksekutor=?, status=? 
                WHERE id=?");
            $stmt->bind_param(
                "sssssssssi",
                $permintaan,
                $problem_location,
                $problem_device,
                $waktu_pengerjaan,
                $kategori,
                $sqmpest,
                $status_pekerjaan,
                $eksekutor,
                $status,
                $id
            );
        } else {
            // Buat tiket baru
            $request_id = generateRequestId();
            $nip = $_SESSION['nip'];
            $fullname = $_SESSION['fullname'];
            date_default_timezone_set('Asia/Jakarta');
            $date = date('Y-m-d H:i:s');
            $eksekutor = $_SESSION['nip'];
            $stmt = $conn->prepare("INSERT INTO ticket 
                (request_id, nip, fullname, permintaan, problem_location, problem_device, waktu_pengerjaan, kategori, sqmpest, status_pekerjaan, eksekutor, `status`, `date`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssssssssss",
                $request_id,
                $nip,
                $fullname,
                $permintaan,
                $problem_location,
                $problem_device,
                $waktu_pengerjaan,
                $kategori,
                $sqmpest,
                $status_pekerjaan,
                $eksekutor,
                $status,
                $date
            );
        }

        if ($stmt->execute()) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Gagal menyimpan tiket.';
        }
        $stmt->close();
    } else {
        $error = 'Harap isi semua field yang wajib!';
    }
}

$conn->close();
?>
<!DOCTYPE html>

<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $edit_mode ? 'Edit' : 'Buat' ?> Tiket - E-Ticket System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="ticket_form.css">
<!-- <style>
</style> -->

</head>
<body>

<?php include 'navbar.php'; ?>
<div id="app-content">
<div class="container-fluid" style="margin-bottom:90px;">
<div class="row justify-content-center">
<div class="col-md-8">

<div class="card">

<div class="card-header bg-light text-dark">
    <h5 class="mb-0"><i class="bi bi-ticket"></i> <?= $edit_mode ? 'Edit Tiket' : 'Buat Tiket Baru' ?></h5>
</div>

<div class="card-body">

<?php if ($error): ?>

<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<!-- PERMINTAAN -->

<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
        <textarea class="form-control" name="permintaan" rows="3" placeholder="Permintaan / Deskripsi" required><?= htmlspecialchars($ticket['permintaan'] ?? '') ?></textarea>
    </div>
</div>

<!-- LOKASI & DEVICE -->

<div class="row g-2 mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
            <input type="text" name="problem_location" class="form-control" placeholder="Lokasi" required value="<?= htmlspecialchars($ticket['problem_location'] ?? '') ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-pc-display"></i></span>
            <input type="text" name="problem_device" class="form-control" placeholder="Device / Perangkat" required value="<?= htmlspecialchars($ticket['problem_device'] ?? '') ?>">
        </div>
    </div>
</div>

<!-- WAKTU -->

<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-clock"></i></span>
       <input type="date" name="waktu_pengerjaan" class="form-control"
value="<?= !empty($ticket['waktu_pengerjaan']) 
    ? date('Y-m-d', strtotime($ticket['waktu_pengerjaan'])) 
    : '' ?>">
    </div>
</div>

<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
        <select name="kategori" id="kategori" class="form-select">
            <option value="" hidden>Kategori</option>
            <?php 
            $categories = ['Hardware', 'Software', 'Network', 'Data', 'Administration', 'Documentation', 'Budgeting', 'Purchase Requestion', 'Inventory Control'];
            foreach ($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= (isset($ticket['kategori']) && $ticket['kategori'] == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- SQMPEST & STATUS -->

<div class="row g-2 mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
            <select name="sqmpest" class="form-select">
                <option value="">SQMPEST</option>
                <?php foreach(['safety','quality','productivity','energy','standardization','training','other'] as $k): ?>
                <option value="<?= $k ?>" <?= ($ticket['sqmpest'] ?? '')==$k?'selected':'' ?>><?= ucfirst($k) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-activity"></i></span>
            <select name="status_pekerjaan" class="form-select">
                <option value="" hidden>Status</option>
                <option value="pending" <?= ($ticket['status_pekerjaan'] ?? '')=='pending'?'selected':'' ?>>Pending</option>
                <option value="rejected" <?= ($ticket['status_pekerjaan'] ?? '')=='rejected'?'selected':'' ?>>Rejected</option>
                <option value="on progress" <?= ($ticket['status_pekerjaan'] ?? '')=='on progress'?'selected':'' ?>>On Progress</option>
                <option value="complete" <?= ($ticket['status_pekerjaan'] ?? '')=='complete'?'selected':'' ?>>Complete</option>
            </select>
        </div>
    </div>
</div>

<!-- EKSEKUTOR -->

<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['fullname']) ?>" readonly>
    </div>
</div>

<!-- ACTION BAR -->

<div class="action-bar">
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> <span>Kembali</span></a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <span><?= $edit_mode ? 'Update' : 'Simpan' ?></span></button>
</div>

</form>
</div>
</div>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
