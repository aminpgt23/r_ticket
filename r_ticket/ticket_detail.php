<?php
// ticket_detail.php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM ticket WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    header('Location: index.php');
    exit();
}
$nameEx = "";
$stmt2 = $conn->prepare("SELECT fullname FROM users WHERE nip = ?");
$stmt2->bind_param("s", $ticket['eksekutor']);
$stmt2->execute();
$nameEx = $stmt2->get_result()->fetch_assoc();


if (
    !isAdmin()
    && $ticket['nip'] !== $_SESSION['nip']
    && $ticket['eksekutor'] !== $_SESSION['nip']
) {
    header('Location: index.php');
    exit();
}


$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Tiket - R-Ticket</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="ticket_detail.css">
<!-- <style>
</style> -->
</head>

<body>

<div class="wave top"></div>
<div class="wave bottom"></div>

<?php include 'navbar.php'; ?>

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-md-10">

<div class="card detail-card">

<div class="card-header bg-light text-dark d-flex justify-content-between">
    <h5><i class="bi bi-file-text"></i> <?= htmlspecialchars($ticket['request_id']) ?></h5>
    <div>
        <?= getStatusBadge($ticket['status']) ?>
        <?= getStatusPekerjaanBadge($ticket['status_pekerjaan']) ?>
    </div>
</div>

<div class="card-body">

<div class="row">
<div class="col-md-6">
    <div class="info-value i-id"><?= htmlspecialchars($ticket['request_id']) ?></div>
    <div class="info-value i-nip"><?= htmlspecialchars($ticket['nip']) ?></div>
    <div class="info-value i-user"><?= htmlspecialchars($ticket['fullname']) ?></div>
    <div class="info-value i-date"><?= formatDate($ticket['date']) ?></div>
    <div class="info-value "><i class="bi bi-box-seam"></i><?= htmlspecialchars($ticket['kategori'] ?? "") ?></div>
</div>

<div class="col-md-6">
    <div class="info-value i-loc"><?= htmlspecialchars($ticket['problem_location']) ?></div>
    <div class="info-value i-dev"><?= htmlspecialchars($ticket['problem_device']) ?></div>
    <div class="info-value i-time"><?= $ticket['waktu_permintaan'] ?: '-' ?></div>
</div>
</div>

<hr>

<div class="info-value desc i-desc">
<?= nl2br(htmlspecialchars($ticket['permintaan'])) ?>
</div>

<?php if (isAdmin() || $ticket['eksekutor']): ?>
<hr>
<h6 class="text-primary">Pengerjaan</h6>
<div class="row">
    <div class="col-md-4">
        <div class="info-value i-work"><?= $nameEx ? htmlspecialchars($nameEx['fullname']) : '-' ?></div>
    </div>
    <div class="col-md-4">
        <div class="info-value i-work"><?= $ticket['sqmpest'] ?: '-' ?></div>
    </div>
    <div class="col-md-4">
        <div class="info-value i-date"><?= formatDate($ticket['waktu_pengerjaan']) ?></div>
    </div>
</div>
<?php endif; ?>

<div class="action-bar mt-3">
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> <span>Kembali</span></a>

    <?php if (isAdmin() || $ticket['status']=='waiting' || $ticket['eksekutor'] == $_SESSION['nip']): ?>
    <a href="ticket_form.php?id=<?= $ticket['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> <span>Edit</span></a>
    <?php endif; ?>

    <?php if (isAdmin()): ?>
    <a href="ticket_delete.php?id=<?= $ticket['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus tiket?')">
        <i class="bi bi-trash"></i> <span>Hapus</span>
    </a>
    <?php endif; ?>
</div>

</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
