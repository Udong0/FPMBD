<?php
// LOKASI: /myitstutor/manajemen_peminjaman_sarpras.php
// Halaman untuk Sarpras mengelola permohonan peminjaman ruangan.

include('includes/header.php');
include('config/db_connect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan hanya sarpras yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'sarpras') {
    header("Location: login.php?error=access_denied");
    exit();
}

$sarpras_id = $_SESSION['user_id'];
$status_msg = '';

// --- Proses Persetujuan/Penolakan ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $peminjaman_id = $_POST['peminjaman_id'];
    $status_baru   = $_POST['action']; // 'Disetujui' atau 'Ditolak'

    if (in_array($status_baru, ['Disetujui','Ditolak'])) {
        $query_update = "
            UPDATE Peminjaman_Ruangan 
            SET Status_peminja   = ?, 
                Sarpras_ID_sarpras = ?, 
                Tanggal_Terbit     = NOW()
            WHERE ID_Peminjaman = ?
              AND Status_peminja = 'Menunggu'
        ";
        $stmt = $conn->prepare($query_update);
        $stmt->bind_param("sss", $status_baru, $sarpras_id, $peminjaman_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $status_msg = "<div class='alert alert-success'>Status permohonan berhasil diperbarui.</div>";
        } else {
            $status_msg = "<div class='alert alert-danger'>Gagal memperbarui status. Mungkin sudah diproses.</div>";
        }
        $stmt->close();
    }
}

// --- Ambil Data untuk Tampilan ---
$query_peminjaman = "
    SELECT 
        p.ID_Peminjaman,
        p.Tanggal_Pinjam,
        p.Ruangan_tujuan,
        p.Status_peminja,
        r.Nama_Ruangan,
        a.Nama_admin
    FROM Peminjaman_Ruangan p
    JOIN Ruangan r ON p.Ruangan_ID_Ruangan = r.ID_Ruangan
    JOIN admin a   ON p.admin_ID_admin       = a.ID_Admin
    ORDER BY 
      FIELD(p.Status_peminja, 'Menunggu','Disetujui','Ditolak'),
      p.Tanggal_Pinjam DESC
";
$result = $conn->query($query_peminjaman);
$peminjaman_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-5">
    <h2>Persetujuan Peminjaman Ruangan</h2>
    <p class="text-muted">Tinjau dan berikan persetujuan untuk semua permohonan peminjaman ruangan yang masuk.</p>

    <?php echo $status_msg; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Diajukan</th>
                            <th>Tgl Pinjam</th>
                            <th>Pemohon (Admin)</th>
                            <th>Ruangan</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($peminjaman_list) > 0): ?>
                            <?php foreach ($peminjaman_list as $item): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($item['Tanggal_Terbit'] ?? $item['Tanggal_Pinjam'])); ?></td>
                                <td class="fw-bold"><?php echo date('d M Y, H:i', strtotime($item['Tanggal_Pinjam'])); ?></td>
                                <td><?php echo htmlspecialchars($item['Nama_admin']); ?></td>
                                <td><?php echo htmlspecialchars($item['Nama_Ruangan']); ?></td>
                                <td><?php echo htmlspecialchars($item['Ruangan_tujuan']); ?></td>
                                <td>
                                    <?php 
                                        $cls = 'bg-secondary';
                                        if ($item['Status_peminja'] == 'Disetujui') $cls = 'bg-success';
                                        if ($item['Status_peminja'] == 'Ditolak')   $cls = 'bg-danger';
                                        if ($item['Status_peminja'] == 'Menunggu')  $cls = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $cls; ?>">
                                        <?php echo htmlspecialchars($item['Status_peminja']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($item['Status_peminja'] == 'Menunggu'): ?>
                                        <form action="manajemen_peminjaman_sarpras.php" method="POST" class="d-flex">
                                            <input type="hidden" name="peminjaman_id" value="<?php echo htmlspecialchars($item['ID_Peminjaman']); ?>">
                                            <button type="submit" name="action" value="Disetujui" class="btn btn-sm btn-success flex-grow-1">Setujui</button>
                                            <button type="submit" name="action" value="Ditolak"   class="btn btn-sm btn-danger  ms-1 flex-grow-1">Tolak</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center p-4">Tidak ada permohonan peminjaman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
