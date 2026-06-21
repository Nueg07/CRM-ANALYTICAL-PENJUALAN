<?php include 'header.php'; ?>

<div class="container">
    <!-- HERO SECTION-->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row px-4 px-lg-5 py-lg-4 align-items-center">
                <div class="col-lg-6">
                    <h1 class="h2 text-uppercase mb-0">Daftar Customer</h1>
                </div>
                <div class="col-lg-6 text-lg-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-lg-end mb-0 px-0">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Daftar</li>
                        </ol>
                    </nav>
                </div>              
            </div>
        </div>      
    </section>

    <section class="py-5">
        <div class="container p-0">
            <div class="row">
                <!-- INFO LOGIN -->
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="well">
                        <h2>Sudah Punya Akun?</h2>
                        <p><strong>Silahkan Login</strong></p>
                        <p>Jika sudah memiliki akun, silahkan login untuk melakukan transaksi.</p>
                        <br>
                        <a href="masuk.php" class="btn btn-primary">Continue Login</a>
                    </div>
                </div>

                <!-- FORM DAFTAR -->
                <div class="col-lg-6 order-2 order-lg-2 mb-5 mb-lg-0">
                    <div class="well">                                   
                        <form action="daftar_act.php" method="post">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" name="nama" placeholder="Nama Lengkap" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">E-Mail Address</label>
                                <input type="email" name="email" placeholder="E-Mail Address" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="hp">Tlp / HP</label>
                                <input type="text" name="hp" placeholder="Nomor Handphone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" placeholder="Password" class="form-control" required>
                            </div>
                            <input type="submit" value="DAFTAR" class="btn btn-danger">                       
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
