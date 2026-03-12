# E-Ticket System - IT Helpdesk

Aplikasi E-Ticket System berbasis PHP Native dan MySQL untuk manajemen tiket IT helpdesk, terinspirasi dari Airtable.

## 🚀 Fitur Utama

### User (Non-Admin)
- ✅ Login dengan NIP dan Password
- ✅ Membuat tiket permintaan baru
- ✅ Melihat daftar tiket milik sendiri
- ✅ Melihat detail tiket
- ✅ Edit tiket yang masih berstatus "waiting"
- ✅ Filter dan search tiket
- ✅ Update profile dan ganti password
- ✅ Dashboard dengan statistik tiket

### Admin
- ✅ Semua fitur user
- ✅ Melihat semua tiket dari semua user
- ✅ Update status tiket (waiting, accepted, close)
- ✅ Update status pekerjaan (on progress, complete, pending)
- ✅ Mengisi eksekutor, SQMPEST, dan waktu pengerjaan
- ✅ Hapus tiket
- ✅ Manajemen user (tambah, edit, hapus)
- ✅ Aktifkan/nonaktifkan user

## 📋 Requirements

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern

## 🔧 Instalasi

### 1. Setup Database

```sql
-- Buat database
CREATE DATABASE public;

-- Gunakan database
USE public;

-- Tabel users sudah ada (sesuai requirement Anda)
-- Tabel ticket sudah ada (sesuai requirement Anda)

-- Import file database.sql untuk data sample
SOURCE database.sql;
```

### 2. Konfigurasi

Edit file `config.php` sesuai dengan setting database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'public');
```

### 3. Upload Files

Upload semua file ke direktori web server Anda (htdocs/www).

### 4. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/eticket/login.php
```

## 👤 Default Users

### Admin Account
- **NIP**: admin
- **Password**: admin123

### User Account 1
- **NIP**: user001
- **Password**: user123

### User Account 2
- **NIP**: user002
- **Password**: user123

## 📁 Struktur File

```
eticket/
├── config.php              # Konfigurasi database & helper functions
├── login.php               # Halaman login
├── logout.php              # Proses logout
├── index.php               # Dashboard & list tiket
├── navbar.php              # Navigation bar component
├── ticket_form.php         # Form create/edit tiket
├── ticket_detail.php       # Detail tiket
├── ticket_delete.php       # Hapus tiket
├── users.php               # List users (admin only)
├── user_form.php           # Form create/edit user (admin only)
├── user_delete.php         # Hapus user (admin only)
├── profile.php             # Profile user & ganti password
└── database.sql            # SQL initialization & sample data
```

## 🎨 Fitur Tampilan

- **Responsive Design** - Tampil optimal di desktop, tablet, dan mobile
- **Bootstrap 5** - UI modern dan clean
- **Bootstrap Icons** - Icon set lengkap
- **Color-coded Status** - Status tiket dengan badge berwarna
- **Statistics Cards** - Dashboard dengan kartu statistik
- **Search & Filter** - Pencarian dan filter multiple
- **Form Validation** - Validasi input di client & server side

## 🔐 Keamanan

- Password di-hash menggunakan `password_hash()` PHP
- Prepared statements untuk mencegah SQL injection
- Session management untuk autentikasi
- Role-based access control (Admin/User)
- Input validation & sanitization

## 📊 Status Tiket

### Status Tiket
- **Waiting** (Menunggu) - Tiket baru menunggu review admin
- **Accepted** (Diterima) - Tiket diterima dan sedang diproses
- **Close** (Selesai) - Tiket sudah selesai dikerjakan

### Status Pekerjaan
- **On Progress** - Sedang dalam pengerjaan
- **Complete** - Pekerjaan selesai
- **Pending** - Pekerjaan tertunda

## 🛠️ Troubleshooting

### Error Koneksi Database
```
Solusi: Pastikan MySQL sudah running dan kredensial di config.php benar
```

### Session Error
```
Solusi: Pastikan PHP memiliki permission write ke session directory
```

### Password Tidak Match
```
Solusi: Gunakan password default atau reset via database:
UPDATE users SET pass = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE nip = 'admin';
Password akan menjadi: admin123
```

## 🔄 Update & Development

### Menambah Field Baru
1. Alter tabel di database
2. Update form di `ticket_form.php`
3. Update query INSERT/UPDATE
4. Update tampilan di `ticket_detail.php` dan `index.php`

### Menambah Role Baru
1. Tambah kolom role di tabel users
2. Update fungsi authorization di `config.php`
3. Update kondisi di setiap halaman

## 📝 License

Free to use for personal and commercial projects.

## 👨‍💻 Developer

Aplikasi E-Ticket System - IT Helpdesk Management

## 📧 Support

Jika ada pertanyaan atau menemukan bug, silakan hubungi developer atau buat issue di repository.

---

**Happy Coding! 🚀**