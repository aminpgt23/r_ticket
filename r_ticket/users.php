<?php
// users.php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - E-Ticket</title>
<link rel="shortcut icon" href="logo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="users.css">     
<!-- <style>
</style> -->
</head>

<body>

<?php include 'navbar.php'; ?>
<div id="app-content">
<div class="container-fluid" style="margin-bottom:90px; ">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-people me-1"></i>User Management
            </h6>
            <a href="user_form.php" class="btn btn-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-plus"></i> Tambah
            </a>
        </div>

        <div class="card-body">

            <!-- ===== DESKTOP TABLE ===== -->
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>NIP</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>No. WA</th>
                            <th>Account</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nip']) ?></td>
                            <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['no_wa']) ?></td>
                            <td><?= htmlspecialchars($user['account']) ?></td>
                            <td>
                                <?php
                                echo $user['admin']==1 ? '<span class="badge bg-danger">Admin</span>' :
                                     ($user['admin']==2 ? '<span class="badge bg-warning text-dark">Eksekutor</span>' :
                                     '<span class="badge bg-info">User</span>');
                                ?>
                            </td>
                            <td>
                                <?= $user['status']
                                    ? '<span class="badge bg-success">Active</span>'
                                    : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td class="text-center">
                                <a href="user_form.php?id=<?= $user['id'] ?>" class="btn btn-icon btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="user_delete.php?id=<?= $user['id'] ?>"
                                   class="btn btn-icon btn-danger"
                                   onclick="return confirm('Yakin hapus user ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ===== MOBILE LIST ===== -->
            <div class="mobile-list">
                <?php foreach ($users as $user): ?>
                <div class="user-card">

                    <div class="user-top">
                        <div>
                            <div class="user-name"><?= htmlspecialchars($user['fullname']) ?></div>
                            <div class="user-nip"><?= htmlspecialchars($user['nip']) ?></div>
                        </div>
                        <?= $user['status']
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>' ?>
                    </div>

                    <div class="user-meta">
                        <div><i class="bi bi-person"></i> <?= htmlspecialchars($user['account']) ?></div>
                        <div><i class="bi bi-person-plus-fill"></i> <?= htmlspecialchars($user['created_at']) ?></div>
                    </div>

                    <div class="mt-2">
                        <?php
                        echo $user['admin']==1 ? '<span class="badge bg-danger">Admin</span>' :
                             ($user['admin']==2 ? '<span class="badge bg-warning text-dark">Eksekutor</span>' :
                             '<span class="badge bg-info">User</span>');
                        ?>
                    </div>

                    <div class="user-actions">
                        <a href="user_form.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-icon">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="user_delete.php?id=<?= $user['id'] ?>"
                           class="btn btn-danger btn-icon"
                           onclick="return confirm('Yakin hapus user ini?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
