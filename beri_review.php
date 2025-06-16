<?php
// LOKASI: /myitstutor/beri_review.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya mahasiswa yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo "<div class='alert alert-danger'>Akses Ditolak.</div>";
    require_once 'includes/footer.php';
    exit();
}

// Ambil ID kelas dari URL dan pastikan tidak kosong
if (!isset($_GET['id_kelas']) || empty($_GET['id_kelas'])) {
    echo "<div class='alert alert-danger'>ID Kelas tidak valid.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_kelas = $_GET['id_kelas'];

// Ambil info kelas untuk ditampilkan
$sql_kelas = "SELECT Matkul, Nama_Tutor FROM Kelas JOIN Tutor ON Kelas.Tutor_NRP_Tutor = Tutor.NRP_Tutor WHERE ID_Kelas = ?";
$stmt_kelas = mysqli_prepare($conn, $sql_kelas);
mysqli_stmt_bind_param($stmt_kelas, "s", $id_kelas);
mysqli_stmt_execute($stmt_kelas);
$result_kelas = mysqli_stmt_get_result($stmt_kelas);
$kelas = mysqli_fetch_assoc($result_kelas);
mysqli_stmt_close($stmt_kelas);

if (!$kelas) {
    echo "<div class='alert alert-danger'>Kelas tidak ditemukan.</div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-light my-4">Beri Ulasan untuk Kelas</h3>
                <h5 class="text-center text-muted"><?php echo htmlspecialchars($kelas['Matkul']); ?> oleh Tutor <?php echo htmlspecialchars($kelas['Nama_Tutor']); ?></h5>
            </div>
            <div class="card-body">
                <form action="proses_beri_review.php" method="POST">
                    <input type="hidden" name="id_kelas" value="<?php echo htmlspecialchars($id_kelas); ?>">
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select class="form-select" name="rating" id="rating" required>
                            <option value="" disabled selected>-- Beri Bintang --</option>
                            <option value="5">⭐⭐⭐⭐⭐ (Luar Biasa)</option>
                            <option value="4">⭐⭐⭐⭐ (Bagus)</option>
                            <option value="3">⭐⭐⭐ (Cukup)</option>
                            <option value="2">⭐⭐ (Kurang)</option>
                            <option value="1">⭐ (Buruk)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review" class="form-label">Ulasan Anda</label>
                        <textarea class="form-control" name="review" id="review" rows="4" placeholder="Tuliskan pengalaman Anda mengikuti kelas ini..." required></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Kirim Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
