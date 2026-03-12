<?php
// profile.php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$error = '';
$success = '';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $no_wa = $_POST['no_wa'] ?? '';
    $account = $_POST['account'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($fullname && $account) {
        // Check if changing password
        if ($new_password) {
            if (!$current_password) {
                $error = 'Masukkan password lama untuk mengubah password!';
            } elseif (!password_verify($current_password, $user['pass'])) {
                $error = 'Password lama salah!';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru tidak cocok!';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password minimal 6 karakter!';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET fullname=?, no_wa=?, account=?, pass=? WHERE id=?");
                $stmt->bind_param("ssssi", $fullname, $no_wa, $account, $hashed, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['fullname'] = $fullname;
                    $success = 'Profile dan password berhasil diupdate!';
                    
                    // Refresh user data
                    $stmt2 = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt2->bind_param("i", $user_id);
                    $stmt2->execute();
                    $user = $stmt2->get_result()->fetch_assoc();
                    $stmt2->close();
                } else {
                    $error = 'Gagal mengupdate profile!';
                }
                $stmt->close();
            }
        } else {
            // Update without password change
            $stmt = $conn->prepare("UPDATE users SET fullname=?, no_wa=?, account=? WHERE id=?");
            $stmt->bind_param("sssi", $fullname, $no_wa, $account, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['fullname'] = $fullname;
                $success = 'Profile berhasil diupdate!';
                
                // Refresh user data
                $stmt2 = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $user = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();
            } else {
                $error = 'Gagal mengupdate profile!';
            }
            $stmt->close();
        }
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
<title>Profile - R-Ticket</title>

<link rel="shortcut icon" href="logo.jpg" type="image/x-icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="profile.css">
<!-- <style>
</style> -->
</head>

<body>

<div class="mobile-wave-top"></div>
<div class="mobile-wave-bottom"></div>

<?php include 'navbar.php'; ?>

<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-md-8">

<div class="card profile-card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-person-circle fs-5"></i>
        <h5>Profile Saya</h5>
    </div>

    <div class="card-body">

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">NIP</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nip']) ?>" disabled>
                </div>
                <div class="col-6">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="fullname" class="form-control" required
                       value="<?= htmlspecialchars($user['fullname']) ?>">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="no_wa" class="form-control"
                           value="<?= htmlspecialchars($user['no_wa']) ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Account *</label>
                    <input type="text" name="account" class="form-control" required
                           value="<?= htmlspecialchars($user['account']) ?>">
                </div>
            </div>

            <hr>

            <div class="section-title">
                <i class="bi bi-shield-lock"></i> Ubah Password
            </div>

            <div class="mb-2">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-control">
            </div>

            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Konfirmasi</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
            </div>

            <div class="action-bar">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> <span>Kembali</span>
                </a>
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> <span>Update</span>
                </button>
            </div>

        </form>
    </div>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
