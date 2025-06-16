<?php
// LOKASI: /myitstutor/profil.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan user sudah login untuk melihat profil
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>Anda harus login untuk melihat halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

// Ambil ID profil yang ingin dilihat dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Pengguna tidak valid.</div>";
    require_once 'includes/footer.php';
    exit();
}
$profil_id = $_GET['id'];

// --- Ambil Data Dasar (dari tabel Mahasiswa) ---
$sql_user = "SELECT Nama_mahasiswa, Email FROM Mahasiswa WHERE NRP_Mahasiswa = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $profil_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if (mysqli_num_rows($result_user) == 0) {
    echo "<div class='alert alert-danger'>Pengguna tidak ditemukan.</div>";
    require_once 'includes/footer.php';
    exit();
}
$user_data = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

// --- Cek apakah pengguna ini juga seorang Tutor ---
$is_tutor = false;
$tutor_data = [];
$sql_tutor = "SELECT Spesialisasi FROM Tutor WHERE NRP_Tutor = ?";
$stmt_tutor = mysqli_prepare($conn, $sql_tutor);
mysqli_stmt_bind_param($stmt_tutor, "s", $profil_id);
mysqli_stmt_execute($stmt_tutor);
$result_tutor = mysqli_stmt_get_result($stmt_tutor);
if (mysqli_num_rows($result_tutor) == 1) {
    $is_tutor = true;
    $tutor_data = mysqli_fetch_assoc($result_tutor);
}
mysqli_stmt_close($stmt_tutor);
?>

<div class="row">
    <!-- Kolom Kiri: Foto dan Info Dasar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <!-- Placeholder untuk foto profil -->
                <img src="https://placehold.co/150/0d6efd/white?text=<?php echo strtoupper(substr($user_data['Nama_mahasiswa'], 0, 1)); ?>" class="rounded-circle mb-3" alt="Foto Profil">
                <h4 class="card-title"><?php echo htmlspecialchars($user_data['Nama_mahasiswa']); ?></h4>
                <p class="text-muted">NRP: <?php echo htmlspecialchars($profil_id); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($user_data['Email']); ?></p>
                
                <?php if ($profil_id == $_SESSION['user_id']): ?>
                    <!-- Tombol hanya muncul di profil sendiri -->
                    <a href="edit_profil.php" class="btn btn-secondary btn-sm">Edit Profil</a>
                <?php else: ?>
                    <!-- PERUBAHAN: Tombol untuk melaporkan pengguna lain dikembalikan -->
                    <a href="laporkan_akun.php?id=<?php echo htmlspecialchars($profil_id); ?>" class="btn btn-outline-danger btn-sm mt-2">Laporkan Pengguna Ini</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Info Tambahan (jika seorang tutor) -->
    <div class="col-md-8">
        <?php if ($is_tutor): ?>
            <div class="card">
                <div class="card-header">
                    <h4>Profil Tutor</h4>
                </div>
                <div class="card-body">
                    <h5>Spesialisasi:</h5>
                    <p><?php echo htmlspecialchars($tutor_data['Spesialisasi']); ?></p>
                    <hr>
                    <h5>Kelas yang Diajar:</h5>
                    <ul class="list-group list-group-flush">
                        <?php
                        $sql_kelas_tutor = "SELECT Matkul, Status_kelas FROM Kelas WHERE Tutor_NRP_Tutor = ? ORDER BY Tanggal_Kelas DESC LIMIT 5";
                        $stmt_kelas_tutor = mysqli_prepare($conn, $sql_kelas_tutor);
                        mysqli_stmt_bind_param($stmt_kelas_tutor, "s", $profil_id);
                        mysqli_stmt_execute($stmt_kelas_tutor);
                        $result_kelas_tutor = mysqli_stmt_get_result($stmt_kelas_tutor);
                        if(mysqli_num_rows($result_kelas_tutor) > 0) {
                            while($kelas = mysqli_fetch_assoc($result_kelas_tutor)) {
                                echo '<li class="list-group-item">' . htmlspecialchars($kelas['Matkul']) . ' <span class="badge bg-info float-end">' . htmlspecialchars($kelas['Status_kelas']) . '</span></li>';
                            }
                        } else {
                            echo '<li class="list-group-item">Tutor ini belum membuat kelas.</li>';
                        }
                        mysqli_stmt_close($stmt_kelas_tutor);
                        ?>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                 <div class="card-body">
                    <p class="text-muted text-center p-4">Pengguna ini adalah seorang mahasiswa.</p>
                 </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
