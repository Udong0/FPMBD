<?php
// FILE: manajemen_pencairan.php
require_once 'includes/header.php';
require_once 'config/db_connect.php';

// Keamanan: hanya admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Pesan status (optional)
if (isset($_GET['status']) && $_GET['status'] === 'sukses') {
    echo "<div class='alert alert-success'>Permintaan berhasil diproses.</div>";
}

// Ambil data permintaan pencairan
$sql = "
  SELECT p.ID_Pencairan, p.Jumlah, p.Tanggal_Permintaan, p.Status,
         p.Tanggal_Persetujuan, t.Nama_Tutor
  FROM Pencairan p
  JOIN Tutor t ON p.Tutor_NRP_Tutor = t.NRP_Tutor
  ORDER BY p.Tanggal_Permintaan DESC
";
$requests = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-5">
  <h2>Persetujuan Pencairan Dana Tutor</h2>
  <div class="table-responsive mt-4">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tutor</th>
          <th>Jumlah (Rp)</th>
          <th>Tanggal Permintaan</th>
          <th>Status</th>
          <th>Tgl Persetujuan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['ID_Pencairan']) ?></td>
          <td><?= htmlspecialchars($r['Nama_Tutor']) ?></td>
          <td><?= number_format($r['Jumlah'],0,',','.') ?></td>
          <td><?= date('d M Y, H:i', strtotime($r['Tanggal_Permintaan'])) ?></td>
          <td>
            <span class="badge <?= $r['Status']=='Menunggu'?'bg-warning text-dark':($r['Status']=='Disetujui'?'bg-success':'bg-danger') ?>">
              <?= htmlspecialchars($r['Status']) ?>
            </span>
          </td>
          <td><?= $r['Tanggal_Persetujuan'] 
                ? date('d M Y, H:i', strtotime($r['Tanggal_Persetujuan'])) 
                : '-' ?></td>
          <td>
            <?php if ($r['Status']=='Menunggu'): ?>
            <form action="proses_verifikasi_pencairan.php" method="POST" class="d-flex gap-2">
              <input type="hidden" name="id_pencairan" value="<?= htmlspecialchars($r['ID_Pencairan']) ?>">
              <button name="action" value="setuju" class="btn btn-sm btn-success">Setujui</button>
              <button name="action" value="tolak" class="btn btn-sm btn-danger">Tolak</button>
            </form>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
