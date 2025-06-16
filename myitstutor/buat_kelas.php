<?php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Halaman ini hanya untuk Tutor.</div>";
    require_once 'includes/footer.php';
    exit();
}

$sql_ruangan = "SELECT ID_Ruangan, Nama_Ruangan FROM Ruangan ORDER BY Nama_Ruangan";
$result_ruangan = mysqli_query($conn, $sql_ruangan);
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header"><h3 class="text-center font-weight-light my-4">Buat Kelas Baru</h3></div>
            <div class="card-body">
                <form action="proses_buat_kelas.php" method="POST">
                    <div class="mb-3">
                        <label for="matkul" class="form-label">Nama Mata Kuliah</label>
                        <input type="text" class="form-control" id="matkul" name="matkul" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tanggal" class="form-label">Tanggal Kelas</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ruangan" class="form-label">Pilih Ruangan</label>
                        <select class="form-select" id="ruangan" name="id_ruangan" required>
                            <option value="" disabled selected>-- Pilih Ruangan --</option>
                            <?php
                            if (mysqli_num_rows($result_ruangan) > 0) {
                                while ($ruang = mysqli_fetch_assoc($result_ruangan)) {
                                    echo '<option value="' . htmlspecialchars($ruang['ID_Ruangan']) . '">' . htmlspecialchars($ruang['Nama_Ruangan']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Buat Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>
