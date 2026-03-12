<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $password = $_POST['password'] ?? '';
    $no_wa = trim($_POST['no_wa'] ?? '');
    $account = trim($_POST['account'] ?? '');

    if ($nip && $name && $fullname && $password && $account) {
        $conn = getConnection();

        // Cek apakah NIP sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE nip = ?");
        $stmt->bind_param("s", $nip);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'NIP sudah terdaftar, silakan login.';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Admin default = 0 (bukan admin)
            $admin = 0;
            $status = 0; // status default = 0 (non-aktif)

            $stmt = $conn->prepare("
                INSERT INTO users (nip, `name`, pass, `admin`, fullname, no_wa, account, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssssi", $nip, $name, $hashedPassword, $admin, $fullname, $no_wa, $account, $status);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal menyimpan data. Silakan coba lagi.';
            }

            $stmt->close();
        }

        $conn->close();
    } else {
        $error = 'Harap isi semua field wajib!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - R-Ticket</title>
<link rel="shortcut icon" href="logo.jpg" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            /* min-height: 10vh; */
            display: flex;
            align-items: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card p-5">
                    <h2 class="text-center mb-4">Registrasi Akun</h2>
                    <p class="text-center text-muted mb-4">Silakan isi data di bawah ini untuk membuat akun baru</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" class="form-control" id="nip" name="nip" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_wa" class="form-label">No. WhatsApp</label>
                            <input type="text" class="form-control" id="no_wa" name="no_wa" placeholder="Opsional">
                        </div>
                        <div class="mb-3">
                            <label for="account" class="form-label">Account/Departemen</label>
                            <input type="text" class="form-control" id="account" name="account" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Daftar</button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center">
                        Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
