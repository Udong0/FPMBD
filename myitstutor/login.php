<?php
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
            <div class="card-body">
                <form action="proses_login.php" method="POST">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="nrp" name="nrp" type="text" placeholder="Masukkan NRP" required />
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

<?php
require_once 'includes/footer.php';
?>
