<?php
// LOKASI: /myitstutor/dashboard_admin.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Admin untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama = $_SESSION['user_nama'];
?>

<h1 class="mt-4">Dashboard Admin</h1>
<p class="lead">Selamat datang, Admin <?php echo htmlspecialchars($user_nama); ?>!</p>
<hr>

<!-- Panel Verifikasi Pendaftaran Tutor -->
<div class="card mb-4">
    <div class="card-header">
        <h4>Verifikasi Pendaftaran Tutor</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID Pendaftaran</th>
                        <th>Nama Mahasiswa</th>
                        <th>NRP</th>
                        <th>Keahlian Diajukan</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // PERBAIKAN: Ini adalah query yang benar untuk mengambil pendaftar yang menunggu
                    $sql_pendaftar = "SELECT pt.ID_pendaftaran, m.Nama_mahasiswa, m.NRP_Mahasiswa, pt.Matkul_didaftar 
                                      FROM Pendaftaran_Tutor pt
                                      JOIN Mahasiswa m ON pt.Mahasiswa_NRP_Mahasiswa = m.NRP_Mahasiswa
                                      WHERE pt.Status_pendaftar = 'Menunggu'";
                    $result_pendaftar = mysqli_query($conn, $sql_pendaftar);

                    if ($result_pendaftar && mysqli_num_rows($result_pendaftar) > 0):
                        while($pendaftar = mysqli_fetch_assoc($result_pendaftar)):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pendaftar['ID_pendaftaran']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['Nama_mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['NRP_Mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($pendaftar['Matkul_didaftar']); ?></td>
                        <td>
                            <!-- Form untuk setiap baris, mengirimkan data ke proses_verifikasi_tutor.php -->
                            <form action="proses_verifikasi_tutor.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_pendaftaran" value="<?php echo $pendaftar['ID_pendaftaran']; ?>">
                                <input type="hidden" name="nrp_mahasiswa" value="<?php echo $pendaftar['NRP_Mahasiswa']; ?>">
                                <input type="hidden" name="matkul" value="<?php echo $pendaftar['Matkul_didaftar']; ?>">
                                <button type="submit" name="action" value="terima" class="btn btn-success btn-sm">Setujui</button>
                                <button type="submit" name="action" value="tolak" class="btn btn-danger btn-sm">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada pendaftaran tutor baru yang perlu diverifikasi.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h3>Menu Panel Kontrol Lainnya</h3>
<div class="list-group">
    <a href="manajemen_pelanggaran.php" class="list-group-item list-group-item-action"><i class="fas fa-gavel me-2"></i>Manajemen Pelanggaran</a>
    <a href="manajemen_tutor.php" class="list-group-item list-group-item-action">Manajemen Role Tutor</a>
    <a href="ajukan_peminjaman.php" class="list-group-item list-group-item-action">Ajukan Peminjaman Ruangan</a>
    <a href="#" class="list-group-item list-group-item-action">Manajemen Peminjaman Ruangan</a>
    <a href="#" class="list-group-item list-group-item-action">Persetujuan Pencairan Dana Tutor</a>
    <li class="list-group-item list-group-item-secondary">Lihat Semua Data</li>
    <a href="view_mahasiswa.php" class="list-group-item list-group-item-action">Daftar Mahasiswa</a>
    <a href="view_tutors.php" class="list-group-item list-group-item-action">Daftar Tutor</a>
    <a href="view_kelas.php" class="list-group-item list-group-item-action">Daftar Kelas</a>
    <a href="view_transaksi.php" class="list-group-item list-group-item-action">Riwayat Transaksi</a>
</div>

<?php
require_once 'includes/footer.php';
?>
