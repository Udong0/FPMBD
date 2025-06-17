<?php
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-lg border-0 rounded-lg mt-5">
      <div class="card-header">
        <h3 class="text-center font-weight-light my-4">Login</h3>
      </div>
      <div class="card-body">
        <form action="proses_login.php" method="POST">
          <!-- PILIH ROLE (opsional untuk admin/sarpras) -->
          <div class="form-floating mb-3">
            <select class="form-select" id="role" name="role">
              <option value="" selected>-- Pilih Role (Mahasiswa/Tutor) --</option>
              <option value="mahasiswa">Mahasiswa</option>
              <option value="tutor">Tutor</option>
            </select>
            <label for="role">Login Sebagai</label>
          </div>

          <div class="form-floating mb-3">
            <input class="form-control" id="nrp" name="nrp" type="text" placeholder="Masukkan NRP atau ID Admin/Sarpras" required />
            <label for="nrp">NRP</label>
          </div>
          <div class="form-floating mb-3">
            <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
            <label for="password">Password</label>
          </div>
          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
          </div>
        </form>
      </div>
      <div class="card-footer text-center py-3">
        <div class="small"><a href="register.php">Belum punya akun? Daftar!</a></div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
