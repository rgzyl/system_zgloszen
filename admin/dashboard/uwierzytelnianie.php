<?php
	if  (file_exists('../../config.php')) {
		require("../../config.php");
	} else {
		header("Location: .../../konfiguracja/?option=1");
	}
	
	require("../../functions.php");
	require("../../params.php");	
	require("../../vendor/autoload.php"); 
	require("auth.php");

	use RobThree\Auth\TwoFactorAuth;

	$IdUser = $_SESSION["IdUser"];
	$ip = $_SERVER['REMOTE_ADDR'];
	$status = "";
	$twofa = new TwoFactorAuth($website_name);
	$secret = null;

	$stmt = $con->prepare("SELECT 2fa_secret FROM str_user WHERE IdUser = ?");
	$stmt->bind_param('i', $IdUser);
	$stmt->execute();
	$stmt->bind_result($secret);
	$stmt->fetch();
	$stmt->close();

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		try {
            if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
                die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
            }
			if (isset($_POST['turnon'])) {
				$secret = $twofa->createSecret();
				$_SESSION['2fa_secret'] = $secret;
			} elseif (isset($_POST['verify'])) {
				$code = sanitize_input($_POST['code']);
				$secret = $_SESSION['2fa_secret'];

				if ($twofa->verifyCode($secret, $code)) {
					$stmt = $con->prepare("UPDATE str_user SET 2fa_secret = ? WHERE IdUser = ?");
					$stmt->bind_param('si', $secret, $IdUser);
					if ($stmt->execute()) {
						$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
						$opis = 'Dwuskładnikowe uwierzytelnianie zostało włączone';
						$kolor = 'green';
						$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
						$stmt->execute();
						$status = 
						'<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Sukces!</strong> Dwuskładnikowe uwierzytelnianie zostało włączone.
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>';
					} else {
						$status = '<div class="alert alert-danger">Wystąpił błąd podczas aktualizacji bazy danych.</div>';
					}
					$stmt->close();
				} else {
					$status = '<div class="alert alert-danger">Kod jest nieprawidłowy. Spróbuj ponownie.</div>';
				}
			} elseif (isset(($_POST['turnoff']))) {
				$stmt = $con->prepare("UPDATE str_user SET 2fa_secret = NULL WHERE IdUser = ?");
				$stmt->bind_param('i', $IdUser);
				if ($stmt->execute()) {
					$secret = null;
					unset($_SESSION['2fa_secret']);
					
					$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
					$opis = 'Dwuskładnikowe uwierzytelnianie zostało wyłączone';
					$kolor = 'red';
					$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
					$stmt->execute();
					$status = 
					'<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong>Sukces!</strong> Dwuskładnikowe uwierzytelnianie zostało wyłączone.
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>';
				} else {
					$status = '<div class="alert alert-danger">Wystąpił błąd podczas aktualizacji bazy danych.</div>';
				}
				$stmt->close();
			}
        } catch (Exception $e) {
            $status = '<div class="error"><p>Błąd: ' . $e->getMessage() . '</p></div>';
        }
	}
	$con->close();
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>System | <?= $website_name; ?></title>
		<link rel="apple-touch-icon" href="../../assets/img/favicon.ico">
		<link rel="icon" href="../../assets/img/favicon.ico">
		<link rel="shortcut icon" href="../../assets/img/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
		<link rel="stylesheet" href="../../assets/css/style.css">
	</head>
	<body>
		<?php include("header.php"); ?>
		<div class="container-fluid">
			<div class="row">
				<?php include("navbar.php"); ?>
				<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
					<?php echo "<div>".$status."</div>" ?>
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2">Uwierzytelnianie dwuskładnikowe</h1>
					</div>
					<p>Aby włączyć uwierzytelnianie dwuskładnikowe proszę kliknąć poniższy przycisk.</p>
					<?php if (!$secret){ ?>
						<form method="POST">
							<input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
							<button type="submit" name="turnon" class="btn btn-blue">Włącz</button>
						</form>
					<?php } else { ?>
						<p>Zeskanuj poniższy kod QR w aplikacji uwierzytelniającej (np. Google Authenticator) i wprowadź wygenerowany kod.</p>
						<?php 
							$stmt = $con->prepare("SELECT username FROM str_user WHERE IdUser = ?");
							$stmt->bind_param('i', $IdUser);
							$stmt->execute();
							$stmt->bind_result($twofa_username);
							$stmt->fetch();
							$stmt->close();
						?>
						<img src="<?= $twofa->getQRCodeImageAsDataUri($twofa_username, $secret) ?>" class="img-fluid mb-3" alt="Kod QR">
						<form method="POST">
							<input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
							<div class="form-group">
								<label for="code">Kod 2FA:</label>
								<input type="number" name="code" class="form-control" required>
							</div>
							<button type="submit" name="verify" class="btn btn-blue mt-2">Zweryfikuj</button>
						</form>
						<form method="POST" class="mt-3">
							<input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
							<button type="submit" name="turnoff" class="btn btn-danger">Wyłącz</button>
						</form>
					<?php } ?>
				</main>
			</div>
		</div>
		<footer class="text-muted py-4 text-center">
			<div class="container">
				<p>© 2024 <?= $website_name; ?>. Wszelkie prawa zastrzeżone.</p>
			</div>
		</footer>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	</body>
</html>
