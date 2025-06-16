<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    echo "<div class='alert alert-danger'>Hanya mahasiswa yang dapat melihat detail kelas.</div>";
    require_once 'includes/footer.php';
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Kelas tidak valid.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_kelas = $_GET['id'];

$sql = "SELECT k.*, t.Nama_Tutor, r.Nama_Ruangan, r.Lokasi 
        FROM Kelas k
        JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
        JOIN Ruangan r ON k.Ruangan_ID_Ruangan = r.ID_Ruangan
        WHERE k.ID_Kelas = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id_kelas);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {
    $kelas = mysqli_fetch_assoc($result);
} else {
    echo "<div class='alert alert-danger'>Kelas tidak ditemukan.</div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h2>Detail Kelas: <?php echo htmlspecialchars($kelas['Matkul']); ?></h2>
    </div>
    <div class="card-body">
        <h5 class="card-title">Tutor: <?php echo htmlspecialchars($kelas['Nama_Tutor']); ?></h5>
        <p class="card-text">
            <strong>Jadwal:</strong> <?php echo date("d F Y", strtotime($kelas['Tanggal_Kelas'])); ?>, 
            pukul <?php echo date("H:i", strtotime($kelas['kelas_start'])); ?> - <?php echo date("H:i", strtotime($kelas['kelas_end'])); ?>
        </p>
        <p class="card-text">
            <strong>Lokasi:</strong> <?php echo htmlspecialchars($kelas['Nama_Ruangan']); ?> (<?php echo htmlspecialchars($kelas['Lokasi']); ?>)
        </p>
        <p class="card-text">
            <strong>Status:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars($kelas['Status_kelas']); ?></span>
        </p>

        <form action="proses_gabung_kelas.php" method="POST" class="mt-4">
            <input type="hidden" name="id_kelas" value="<?php echo htmlspecialchars($kelas['ID_Kelas']); ?>">
            <input type="hidden" name="nrp_tutor" value="<?php echo htmlspecialchars($kelas['Tutor_NRP_Tutor']); ?>">
            <button type="submit" class="btn btn-primary btn-lg">Gabung Kelas Ini</button>
        </form>
    </div>
</div>


<?php
mysqli_stmt_close($stmt);
require_once 'includes/footer.php';
?>
