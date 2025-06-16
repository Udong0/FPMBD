<?php
// LOKASI: /myitstutor/pencairan_dana.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya tutor yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Halaman ini hanya untuk Tutor.</div>";
    require_once 'includes/footer.php';
    exit();
}

$nrp_tutor = $_SESSION['user_id'];

// --- Menghitung Saldo Saat Ini ---
// 1. Hitung total pendapatan dari transaksi yang sudah lunas
$sql_pendapatan = "SELECT SUM(Total_Bayar + IFNULL(Tips, 0)) AS total_pendapatan FROM Transaksi WHERE Tutor_NRP_Tutor = ? AND Status_Pembayaran = 1";
$stmt_pendapatan = mysqli_prepare($conn, $sql_pendapatan);
mysqli_stmt_bind_param($stmt_pendapatan, "s", $nrp_tutor);
mysqli_stmt_execute($stmt_pendapatan);
$result_pendapatan = mysqli_stmt_get_result($stmt_pendapatan);
$data_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$total_pendapatan = $data_pendapatan['total_pendapatan'] ?? 0;
mysqli_stmt_close($stmt_pendapatan);

// 2. Hitung total dana yang sudah dicairkan atau sedang menunggu
$sql_pencairan = "SELECT SUM(Jumlah) AS total_dicairkan FROM Pencairan WHERE Tutor_NRP_Tutor = ? AND Status IN ('Disetujui', 'Menunggu')";
$stmt_pencairan = mysqli_prepare($conn, $sql_pencairan);
mysqli_stmt_bind_param($stmt_pencairan, "s", $nrp_tutor);
mysqli_stmt_execute($stmt_pencairan);
$result_pencairan = mysqli_stmt_get_result($stmt_pencairan);
$data_pencairan = mysqli_fetch_assoc($result_pencairan);
$total_dicairkan = $data_pencairan['total_dicairkan'] ?? 0;
mysqli_stmt_close($stmt_pencairan);

// 3. Saldo yang tersedia adalah selisihnya
$saldo_tersedia = $total_pendapatan - $total_dicairkan;
?>

<h1 class="mt-4">Pencairan Dana</h1>
<p class="lead">Lihat saldo Anda dan ajukan permintaan pencairan dana.</p>
<hr>

<div class="row">
    <div class="col-md-6">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Saldo Tersedia</div>
            <div class="card-body">
                <h2 class="card-title">Rp <?php echo number_format($saldo_tersedia, 0, ',', '.'); ?></h2>
                <p class="card-text">Ini adalah jumlah dana yang dapat Anda cairkan saat ini.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Ajukan Pencairan Baru</div>
            <div class="card-body">
                <form action="proses_pencairan.php" method="POST">
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah Pencairan (Rp)</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" max="<?php echo $saldo_tersedia; ?>" min="50000" placeholder="Minimal Rp 50.000" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" <?php echo ($saldo_tersedia < 50000) ? 'disabled' : ''; ?>>Ajukan Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<h3 class="mt-5">Riwayat Pencairan Dana Anda</h3>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Jumlah</th>
                <th>Tanggal Permintaan</th>
                <th>Status</th>
                <th>Tanggal Persetujuan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_riwayat = "SELECT * FROM Pencairan WHERE Tutor_NRP_Tutor = ? ORDER BY Tanggal_Permintaan DESC";
            $stmt_riwayat = mysqli_prepare($conn, $sql_riwayat);
            mysqli_stmt_bind_param($stmt_riwayat, "s", $nrp_tutor);
            mysqli_stmt_execute($stmt_riwayat);
            $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
            if (mysqli_num_rows($result_riwayat) > 0):
                while($row = mysqli_fetch_assoc($result_riwayat)):
            ?>
            <tr>
                <td><?php echo $row['ID_Pencairan']; ?></td>
                <td>Rp <?php echo number_format($row['Jumlah'], 0, ',', '.'); ?></td>
                <td><?php echo date("d M Y, H:i", strtotime($row['Tanggal_Permintaan'])); ?></td>
                <td><span class="badge bg-info text-dark"><?php echo $row['Status']; ?></span></td>
                <td><?php echo $row['Tanggal_Persetujuan'] ? date("d M Y, H:i", strtotime($row['Tanggal_Persetujuan'])) : '-'; ?></td>
            </tr>
            <?php
                endwhile;
            else:
            ?>
            <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat pencairan.</td></tr>
            <?php endif; mysqli_stmt_close($stmt_riwayat); ?>
        </tbody>
    </table>
</div>

<?php
require_once 'includes/footer.php';
?>
