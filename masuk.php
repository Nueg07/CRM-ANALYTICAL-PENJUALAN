<?php include 'header.php'; ?>

<div class="container">
    <!-- HERO SECTION-->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row px-4 px-lg-5 py-lg-4 align-items-center">
                <div class="col-lg-6">
                    <h1 class="h2 text-uppercase mb-0">Login Customer</h1>
                </div>
                <div class="col-lg-6 text-lg-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-lg-end mb-0 px-0">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Login</li>
                        </ol>
                    </nav>
                </div>                
            </div>
        </div>        
    </section>

    <section class="py-5">
        <div class="container p-0">
            <div class="row">
                <!-- SIDEBAR -->
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="well">
                        <h2>Belum Punya Akun?</h2>
                        <p><strong>Silahkan Daftar</strong></p>
                        <p>Silahkan daftar untuk melakukan transaksi pada sistem ini. Kerahasiaan data customer dijamin terjaga.</p>
                        <br>
                        <a href="daftar.php" class="btn btn-primary">Continue Daftar</a>
                    </div>
                </div>

                <!-- FORM LOGIN -->
                <div class="col-lg-6 order-2 order-lg-2 mb-5 mb-lg-0">
                    <div class="well">                                    
                        <form action="masuk_act.php" method="post">
                            <div class="form-group">
                                <label class="control-label" for="input-email">E-Mail Address</label>
                                <input type="text" name="email" placeholder="E-Mail Address" class="form-control" required="required">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="input-password">Password</label>
                                <input type="password" name="password" placeholder="Password" class="form-control" required="required">
                            </div>
                            <input type="submit" value="Login" class="btn btn-primary">                        
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
