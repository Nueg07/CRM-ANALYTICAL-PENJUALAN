<?php include 'header.php'; ?>

<div class="container">
	<!-- HERO SECTION-->
	<section class="py-5 bg-light">
		<div class="container">
			<div class="row px-4 px-lg-5 py-lg-4 align-items-center">
				<div class="col-lg-6">
					<h1 class="h2 text-uppercase mb-0">Ganti Password</h1>
				</div>
				<div class="col-lg-6 text-lg-right">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-lg-end mb-0 px-0">
							<li class="breadcrumb-item"><a href="index.php">Home</a></li>
							<li class="breadcrumb-item active" aria-current="page">Ganti Password</li>
						</ol>
					</nav>
				</div>				
			</div>
		</div>		
	</section>
	<section class="py-5">
		<div class="container p-0">
			<div class="row">
				<!-- SHOP SIDEBAR-->
				<div class="col-lg-3 order-2 order-lg-1">
					<div class="well">
						<?php include 'customer_sidebar.php' ?>
					</div>
				</div>
				<!-- SHOP LISTING-->				

				<div class="col-lg-9 order-2 order-lg-2 mb-5 mb-lg-0">
					<?php 
					if(isset($_GET['alert'])){
						if($_GET['alert'] == "sukses"){
							echo "<div class='alert alert-success'>Password anda berhasil diganti!</div>";
						}
					}
					?>
					<div class="well">									
						<form action="customer_password_act.php" method="post">
							<div class="form-group">
								<label for="">Masukkan Password Baru</label>
								<input type="password" class="form-control" required="required" name="password" placeholder="Masukkan password .." min="5">
							</div>

							<div class="form-group">
								<input type="submit" class="btn btn-primary btn-sm" value="Ganti Password">
							</div>
						</form>			
					</div>
				</div>
			</div>
		</div>
	</section>

</div>
<?php include 'footer.php'; ?>