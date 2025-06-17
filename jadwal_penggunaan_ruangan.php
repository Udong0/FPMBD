<?php
// LOKASI: /myitstutor/jadwal_penggunaan_ruangan.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'sarpras') {
    header("Location: login.php?error=access_denied");
    exit();
}
?>

<div class="container mt-5">
    <h2>Jadwal Penggunaan Ruangan</h2>
    <p class="text-muted">Daftar semua jadwal peminjaman ruangan yang telah disetujui.</p>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Peminjaman</th>
                        <th>Ruangan</th>
                        <th>Tujuan</th>
                        <th>Waktu Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "
                    SELECT p.ID_Peminjaman, r.Nama_Ruangan, p.Ruangan_tujuan, p.Tanggal_Pinjam, p.Status_peminja
                    FROM Peminjaman_Ruangan p
                    JOIN Ruangan r ON p.Ruangan_ID_Ruangan = r.ID_Ruangan
                    WHERE p.Status_peminja = 'Disetujui'
                    ORDER BY p.Tanggal_Pinjam
                ";
                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['ID_Peminjaman']}</td>
                                <td>" . htmlspecialchars($row['Nama_Ruangan']) . "</td>
                                <td>" . htmlspecialchars($row['Ruangan_tujuan']) . "</td>
                                <td>" . date('d M Y, H:i', strtotime($row['Tanggal_Pinjam'])) . "</td>
                                <td><span class='badge bg-success'>{$row['Status_peminja']}</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada peminjaman disetujui.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
