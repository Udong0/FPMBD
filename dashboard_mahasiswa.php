<?php
// LOKASI: /dashboard_mahasiswa.php

require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: pastikan hanya mahasiswa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mahasiswa') {
    echo "<div class='alert alert-danger'>Akses Ditolak. Anda harus login sebagai Mahasiswa untuk mengakses halaman ini.</div>";
    require_once 'includes/footer.php';
    exit();
}

$user_nama     = $_SESSION['user_nama'];
$nrp_mahasiswa = $_SESSION['user_id'];

// Jika ada pencarian spesialisasi
if (!empty($_GET['specialisasi'])) {
    $search = '%' . $_GET['specialisasi'] . '%';
    $sql = "
      SELECT DISTINCT
        t.NRP_Tutor,
        t.Nama_Tutor,
        t.Spesialisasi,
        k.ID_Kelas,
        k.Matkul
      FROM Tutor t
      JOIN Kelas k 
        ON t.NRP_Tutor = k.Tutor_NRP_Tutor
      WHERE t.Spesialisasi LIKE ?
      ORDER BY t.Nama_Tutor, k.Tanggal_Kelas DESC
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    echo "<div class='container mt-5'>";
    echo "<h2>Hasil Pencarian Tutor: “" . htmlspecialchars($_GET['specialisasi']) . "”</h2>";

    if (mysqli_num_rows($result) > 0) {
        echo "<div class='table-responsive'><table class='table table-striped'>";
        echo "<thead><tr>
                <th>NRP Tutor</th>
                <th>Nama Tutor</th>
                <th>Spesialisasi</th>
                <th>ID Kelas</th>
                <th>Mata Kuliah</th>
              </tr></thead><tbody>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>".htmlspecialchars($row['NRP_Tutor'])."</td>
                    <td>".htmlspecialchars($row['Nama_Tutor'])."</td>
                    <td>".htmlspecialchars($row['Spesialisasi'])."</td>
                    <td>".htmlspecialchars($row['ID_Kelas'])."</td>
                    <td>".htmlspecialchars($row['Matkul'])."</td>
                  </tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p class='text-muted'>Tidak ditemukan tutor dengan spesialisasi “" 
             . htmlspecialchars($_GET['specialisasi']) . "”.</p>";
    }

    echo "</div>";
    mysqli_stmt_close($stmt);
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="container mt-4">
  <h1>Selamat datang, <?php echo htmlspecialchars($user_nama); ?>!</h1>
  <p class="lead">Dashboard Mahasiswa</p>
  <hr>

  <!-- Form Pencarian Tutor berdasarkan Spesialisasi -->
  <form method="GET" class="mb-5">
    <div class="input-group">
      <input
        type="text"
        name="specialisasi"
        class="form-control"
        placeholder="Cari tutor berdasarkan spesialisasi… (misal: Fisika Dasar)"
        value="<?php echo htmlspecialchars($_GET['specialisasi'] ?? ''); ?>"
      >
      <button class="btn btn-outline-secondary" type="submit">Cari Tutor</button>
    </div>
  </form>

  <div class="row">
    <div class="col-md-8">

      <!-- KELAS YANG SAYA IKUTI -->
      <h3 class="mb-3">Kelas yang Anda Ikuti</h3>
      <div class="list-group mb-5">
        <?php
        $sql_my_kelas = "
          SELECT k.ID_Kelas, k.Matkul, t.Nama_Tutor, k.Status_kelas
          FROM mahasiswa_kelas mk
          JOIN Kelas k ON mk.Kelas_ID = k.ID_Kelas
          JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
          WHERE mk.Mahasiswa_NRP = ?
          ORDER BY k.Tanggal_Kelas DESC
        ";
        $stmt_my_kelas = mysqli_prepare($conn, $sql_my_kelas);
        mysqli_stmt_bind_param($stmt_my_kelas, "s", $nrp_mahasiswa);
        mysqli_stmt_execute($stmt_my_kelas);
        $result_my_kelas = mysqli_stmt_get_result($stmt_my_kelas);

        if (mysqli_num_rows($result_my_kelas) > 0) {
          while ($kelas = mysqli_fetch_assoc($result_my_kelas)) {
            echo '<div class="list-group-item">';
            echo '  <div class="d-flex w-100 justify-content-between">';
            echo '    <h5 class="mb-1">'.htmlspecialchars($kelas['Matkul']).'</h5>';
            echo '    <span class="badge bg-success">'.htmlspecialchars($kelas['Status_kelas']).'</span>';
            echo '  </div>';
            echo '  <p class="mb-1">Tutor: '.htmlspecialchars($kelas['Nama_Tutor']).'</p>';
            echo '</div>';
          }
        } else {
          echo '<p class="text-muted">Anda belum bergabung dengan kelas manapun.</p>';
        }
        mysqli_stmt_close($stmt_my_kelas);
        ?>
      </div>

      <!-- DAFTAR KELAS TERSEDIA -->
      <h3 class="mb-3">Daftar Kelas Tersedia</h3>
      <div class="list-group">
        <?php
        $sql_kelas = "
          SELECT k.ID_Kelas, k.Matkul, k.Tanggal_Kelas, k.kelas_start, t.Nama_Tutor
          FROM Kelas k
          JOIN Tutor t ON k.Tutor_NRP_Tutor = t.NRP_Tutor
          WHERE k.Status_kelas IN ('Aktif','Dijadwalkan')
            AND k.ID_Kelas NOT IN (
              SELECT Kelas_ID FROM mahasiswa_kelas WHERE Mahasiswa_NRP = ?
            )
          ORDER BY k.Tanggal_Kelas, k.kelas_start
        ";
        $stmt_kelas = mysqli_prepare($conn, $sql_kelas);
        mysqli_stmt_bind_param($stmt_kelas, "s", $nrp_mahasiswa);
        mysqli_stmt_execute($stmt_kelas);
        $result_kelas = mysqli_stmt_get_result($stmt_kelas);

        if (mysqli_num_rows($result_kelas) > 0) {
          while ($kelas = mysqli_fetch_assoc($result_kelas)) {
            $tgl   = date("d F Y", strtotime($kelas['Tanggal_Kelas']));
            $jam   = date("H:i",    strtotime($kelas['kelas_start']));
            echo '<a href="detail_kelas.php?id='.$kelas['ID_Kelas'].'" class="list-group-item list-group-item-action">';
            echo '  <div class="d-flex w-100 justify-content-between">';
            echo '    <h5 class="mb-1">'.htmlspecialchars($kelas['Matkul']).'</h5>';
            echo '    <small>Tanggal: '.$tgl.' | Jam: '.$jam.'</small>';
            echo '  </div>';
            echo '  <p class="mb-1">Tutor: '.htmlspecialchars($kelas['Nama_Tutor']).'</p>';
            echo '</a>';
          }
        } else {
          echo '<p class="text-muted">Saat ini tidak ada kelas lain yang tersedia.</p>';
        }
        mysqli_stmt_close($stmt_kelas);
        ?>
      </div>

    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header"><h4>Menu Anda</h4></div>
        <div class="list-group list-group-flush">
          <a href="dashboard_mahasiswa.php" class="list-group-item list-group-item-action active">Cari &amp; Gabung Kelas</a>
          <a href="riwayat_transaksi.php"    class="list-group-item list-group-item-action">Riwayat Transaksi</a>
          <a href="daftar_tutor.php"         class="list-group-item list-group-item-action">Daftar Menjadi Tutor</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require_once 'includes/footer.php';
?>
