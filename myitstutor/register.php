<?php
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header"><h3 class="text-center font-weight-light my-4">Buat Akun Baru</h3></div>
            <div class="card-body">
                <form action="proses_register.php" method="POST">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="nama" name="nama" type="text" placeholder="Masukkan Nama Lengkap" required />
                        <label for="nama">Nama Lengkap</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="nrp" name="nrp" type="text" placeholder="Masukkan NRP" required />
                        <label for="nrp">NRP</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="email" name="email" type="email" placeholder="nama@email.com" required />
                        <label for="email">Alamat Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="password" name="password" type="password" placeholder="Buat Password" required />
                        <label for="password">Password</label>
                    </div>
                    <div class="mt-4 mb-0 d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Daftar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small"><a href="login.php">Sudah punya akun? Login di sini!</a></div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
