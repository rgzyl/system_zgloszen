<?php
	if (file_exists('../../config.php')) {
		require("../../config.php");
	} else {
		header("Location: ../../konfiguracja/?option=1");
		exit();
	}
	
	require("../../functions.php");
	require("../../params.php");
	require("auth.php");

	
	$IdUser = $_SESSION["IdUser"];
	$ip = $_SERVER['REMOTE_ADDR'];
	$status = "";

	try {
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
			$old_password = sanitize_input($_POST['old_password']);
			$password = sanitize_input($_POST['password']);
			$re_password = sanitize_input($_POST['re_password']);
			$ip = $_SERVER['REMOTE_ADDR'];
			date_default_timezone_set('Europe/Berlin');
			$data = date('Y-m-d H:i:s');

			$stmt = $con->prepare("SELECT password FROM str_user WHERE IdUser = ?");
			$stmt->bind_param('i', $IdUser);
			$stmt->execute();
			$stmt->bind_result($db_password_hash);
			$stmt->fetch();
			$stmt->close();

			if (password_verify($old_password, $db_password_hash)) {
				if ($password === $re_password) {
					$new_password_hash = password_hash($password, PASSWORD_DEFAULT);

					$stmt = $con->prepare("UPDATE str_user SET password = ? WHERE IdUser = ?");
					$stmt->bind_param('si', $new_password_hash, $IdUser);
					
					if ($stmt->execute()) {
						$status = 
						'<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Sukces!</strong> Hasło zostało zmienione.
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>';

						$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
						$opis = 'Zmieniono hasło do konta użytkownika';
						$kolor = 'green';
						$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
						$stmt->execute();
					} else {
						throw new Exception("Nie udało się zmienić hasła. Spróbuj ponownie.");
					}
				} else {
					throw new Exception("Hasła nie są identyczne.");
				}
			} else {
				throw new Exception("Obecne hasło jest nieprawidłowe.");
			}
		}
	} catch (Exception $e) {
		$status = 
		'<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>Błąd!</strong> ' . $e->getMessage() . '
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';

		$stmt = $con->prepare("INSERT INTO str_user_check (`opis`, `ip`, `data`, `kolor`, `IdUser`) VALUES (?, ?, ?, ?, ?)");
		$opis = $e->getMessage();
		$color = "red";
		$stmt->bind_param('sssii', $opis, $ip, $data, $color, $IdUser);
		$stmt->execute();
	}
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
						<h1 class="h2">Zmień hasło</h1>
					</div>
					<form action="" method="POST">
						<div class="mb-3">
							<label>Podaj obecne hasło:</label>
							<input type="password" name="old_password" class="form-control" required>
						</div>
						<div class="mb-3">
							<label>Podaj nowe hasło:</label>
							<input type="password" name="password" class="form-control" required>
						</div>
						<div class="mb-3">
							<label>Powtórz nowe hasło:</label>
							<input type="password" name="re_password" class="form-control" required>
						</div>
						<div class="d-grid gap-2">
							<button type="submit" name="submit" class="btn btn-blue">Zapisz</button>
						</div>
					</form>
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
