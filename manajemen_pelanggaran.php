<?php
// manajemen_pelanggaran.php
// Halaman untuk admin meninjau laporan dan memberikan sanksi.

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

// Logika untuk memproses form pemberian sanksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beri_sanksi'])) {
    $tutor_nrp_sanksi = $_POST['tutor_nrp'];
    
    // Update status tutor menjadi 'Suspended'
    $query_sanksi = "UPDATE Tutor SET Status_tutor = 'Suspended' WHERE NRP_Tutor = ?";
    $stmt_sanksi = $conn->prepare($query_sanksi);
    $stmt_sanksi->bind_param("s", $tutor_nrp_sanksi);
    if ($stmt_sanksi->execute()) {
        $stmt_sanksi->close();
        // Redirect untuk refresh halaman dan menunjukkan status terbaru
        header("Location: manajemen_pelanggaran.php?status=sanksi_sukses");
        exit();
    } else {
        // Handle error jika gagal update
        $error_msg = "Gagal memberikan sanksi.";
    }
}

// Query utama untuk mengambil semua data laporan
$query_laporan = "
    SELECT 
        p.ID_Pelaporan, 
        p.Tanggal_lapor,
        p.Tipe_Pelanggaran, 
        p.Deskripsi_Pelanggaran, 
        m.Nama_mahasiswa AS nama_pelapor,
        t.NRP_Tutor AS nrp_terlapor,
        t.Nama_Tutor AS nama_terlapor,
        t.Status_tutor
    FROM Pelanggaran p
    LEFT JOIN mahasiswa_pelanggaran mp ON p.ID_Pelaporan = mp.Pelanggaran_ID
    LEFT JOIN Mahasiswa m ON mp.Mahasiswa_NRP = m.NRP_Mahasiswa
    LEFT JOIN tutor_pelanggaran tp ON p.ID_Pelaporan = tp.Pelanggaran_ID
    LEFT JOIN Tutor t ON tp.Tutor_NRP = t.NRP_Tutor
    ORDER BY p.Tanggal_lapor DESC
";

$result = $conn->query($query_laporan);
$laporan_list = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Pelanggaran</h2>
        <span class="badge bg-dark p-2">Total Laporan: <?php echo count($laporan_list); ?></span>
    </div>
    <p class="text-muted">Tinjau semua laporan pelanggaran yang diajukan oleh mahasiswa. Gunakan tombol aksi untuk memberikan sanksi jika diperlukan.</p>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sanksi_sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Berhasil!</strong> Sanksi suspend telah diterapkan pada tutor.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
     <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Laporan</th>
                            <th>Tanggal</th>
                            <th>Pelapor</th>
                            <th>Tutor Terlapor</th>
                            <th>Status Tutor</th>
                            <th>Subjek Laporan</th>
                            <th style="width: 30%;">Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($laporan_list) > 0): ?>
                            <?php foreach ($laporan_list as $laporan): ?>
                            <tr class="<?php echo ($laporan['Status_tutor'] == 'Suspended') ? 'table-danger' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($laporan['ID_Pelaporan']); ?></strong></td>
                                <td><?php echo date('d M Y, H:i', strtotime($laporan['Tanggal_lapor'])); ?></td>
                                <td><?php echo htmlspecialchars($laporan['nama_pelapor'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($laporan['nama_terlapor'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($laporan['Status_tutor'] == 'Aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($laporan['Tipe_Pelanggaran']); ?></td>
                                <td><small><?php echo nl2br(htmlspecialchars($laporan['Deskripsi_Pelanggaran'])); ?></small></td>
                                <td>
                                    <?php if ($laporan['Status_tutor'] == 'Aktif' && !empty($laporan['nrp_terlapor'])): ?>
                                    <form action="manajemen_pelanggaran.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin men-suspend tutor ini? Tindakan ini akan menghentikan tutor dari membuat kelas baru.');">
                                        <input type="hidden" name="tutor_nrp" value="<?php echo htmlspecialchars($laporan['nrp_terlapor']); ?>">
                                        <button type="submit" name="beri_sanksi" class="btn btn-sm btn-outline-danger">Suspend Tutor</button>
                                    </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center p-4">Belum ada laporan pelanggaran yang masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
