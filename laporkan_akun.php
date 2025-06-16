<?php
// LOKASI: /myitstutor/laporkan_akun.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>Anda harus login untuk membuat laporan.</div>";
    require_once 'includes/footer.php';
    exit();
}

// Ambil ID pengguna yang akan dilaporkan dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Pengguna yang akan dilaporkan tidak valid.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_terlapor = $_GET['id'];

// Ambil nama pengguna yang dilaporkan untuk ditampilkan
$sql_user = "SELECT Nama_mahasiswa FROM Mahasiswa WHERE NRP_Mahasiswa = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $id_terlapor);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

if (!$user_data) {
    echo "<div class='alert alert-danger'>Pengguna yang akan dilaporkan tidak ditemukan.</div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<h1 class="mt-4">Laporkan Pengguna</h1>
<p class="lead">Anda akan melaporkan pengguna: <strong><?php echo htmlspecialchars($user_data['Nama_mahasiswa']); ?></strong> (NRP: <?php echo htmlspecialchars($id_terlapor); ?>)</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-body">
                <form action="proses_laporkan.php" method="POST">
                    <input type="hidden" name="id_terlapor" value="<?php echo htmlspecialchars($id_terlapor); ?>">
                    
                    <div class="mb-3">
                        <label for="tipe_pelanggaran" class="form-label">Tipe Pelanggaran</label>
                        <select class="form-select" id="tipe_pelanggaran" name="tipe_pelanggaran" required>
                            <option value="" disabled selected>-- Pilih Tipe Pelanggaran --</option>
                            <option value="Spam">Spam</option>
                            <option value="Konten Tidak Pantas">Konten Tidak Pantas</option>
                            <option value="Penipuan">Penipuan</option>
                            <option value="No Show">Tutor Tidak Hadir (No Show)</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi Lengkap</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" placeholder="Jelaskan secara rinci pelanggaran yang terjadi..." required></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger btn-lg">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
