<?php
require_once 'config.php';
requireLogin();

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$conn = getConnection();
$edit_mode = false;
$user = null;
$error = '';
$success = '';

// Edit mode
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user) $edit_mode = true;
    $stmt->close();
}

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = $_POST['nip'] ?? '';
    $name = $_POST['name'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $no_wa = $_POST['no_wa'] ?? '';
    $account = $_POST['account'] ?? '';
    $admin = $_POST['admin'] ?? '0'; // bisa 0=user, 1=admin, 2=eksekutor
    $status = isset($_POST['status']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($nip && $name && $fullname && $account) {
        if ($edit_mode && $user) {
            // Update
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET nip=?, name=?, fullname=?, no_wa=?, account=?, admin=?, status=?, pass=? WHERE id=?");
                $stmt->bind_param("sssssiisi", $nip, $name, $fullname, $no_wa, $account, $admin, $status, $hashed, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET nip=?, name=?, fullname=?, no_wa=?, account=?, admin=?, status=? WHERE id=?");
                $stmt->bind_param("sssssisi", $nip, $name, $fullname, $no_wa, $account, $admin, $status, $id);
            }

            if ($stmt->execute()) {
                header('Location: users.php');
                exit();
            } else {
                $error = 'Gagal mengupdate user!';
            }
            $stmt->close();
        } else {
            // Insert baru
            if (!$password) {
                $error = 'Password wajib diisi untuk user baru!';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (nip, name, fullname, no_wa, account, admin, status, pass) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssiis", $nip, $name, $fullname, $no_wa, $account, $admin, $status, $hashed);

                if ($stmt->execute()) {
                    header('Location: users.php');
                    exit();
                } else {
                    $error = 'Gagal membuat user! NIP mungkin sudah digunakan.';
                }
                $stmt->close();
            }
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
<title><?= $edit_mode ? 'Edit' : 'Tambah'; ?> User - R-Ticket</title>

<link rel="shortcut icon" href="logo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="user_form.css">
<!-- <style>
</style> -->
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid my-4">
<div class="row justify-content-center">
<div class="col-lg-6 col-md-8">

<div class="form-card">

    <div class="form-header">
        <h5>
            <i class="bi bi-person-plus me-1"></i>
            <?= $edit_mode ? 'Edit User' : 'Tambah User Baru'; ?>
        </h5>
    </div>

    <div class="p-4">

        <?php if ($error): ?>
            <div class="alert alert-danger small"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NIP *</label>
                    <input type="text" name="nip" class="form-control"
                           value="<?= $user ? htmlspecialchars($user['nip']) : '' ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username *</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= $user ? htmlspecialchars($user['name'] ?? '') : '' ?>" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="fullname" class="form-control"
                       value="<?= $user ? htmlspecialchars($user['fullname']) : '' ?>" required>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">No. WhatsApp</label>
                    <input type="text" name="no_wa" class="form-control"
                           value="<?= $user ? htmlspecialchars($user['no_wa']) : '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account *</label>
                    <input type="text" name="account" class="form-control"
                           value="<?= $user ? htmlspecialchars($user['account']) : '' ?>" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Role / Hak Akses</label>
                <select name="admin" class="form-select">
                    <option value="0" <?= ($user && $user['admin']==0)?'selected':'' ?>>User</option>
                    <option value="1" <?= ($user && $user['admin']==1)?'selected':'' ?>>Admin</option>
                    <option value="2" <?= ($user && $user['admin']==2)?'selected':'' ?>>Eksekutor</option>
                </select>
            </div>

            <div class="mt-3">
                <label class="form-label">
                    Password <?= $edit_mode ? '(Opsional)' : '*' ?>
                </label>

                <div class="password-wrap">
                    <input type="password" name="password" id="password"
                           class="form-control" <?= !$edit_mode ? 'required' : '' ?>>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" name="status"
                       <?= !$user || $user['status'] ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold">Active</label>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="users.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-main">
                    <i class="bi bi-save"></i>
                    <?= $edit_mode ? 'Update' : 'Simpan'; ?>
                </button>
            </div>

        </form>
    </div>
</div>

</div>
</div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.querySelector('.toggle-password i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
