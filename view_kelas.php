<?php
// view_kelas.php
// Halaman untuk admin melihat daftar semua kelas yang pernah dibuat.

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
$query_kelas = "
    SELECT 
        k.ID_Kelas, 
        k.Matkul, 
        k.Tanggal_Kelas, 
        k.kelas_start, 
        k.kelas_end, 
        k.Status_kelas,
        t.Nama_Tutor,
        r.Nama_Ruangan
    FROM Kelas k
    JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
    JOIN Ruangan r ON k.Ruangan_ID_Ruangan = r.ID_Ruangan
    ORDER BY k.Tanggal_Kelas DESC, k.kelas_start DESC
";
$result = $conn->query($query_kelas);
$kelas_list = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Semua Kelas</h2>
        <span class="badge bg-info p-2 fs-6">Total: <?php echo count($kelas_list); ?> Kelas</span>
    </div>
    <p class="text-muted">Berikut adalah riwayat lengkap semua kelas yang pernah dijadwalkan di platform ini.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Tutor</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($kelas_list) > 0): ?>
                            <?php foreach ($kelas_list as $kelas): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($kelas['Matkul']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['Nama_Tutor']); ?></td>
                                <td><?php echo date('d M Y', strtotime($kelas['Tanggal_Kelas'])); ?></td>
                                <td><?php echo date('H:i', strtotime($kelas['kelas_start'])) . ' - ' . date('H:i', strtotime($kelas['kelas_end'])); ?></td>
                                <td><?php echo htmlspecialchars($kelas['Nama_Ruangan']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['Status_kelas']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-4">Belum ada kelas yang pernah dibuat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
