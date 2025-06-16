<?php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Tutor untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama = $_SESSION['user_nama'];
$nrp_tutor = $_SESSION['user_id'];
?>

<h1 class="mt-4">Dashboard Tutor</h1>
<p class="lead">Selamat datang, Tutor <?php echo htmlspecialchars($user_nama); ?>!</p>
<hr>

<div class="row">
    <div class="col-md-8">
        <h3>Kelas yang Anda Ajar</h3>
        <div class="list-group">
        <?php

            $sql = "SELECT ID_Kelas, Matkul, Tanggal_Kelas, Status_kelas FROM Kelas WHERE Tutor_NRP_Tutor = ? ORDER BY Tanggal_Kelas DESC";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $nrp_tutor);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                while ($kelas = mysqli_fetch_assoc($result)) {
                    $tanggal_format = date("d F Y", strtotime($kelas['Tanggal_Kelas']));

                    $status_badge_class = '';
                    switch ($kelas['Status_kelas']) {
                        case 'Selesai':
                            $status_badge_class = 'bg-secondary';
                            break;
                        case 'Aktif':
                            $status_badge_class = 'bg-success';
                            break;
                        case 'Dijadwalkan':
                            $status_badge_class = 'bg-info text-dark';
                            break;
                        default:
                            $status_badge_class = 'bg-light text-dark';
                    }

                    echo '<div class="list-group-item list-group-item-action">';
                    echo '  <div class="d-flex w-100 justify-content-between">';
                    echo '      <h5 class="mb-1">' . htmlspecialchars($kelas['Matkul']) . '</h5>';
                    echo '      <span class="badge ' . $status_badge_class . '">' . htmlspecialchars($kelas['Status_kelas']) . '</span>';
                    echo '  </div>';
                    echo '  <p class="mb-1">Tanggal: ' . $tanggal_format . '</p>';
                    echo '  <small>ID Kelas: ' . htmlspecialchars($kelas['ID_Kelas']) . '</small>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-muted">Anda belum memiliki kelas. Silakan buat kelas baru dari menu di samping.</p>';
            }
            mysqli_stmt_close($stmt);
        ?>
        </div>
    </div>
    <div class="col-md-4">
        <h3>Menu Tutor</h3>
        <div class="list-group">
            <a href="dashboard_tutor.php" class="list-group-item list-group-item-action active">Manajemen Kelas Anda</a>
            <a href="buat_kelas.php" class="list-group-item list-group-item-action">Buat Kelas Baru</a>
            <a href="#" class="list-group-item list-group-item-action">Lihat Rating & Review</a>
            <a href="#" class="list-group-item list-group-item-action">Pencairan Dana</a>
            <a href="#" class="list-group-item list-group-item-action">Profil Tutor</a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
