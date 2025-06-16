<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Mahasiswa untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama = $_SESSION['user_nama'];
$nrp_mahasiswa = $_SESSION['user_id'];
?>

<h1 class="mt-4">Dashboard Mahasiswa</h1>
<p class="lead">Selamat datang, <?php echo htmlspecialchars($user_nama); ?>!</p>
<hr>

<div class="row">
    <div class="col-md-8">

        <h3>Kelas yang Anda Ikuti</h3>
        <div class="list-group mb-5">
            <?php
            $sql_my_kelas = "SELECT k.ID_Kelas, k.Matkul, t.Nama_Tutor, k.Status_kelas
                             FROM mahasiswa_kelas mk
                             JOIN Kelas k ON mk.Kelas_ID = k.ID_Kelas
                             JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
                             WHERE mk.Mahasiswa_NRP = ?
                             ORDER BY k.Tanggal_Kelas DESC";
            
            $stmt_my_kelas = mysqli_prepare($conn, $sql_my_kelas);
            mysqli_stmt_bind_param($stmt_my_kelas, "s", $nrp_mahasiswa);
            mysqli_stmt_execute($stmt_my_kelas);
            $result_my_kelas = mysqli_stmt_get_result($stmt_my_kelas);

            if (mysqli_num_rows($result_my_kelas) > 0) {
                while ($kelas = mysqli_fetch_assoc($result_my_kelas)) {
                    echo '<div class="list-group-item">';
                    echo '  <div class="d-flex w-100 justify-content-between">';
                    echo '      <h5 class="mb-1">' . htmlspecialchars($kelas['Matkul']) . '</h5>';
                    echo '      <span class="badge bg-success">' . htmlspecialchars($kelas['Status_kelas']) . '</span>';
                    echo '  </div>';
                    echo '  <p class="mb-1">Tutor: ' . htmlspecialchars($kelas['Nama_Tutor']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-muted">Anda belum bergabung dengan kelas manapun.</p>';
            }
            mysqli_stmt_close($stmt_my_kelas);
            ?>
        </div>

        <h3>Daftar Kelas Tersedia</h3>
        <div class="list-group">
            <?php
            $sql_kelas = "SELECT k.ID_Kelas, k.Matkul, k.Tanggal_Kelas, k.kelas_start, t.Nama_Tutor 
                          FROM Kelas k
                          JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
                          WHERE k.Status_kelas IN ('Aktif', 'Dijadwalkan')
                          ORDER BY k.Tanggal_Kelas, k.kelas_start";
                          
            $result_kelas = mysqli_query($conn, $sql_kelas);
            
            if (mysqli_num_rows($result_kelas) > 0) {
                while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                    $tanggal_format = date("d F Y", strtotime($kelas['Tanggal_Kelas']));
                    $jam_format = date("H:i", strtotime($kelas['kelas_start']));

                    echo '<a href="detail_kelas.php?id=' . $kelas['ID_Kelas'] . '" class="list-group-item list-group-item-action">';
                    echo '<div class="d-flex w-100 justify-content-between">';
                    echo '<h5 class="mb-1">' . htmlspecialchars($kelas['Matkul']) . '</h5>';
                    echo '<small>Tanggal: ' . $tanggal_format . ' | Jam: ' . $jam_format . '</small>';
                    echo '</div>';
                    echo '<p class="mb-1">Tutor: ' . htmlspecialchars($kelas['Nama_Tutor']) . '</p>';
                    echo '</a>';
                }
            } else {
                echo '<p class="text-muted">Saat ini tidak ada kelas lain yang tersedia.</p>';
            }
            ?>
        </div>
    </div>
    <div class="col-md-4">
        <h3>Menu Anda</h3>
        <div class="list-group">
            <a href="dashboard_mahasiswa.php" class="list-group-item list-group-item-action active">Cari & Gabung Kelas</a>
            <a href="#" class="list-group-item list-group-item-action">Riwayat Transaksi</a>
            <a href="daftar_tutor.php" class="list-group-item list-group-item-action">Daftar Menjadi Tutor</a>
            <a href="#" class="list-group-item list-group-item-action text-danger">Laporkan Pelanggaran</a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
