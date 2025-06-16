<?php
// LOKASI: /myitstutor/edit_profil.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user saat ini dari database untuk mengisi form
$sql_user = "SELECT Nama_mahasiswa, Email FROM Mahasiswa WHERE NRP_Mahasiswa = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

// Cek apakah user juga seorang tutor untuk mengambil data spesialisasi
$is_tutor = false;
$spesialisasi_tutor = '';
$sql_tutor_check = "SELECT Spesialisasi FROM Tutor WHERE NRP_Tutor = ?";
$stmt_tutor_check = mysqli_prepare($conn, $sql_tutor_check);
mysqli_stmt_bind_param($stmt_tutor_check, "s", $user_id);
mysqli_stmt_execute($stmt_tutor_check);
$result_tutor_check = mysqli_stmt_get_result($stmt_tutor_check);
if (mysqli_num_rows($result_tutor_check) > 0) {
    $is_tutor = true;
    $tutor_data = mysqli_fetch_assoc($result_tutor_check);
    $spesialisasi_tutor = $tutor_data['Spesialisasi'];
}
mysqli_stmt_close($stmt_tutor_check);

?>

<h1 class="mt-4">Edit Profil</h1>
<p class="lead">Perbarui informasi profil Anda di bawah ini.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-body">
                <form action="proses_edit_profil.php" method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user_data['Nama_mahasiswa']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" required>
                    </div>
                    
                    <?php if ($is_tutor): ?>
                    <div class="mb-3">
                        <label for="spesialisasi" class="form-label">Spesialisasi Tutor</label>
                        <textarea class="form-control" id="spesialisasi" name="spesialisasi" rows="3"><?php echo htmlspecialchars($spesialisasi_tutor); ?></textarea>
                        <div class="form-text">Tuliskan keahlian Anda, pisahkan dengan koma.</div>
                    </div>
                    <?php endif; ?>

                    <hr>
                    <p class="text-muted">Isi bagian di bawah ini hanya jika Anda ingin mengubah password.</p>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Biarkan kosong jika tidak ingin diubah">
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
