<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();

// Ambil filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$status_pekerjaan_filter = $_GET['status_pekerjaan'] ?? '';
$eksekutor_filter = $_GET['eksekutor'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Build query dinamis
$where = [];
$params = [];
$types = '';


if ($from) {
    $where[] = "`date` >= ?";
    $params[] = $from . ' 00:00:00';
    $types .= 's';
}

if ($to) {
    $where[] = "`date` < ?";
    $params[] = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00';
    $types .= 's';
}


if ($search) {
    $where[] = "(request_id LIKE ? OR fullname LIKE ? OR permintaan LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= 'sss';
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($status_pekerjaan_filter) {
    $where[] = "status_pekerjaan = ?";
    $params[] = $status_pekerjaan_filter;
    $types .= 's';
}

if ($eksekutor_filter) {
    $where[] = "eksekutor = ?";
    $params[] = $eksekutor_filter;
    $types .= 's';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$query = "SELECT * FROM ticket $whereClause ORDER BY date DESC";
$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting,
    SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN status = 'close' THEN 1 ELSE 0 END) as closed
    FROM ticket" . (!isAdmin() ? " WHERE eksekutor = '{$_SESSION['nip']}'" : '');
$stats = $conn->query($stats_query)->fetch_assoc();

// Ambil daftar eksekutor (admin=2)
$eksekutors = $conn->query("SELECT nip, fullname FROM users WHERE admin = 2 AND status = 1 ORDER BY fullname ASC")->fetch_all(MYSQLI_ASSOC);

$conn->close();


// function normalizeWA($raw) {
//     if (!$raw) return null;

//     // ambil sebelum @
//     if (strpos($raw, '@c.us') !== false) {
//         $num = str_replace('@c.us', '', $raw);
//         return preg_replace('/\D+/', '', $num);
//     }

//     // selain c.us → BUKAN nomor
//     return null;
// }

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>R-Ticket</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#aecbf7ff">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="shortcut icon" href="logo.jpg" type="image/x-icon">
<link rel="stylesheet" href="index.css">
<link rel="stylesheet" href="ios-android-smooth.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

</head>
<body >
<div class="mobile-wave-top"></div>
<div class="mobile-wave-bottom"></div>

<?php include 'navbar.php'; ?>
<div id="app-content">
<div class="container-fluid " >
    <!-- Statistik -->
   
    <div class="row g-3 mb-4 text-center stats-modern">
        <div class="col-6 col-md-3">
            <div class="stat-modern" onclick="filterByStatus('')">
                <span class="stat-label">Total Tiket</span>
                <span class="stat-value"><?= $stats['total'] ?? 0 ?></span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-modern waiting" onclick="filterByStatus('waiting')">
                <span class="stat-label">Menunggu</span>
                <span class="stat-value"><?= $stats['waiting'] ?? 0 ?></span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-modern accepted" onclick="filterByStatus('accepted')">
                <span class="stat-label">Progress</span>
                <span class="stat-value"><?= $stats['accepted'] ?? 0 ?></span>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-modern closed" onclick="filterByStatus('close')">
                <span class="stat-label">Selesai</span>
                <span class="stat-value"><?= $stats['closed'] ?? 0 ?></span>
            </div>
        </div>
    </div>



    <div class="card filter-card mb-4">
    <div class="card-body">

        <div class="filter-modern">

            <!-- SEARCH -->
            <form method="GET" class="filter-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Cari tiket..."
                       value="<?= htmlspecialchars($search) ?>">
            </form>

            <!-- FILTER GROUP -->
<div class="filter-inline">
    <input class="filter-select" type="date" id="fromDate" value="<?= $_GET['from'] ?? '' ?>">
    <input class="filter-select" type="date" id="toDate" value="<?= $_GET['to'] ?? '' ?>">

    <select class="filter-select" onchange="window.location.href='?eksekutor='+this.value">
        <option value="">Eksekutor</option>
        <?php foreach ($eksekutors as $e): ?>
            <option value="<?= $e['nip'] ?>" <?= $eksekutor_filter===$e['nip']?'selected':'' ?>>
                <?= htmlspecialchars($e['fullname']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- RESET -->
    <button class="filter-reset" onclick="window.location.href='<?= strtok($_SERVER['REQUEST_URI'], '?') ?>'">
        Reset
    </button>
</div>


            <!-- ACTION -->
            <div class="filter-actions">
                <a href="ticket_export.php" class="btn btn-outline-secondary export-btn">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>

        </div>

    </div>
</div>


    <!-- Tabel Tiket -->
<div class="card-body">
    <div class="table-responsive">
    <!-- TABEL -->
    <div class="card table-card2" >
        <div class="table-container">
            <table class="table align-middle table-hover table-sm mb-0" >
                <thead class="table-light">
                    <tr>
                        <th>Request ID</th>
                        <th>Tanggal</th>
                        <th>Pemohon</th>
                        <th>Deskripsi</th>
                        <th>Lokasi</th>
                        <th>Device</th>
                        <th>Assignee</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>SQMPest</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="11" class="text-center text-muted">Tidak ada data tiket</td></tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            // Ambil nama eksekutor (fullname)
                            $eksekutor_name = '-';
                            if (!empty($t['eksekutor'])) {
                                foreach ($eksekutors as $e) {
                                    if ($e['nip'] === $t['eksekutor']) {
                                        $eksekutor_name = htmlspecialchars($e['fullname']);
                                        break;
                                    }
                                }
                            }

                            // Badge Status Utama
                            $status_badge = '';
                            switch ($t['status']) {
                                case 'waiting':
                                    $status_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Waiting</span>';
                                    break;
                                case 'accepted':
                                    $status_badge = '<span class="badge bg-info text-dark"><i class="bi bi-person-check"></i> Accepted</span>';
                                    break;
                                case 'close':
                                    $status_badge = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Close</span>';
                                    break;
                                default:
                                    $status_badge = '<span class="badge bg-secondary">-</span>';
                                    break;
                            }

                            // Badge Progress
                            $progress_badge = '';
                            switch (strtolower($t['status_pekerjaan'] ?? '')) {
                                case 'on progress':
                                    $progress_badge = '<span class="badge bg-primary"><i class="bi bi-tools"></i> On Progress</span>';
                                    break;
                                case 'complete':
                                    $progress_badge = '<span class="badge bg-success"><i class="bi bi-check2-circle"></i> Completed</span>';
                                    break;
                                case 'pending':
                                    $progress_badge = '<span class="badge bg-danger"><i class="bi bi-pause-circle"></i> Pending</span>';
                                    break;
                                case 'rejected':
                                    $progress_badge = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
                                    break;                                        
                                default:
                                    $progress_badge = '<span class="badge bg-secondary">-</span>';
                                    break;
                            }
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['request_id']) ?></strong></td>
                                <td><?= formatDate($t['date']) ?></td>
                                <td><?= htmlspecialchars($t['fullname']) ?></td>
                                <td><?= htmlspecialchars(substr($t['permintaan'],0,40)) ?></td>
                                <td><?= htmlspecialchars($t['problem_location']) ?></td>
                                <td><?= htmlspecialchars($t['problem_device']) ?></td>
                                <td><?= $eksekutor_name ?></td>
                                <td><?= $status_badge ?></td>
                                <td><?= $progress_badge ?></td>
                                <td>
                                    <?php if (isset($_SESSION['nip']) && $t['eksekutor'] === $_SESSION['nip']): ?>
                                        <form method="POST" action="ticket_update_sqmpest.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <select name="sqmpest" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">- Pilih -</option>
                                                <?php 
                                                $options = ['safety','quality','productivity','energy','standardization','training','other'];
                                                foreach ($options as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= ($t['sqmpest']??'')===$opt?'selected':'' ?>>
                                                        <?= ucfirst($opt) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <?= htmlspecialchars($t['sqmpest'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap position-relative">
                                    <?php if (isAdmin() || $_SESSION['nip'] === $t['eksekutor']): ?>
                                        <a href="ticket_detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php endif; ?>


                                    <?php if (isAdmin()): ?>
                                        <div class="btn-group">
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateModal<?= $t['id'] ?>"
                                            title="Action Tiket"
                                        >
                                            <i class="bi bi-gear-fill"></i>
                                        </button>
                                        </div>
                                    <?php endif; ?>
                                </td>

                            </tr>

                       

                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
        </div>
    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="mobile-cards " style="margin-bottom:70px;">
        <?php foreach ($tickets as $t): ?>
<?php
                            // Ambil nama eksekutor (fullname)
                            $eksekutor_name = '-';
                            if (!empty($t['eksekutor'])) {
                                foreach ($eksekutors as $e) {
                                    if ($e['nip'] === $t['eksekutor']) {
                                        $eksekutor_name = htmlspecialchars($e['fullname']);
                                        break;
                                    }
                                }
                            }

                            // Badge Status Utama
                            $status_badge = '';
                            switch ($t['status']) {
                                case 'waiting':
                                    $status_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Waiting</span>';
                                    break;
                                case 'accepted':
                                    $status_badge = '<span class="badge bg-info text-dark"><i class="bi bi-person-check"></i> Accepted</span>';
                                    break;
                                case 'close':
                                    $status_badge = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Close</span>';
                                    break;
                                default:
                                    $status_badge = '<span class="badge bg-secondary">-</span>';
                                    break;
                            }

                            // Badge Progress
                            $progress_badge = '';
                            switch (strtolower($t['status_pekerjaan'] ?? '')) {
                                case 'on progress':
                                    $progress_badge = '<span class="badge bg-primary"><i class="bi bi-tools"></i> On Progress</span>';
                                    break;
                                case 'complete':
                                    $progress_badge = '<span class="badge bg-success"><i class="bi bi-check2-circle"></i> Completed</span>';
                                    break;
                                case 'pending':
                                    $progress_badge = '<span class="badge bg-danger"><i class="bi bi-pause-circle"></i> Pending</span>';
                                    break;
                                case 'rejected':
                                    $progress_badge = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
                                    break;                               
                                default:
                                    $progress_badge = '<span class="badge bg-secondary">-</span>';
                                    break;
                            }
                            ?>
            
            <div class="mobile-card">
                <div class="mobile-title">
                    <?= htmlspecialchars($t['request_id']) ?>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Tanggal</div>
                    <div class="mobile-value"><?= formatDate($t['date']) ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Pemohon</div>
                    <div class="mobile-value"><?= htmlspecialchars($t['fullname']) ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Deskripsi</div>
                    <div class="mobile-value"><?= htmlspecialchars(substr($t['permintaan'],0,50)) ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Lokasi</div>
                    <div class="mobile-value"><?= htmlspecialchars($t['problem_location']) ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Device</div>
                    <div class="mobile-value"><?= htmlspecialchars($t['problem_device']) ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Assignee</div>
                    <div class="mobile-value"><?= $eksekutor_name ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Status</div>
                    <div class="mobile-value"><?= $status_badge ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">Progress</div>
                    <div class="mobile-value"><?= $progress_badge ?></div>
                </div>

                <div class="mobile-row">
                    <div class="mobile-label">SQMPest</div>
                    <div class="mobile-value">
                                    <?php if (isset($_SESSION['nip']) && $t['eksekutor'] === $_SESSION['nip']): ?>
                                        <form method="POST" action="ticket_update_sqmpest.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <select name="sqmpest" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="">- Pilih -</option>
                                                <?php 
                                                $options = ['safety','quality','productivity','energy','standardization','training','other'];
                                                foreach ($options as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= ($t['sqmpest']??'')===$opt?'selected':'' ?>>
                                                        <?= ucfirst($opt) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <?= htmlspecialchars($t['sqmpest'] ?? '-') ?>
                                    <?php endif; ?>       
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="mobile-actions">
                                    <?php if (isAdmin() || $_SESSION['nip'] === $t['eksekutor'] || $_SESSION['nip'] === $t['nip']  ): ?>
                                        <a href="ticket_detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <button 
                            type="button"
                            class="btn btn-secondary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#updateModal<?= $t['id'] ?>"
                        >
                            <i class="bi bi-gear-fill"></i> Action
                        </button>

                        <?php
                        // $wa = normalizeWA($t['no_wa_user'] ?? '');
                        ?>

                        <?php if ($t['no_wa_user']): ?>
                        <button
                            type="button"
                            class="btn btn-success btn-sm"
                            onclick="window.open('https://wa.me/<?= $t['no_wa_user'] ?>', '_blank')"
                        >
                            <i class="bi bi-whatsapp"></i> Chat User
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn-success btn-sm" style="font-size:10px;" disabled>
                            <i class="bi bi-whatsapp"></i> Tidak tersedia
                        </button>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
</div>
                    <?php foreach ($tickets as $t): ?>
                     <!-- MODAL UPDATE TIKET -->
                        <div class="modal fade" id="updateModal<?= $t['id'] ?>" tabindex="-1" aria-labelledby="updateModalLabel<?= $t['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-ios">

                            <div class="modal-content border-0 shadow-lg rounded-4">

                            <!-- HEADER -->
                            <div class="modal-header bg-gradient text-white" 
                                style="background: linear-gradient(135deg, #007bff, #6610f2);">
                                <h6 class="modal-title " id="updateModalLabel<?= $t['id'] ?>" style="color:black">
                                <i class="bi bi-gear-fill me-2" style="color:black"></i> Set Tiket #<?= htmlspecialchars($t['request_id']) ?>
                                <i class="bi bi-receipt-cutoff"></i> Desk :<?= htmlspecialchars($t['permintaan']) ?>
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                           <!-- FORM -->
                            <form action="ticket_update_combined.php" method="POST" autocomplete="off">
                                <div class="modal-body px-4 py-3">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($t['id']) ?>">
                                    <div class="row g-2">
                                        <!-- EKSEKUTOR -->
                                        <div class="col-md-6">
                                            <label for="eksekutor<?= $t['id'] ?>" class="form-label fw-semibold text-secondary">
                                                <i class="bi bi-person-badge me-1"></i> Eksekutor
                                            </label>
                                            <select class="form-select shadow-sm" name="eksekutor" id="eksekutor<?= $t['id'] ?>" required>
                                                <option value="">-- Pilih Eksekutor --</option>
                                                <?php foreach ($eksekutors as $e): ?>
                                                    <option value="<?= htmlspecialchars($e['nip']) ?>" 
                                                        <?= $t['eksekutor'] == $e['nip'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($e['fullname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- STATUS -->
                                        <div class="col-md-6">
                                            <label for="status<?= $t['id'] ?>" class="form-label fw-semibold text-secondary">
                                                <i class="bi bi-flag me-1"></i> Status Tiket
                                            </label>
                                            <select class="form-select shadow-sm" name="status" id="status<?= $t['id'] ?>" required>
                                                <option value="">-- Pilih Status --</option>
                                                <option value="waiting" <?= $t['status'] == 'waiting' ? 'selected' : '' ?>>🕓 Waiting</option>
                                                <option value="accepted" <?= $t['status'] == 'accepted' ? 'selected' : '' ?>>✅ Accepted</option>
                                                <option value="close" <?= $t['status'] == 'close' ? 'selected' : '' ?>>🔒 Close</option>
                                            </select>
                                        </div>

                                        <!-- WAKTU MULAI / DATETIME -->
                                        <div class="col-md-12 d-flex justify-content-between gap-1">
                                            <div class="mb-1 ">
                                                <label for="kategori<?= $t['id'] ?>" class="form-label fw-semibold text-secondary">
                                                    <i class="bi bi-box-seam me-1"></i> Kategori
                                                </label>
                                                <select class="form-select shadow-sm" name="kategori" id="kategori<?= $t['id'] ?>" required>
                                                    <option value="">Kategori</option>
                                                    <option value="Hardware" >Hardware</option>
                                                    <option value="Software" >Software</option>
                                                    <option value="Network" >Network</option>
                                                    <option value="Data" >Data</option>
                                                    <option value="Administration" >Administration</option>
                                                    <option value="Documentation" >Documentation</option>
                                                    <option value="Budgeting" >Budgeting</option>
                                                    <option value="Purchase Requestion" >Purchase Requestion</option>
                                                    <option value="Inventory Control" >Inventory Control</option>
                                                </select>
                                            </div>
                                            <div class="mb-1">
                                                <label for="waktu_mulai<?= $t['id'] ?>" class="form-label fw-semibold text-secondary">
                                                    <i class="bi bi-clock-history me-1"></i>Pengerjaan
                                                </label>
                                                <input type="datetime-local" class="form-control shadow-sm" 
                                                    name="waktu_mulai" id="waktu_mulai<?= $t['id'] ?>"
                                                    value="<?= !empty($t['waktu_mulai']) ? date('Y-m-d\TH:i', strtotime($t['waktu_mulai'])) : '' ?>"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <div class="bg-light rounded-3 p-3 small text-muted">
                                        <i class="bi bi-info-circle"></i> Pastikan eksekutor, status, dan waktu pengerjaan telah sesuai sebelum menyimpan.
                                    </div>
                                </div>

                                <!-- FOOTER -->
                                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle me-1"></i> Batal
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                            </div>
                        </div>
                        </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterByDate() {
    const params = new URLSearchParams(window.location.search);
    const from = document.getElementById('fromDate').value;
    const to   = document.getElementById('toDate').value;

    if (from) params.set('from', from); else params.delete('from');
    if (to)   params.set('to', to);   else params.delete('to');

    window.location.search = params.toString();
}


function formatDateTimeForMySQL(dt) { if (!dt) return ''; return dt.replace('T', ' ') + ':00'; } function filterByStatus(status) { const params = new URLSearchParams(window.location.search); const from = formatDateTimeForMySQL(document.getElementById('fromDate').value); const to = formatDateTimeForMySQL(document.getElementById('toDate').value); if (from) params.set('from', from); else params.delete('from'); if (to) params.set('to', to); else params.delete('to'); if (status) params.set('status', status); else params.delete('status'); window.location.search = params.toString(); }
document.getElementById('fromDate').addEventListener('change', () => filterByStatus('')); document.getElementById('toDate').addEventListener('change', () => filterByStatus(''));
document.getElementById('fromDate').addEventListener('change', filterByDate);
document.getElementById('toDate').addEventListener('change', filterByDate);
</script>
</body>
</html>
