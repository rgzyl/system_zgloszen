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
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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
		<h3 class="text-center mt-5" style="font-weight:800; color:#070D1F;">POLITYKA PRYWATNOŚCI</h3>
		<div class="container" style="margin-top:5px;">
			<div class="card">		
				<h5 style="font-weight:800;">1. Zakres zbieranych danych osobowych</h5>
				<hr class="custom-hr"/>
				<p>W ramach naszej działalności zbieramy następujące dane osobowe:</p>
				<ul>
					<li>Imię</li>
					<li>Nazwisko</li>
					<li>Adres zamieszkania</li>
					<li>Numer telefonu</li>
					<li>Adres e-mail</li>
					<li>Adres IP (przy przesyłaniu zgłoszeń)</li>
				</ul>
				
				<h5 style="font-weight:800;">2. Cel przetwarzania danych osobowych</h5>
				<hr class="custom-hr"/>
				<p>Zgromadzone dane osobowe przetwarzane są wyłącznie w celu:</p>
				<ul>
					<li>umożliwienia kontaktu z osobami poszkodowanymi w wyniku powodzi;</li>
					<li>zapewnienia sprawnej komunikacji i odpowiedzi na zgłoszenia.</li>
				</ul>
				<p>Dane osobowe nie będą udostępniane podmiotom trzecim, chyba że zostanie to wymuszone przepisami prawa.</p>
				
				<h5 style="font-weight:800;">3. Podstawy prawne przetwarzania danych</h5>
				<hr class="custom-hr"/>
				<p>Dane osobowe przetwarzane są zgodnie z przepisami Rozporządzenia Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. (RODO) oraz innymi obowiązującymi przepisami o ochronie danych osobowych.</p>
				
				<h5 style="font-weight:800;">4. Bezpieczeństwo danych</h5>
				<hr class="custom-hr"/>
				<p>Zobowiązujemy się do zachowania najwyższych standardów bezpieczeństwa w zakresie ochrony danych osobowych. W tym celu stosujemy odpowiednie techniczne i organizacyjne środki zabezpieczające przed nieautoryzowanym dostępem, zmianą, utratą lub zniszczeniem danych.</p>

				<h5 style="font-weight:800;">5. Prawa użytkowników</h5>
				<hr class="custom-hr"/>
				<p>Każda osoba, której dane dotyczą, ma prawo do:</p>
				<ul>
					<li>dostępu do swoich danych osobowych,</li>
					<li>ich sprostowania, usunięcia lub ograniczenia przetwarzania,</li>
					<li>wniesienia sprzeciwu wobec przetwarzania,</li>
					<li>przenoszenia danych.</li>
				</ul>
				<p>W celu realizacji powyższych uprawnień prosimy o kontakt z nami poprzez poniżej wskazany adres e-mail.</p>

				<h5 style="font-weight:800;">6. Okres przechowywania danych</h5>
				<hr class="custom-hr"/>
				<p>Dane osobowe będą przechowywane wyłącznie przez okres niezbędny do realizacji celu, dla którego zostały zebrane, a następnie zostaną usunięte zgodnie z obowiązującymi przepisami prawa.</p>
	
				<h5 style="font-weight:800;">7. Kontakt</h5>
				<hr class="custom-hr"/>
				<p>Wszelkie pytania, wątpliwości lub wnioski dotyczące przetwarzania danych osobowych prosimy kierować na adres e-mail: <a style="color: #070D1F;" href="mailto:awaria@zglos.strzegom.pl">awaria@zglos.strzegom.pl</a>.</p>
				<p>W przypadku uznania, że przetwarzanie danych osobowych narusza przepisy prawa, użytkownik ma prawo wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych.</p>
			</div>
		</div>	
		<div class="footer">
			<p>&copy; <?= date('Y').' '.$website_name; ?>. Wszelkie prawa zastrzeżone.</p>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
	</body>
</html>
