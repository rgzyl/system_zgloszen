<?php 
	session_start();
	if(file_exists('../config.php')){
		require("../config.php");
	} else {
		header("Location: ../konfiguracja/?option=1");
	}
	
	require("../params.php");
	require("../functions.php");
	
	$stmt = $con->prepare('SELECT status FROM str_config WHERE IdConfig = 1');
	$stmt->execute();
	$stmt->bind_result($status);
	$stmt->fetch();
	$stmt->close();

	$isLogged = isset($_SESSION['IdUser']) && $_SESSION['IdUser'] == true;

	if($status != 1 && !$isLogged){
		header("Location: ../status/");
		exit();
	}
?>

<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Zgłoś szkodę | <?= $website_name; ?></title>
		<meta name="description" content="<?= $website_description; ?>">
		<meta name="keywords" content="<?= $website_keywords; ?>">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
		<link href="../assets/css/styles.css" rel="stylesheet">
		<link rel="shortcut icon" href="../assets/img/favicon.ico">
		<link rel="icon" href="../assets/img/favicon.ico">
		<link rel="apple-touch-icon" href="../assets/img/favicon.ico">
	</head>
	<body>	
		<header class="navbar navbar-expand-lg navbar-light shadow-sm">
			<div class="container-fluid">
				<a class="navbar-brand" href="../"><img src="../assets/img/logo.png" alt="Logo" width="40" height="40" class="d-inline-block">  Gmina Strzegom</a>
				<ul class="navbar-nav ms-auto">
					<?php if ($isLogged) { ?>
						<li class="nav-item">
							<a class="nav-link" href="../admin/dashboard/index.php" role="button"><i class="bi bi-box-arrow-in-up-left"></i></a>
						</li>
					<?php } ?>	
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-list"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item item" href="../kontakt/"><i class="bi bi-envelope"></i> Kontakt</a></li>
							<li><a class="dropdown-item item" href="../polityka-prywatnosci/"><i class="bi bi-shield-lock"></i> Polityka prywatności</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</header>
		<h3 class="text-center mt-5" style="font-weight:800; color:#070D1F;">KONTAKT</h3>
		<div class="container" style="margin-top:5px;">
			<div class="card">
				<div class="card-body">
					<h5 style="font-weight:800;">Ośrodek Pomocy Społecznej w Strzegomiu</h5>
					<hr class="custom-hr" />
					<p>ul. Armi Krajowej 23</p>
					<p>tel./faks: <a href="tel:746477180" style="color: #070D1F;">74 64 77 180</a></p>
					<p>e-mail: <a href="mailto:opsstrzegom@poczta.wp.pl" style="color: #070D1F;">opsstrzegom@poczta.wp.pl</a></p>
					<hr class="custom-hr" />
					<h6 style="font-weight:600;">Czynne:</h6>
					<p>pn. - pt. - 7:30 do 15:30</p>
					<hr class="custom-hr" />
					<p>W przypadku awarii strony, prosimy o kontakt pod adresem:</p>
					<p>e-mail: <a href="mailto:awaria@zglos.strzegom.pl" style="color: #070D1F;">awaria@zglos.strzegom.pl</a></p>
				</div>
			</div>
		</div>
		<div class="footer">
			<p>&copy; <?= date('Y').' '.$website_name; ?>. Wszelkie prawa zastrzeżone.</p>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
	</body>
</html>
