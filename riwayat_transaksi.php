<?php
// LOKASI: /myitstutor/riwayat_transaksi.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Pastikan hanya mahasiswa yang bisa mengakses halaman ini
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Halaman ini hanya untuk Mahasiswa.</div>";
    require_once 'includes/footer.php';
    exit();
}

$nrp_mahasiswa = $_SESSION['user_id'];
?>

<h1 class="mt-4">Riwayat Transaksi</h1>
<p class="lead">Lihat semua riwayat pemesanan kelas Anda di sini.</p>
<hr>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID Transaksi</th>
                <th>Mata Kuliah</th>
                <th>Tutor</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // PERBAIKAN: Query mengambil data kelas (status & review) juga
            $sql = "SELECT t.ID_Transaksi, k.ID_Kelas, k.Matkul, tu.Nama_Tutor, t.Status_Pembayaran, k.Status_kelas, k.Review_kelas
                    FROM Transaksi t
                    JOIN Kelas k ON t.Kelas_ID_Kelas = k.ID_Kelas
                    JOIN Tutor tu ON t.Tutor_NRP_Tutor = tu.NRP_Tutor
                    WHERE t.Mahasiswa_NRP_mahasiswa = ?
                    ORDER BY t.Tanggal_Bayar DESC";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $nrp_mahasiswa);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0):
                while($transaksi = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($transaksi['ID_Transaksi']); ?></td>
                <td><?php echo htmlspecialchars($transaksi['Matkul']); ?></td>
                <td><?php echo htmlspecialchars($transaksi['Nama_Tutor']); ?></td>
                <td>
                    <?php if ($transaksi['Status_Pembayaran'] == 1): ?>
                        <span class="badge bg-success">Lunas</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    // Logika untuk menampilkan tombol yang sesuai
                    if ($transaksi['Status_Pembayaran'] == 0) {
                        // Jika belum lunas, tampilkan tombol bayar
                        echo '<form action="proses_pembayaran.php" method="POST" style="display:inline;"><input type="hidden" name="id_transaksi" value="' . $transaksi['ID_Transaksi'] . '"><button type="submit" class="btn btn-primary btn-sm">Bayar Sekarang</button></form>';
                    } elseif ($transaksi['Status_kelas'] == 'Selesai' && empty($transaksi['Review_kelas'])) {
                        // Jika sudah lunas, kelas selesai, dan belum ada review, tampilkan tombol review
                        echo '<a href="beri_review.php?id_kelas=' . $transaksi['ID_Kelas'] . '" class="btn btn-info btn-sm">Beri Review</a>';
                    } else {
                        // Jika sudah lunas dan sudah direview, atau kelas belum selesai
                        echo '<span class="text-muted">-</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="5" class="text-center text-muted">Anda belum memiliki riwayat transaksi.</td>
            </tr>
            <?php endif; mysqli_stmt_close($stmt); ?>
        </tbody>
    </table>
</div>


<?php
require_once 'includes/footer.php';
?>
