<?php
// view_transaksi.php
// Halaman untuk admin melihat riwayat semua transaksi pembayaran.

include('includes/header.php');
include('config/db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// --- Logika Ambil Data ---
$query_transaksi = "
    SELECT 
        tr.ID_Transaksi,
        tr.Tanggal_Bayar,
        tr.Total_Bayar,
        tr.Status_Pembayaran,
        tr.Metode_Bayar,
        m.Nama_mahasiswa,
        t.Nama_Tutor,
        k.Matkul
    FROM Transaksi tr
    JOIN Mahasiswa m ON tr.Mahasiswa_NRP_mahasiswa = m.NRP_Mahasiswa
    JOIN Tutor t ON tr.Tutor_NRP_Tutor = t.NRP_Tutor
    JOIN Kelas k ON tr.Kelas_ID_Kelas = k.ID_Kelas
    ORDER BY tr.Tanggal_Bayar DESC
";
$result = $conn->query($query_transaksi);
$transaksi_list = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Riwayat Semua Transaksi</h2>
        <span class="badge bg-dark p-2 fs-6">Total: <?php echo count($transaksi_list); ?> Transaksi</span>
    </div>
    <p class="text-muted">Berikut adalah catatan lengkap semua transaksi pembayaran yang telah dilakukan di platform ini.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Mahasiswa</th>
                            <th>Tutor</th>
                            <th>Kelas</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transaksi_list) > 0): ?>
                            <?php foreach ($transaksi_list as $transaksi): ?>
                            <tr>
                                <td><?php echo date('d M Y, H:i', strtotime($transaksi['Tanggal_Bayar'])); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['Nama_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['Nama_Tutor']); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['Matkul']); ?></td>
                                <td class="fw-bold">Rp <?php echo number_format($transaksi['Total_Bayar'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if ($transaksi['Status_Pembayaran']): ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-4">Belum ada transaksi yang tercatat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
