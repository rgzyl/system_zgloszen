<?php
	date_default_timezone_set('Europe/Berlin');
	require("../functions.php");
	require("../params.php");

	if (file_exists('../config.php')) {
		require("../config.php");
	} else {
		header("Location: ../konfiguracja/?option=1");
		exit();
	}

	//require("dashboard/auth.php");
	//$IdUser = $_SESSION["IdUser"];
	
	$status = "";
	$ip = $_SERVER['REMOTE_ADDR'];
	$csrf_token = "";

	try {
		$csrf_token = generate_csrf_token();
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
				throw new Exception('Nieprawidłowy token CSRF. Proszę spróbować ponownie.');
			}

			$name = sanitize_input($_POST["name"]);
			$surname = sanitize_input($_POST["surname"]);
			$username = sanitize_input($_POST["username"]);
			$password = sanitize_input($_POST["password"]);
			$re_password = sanitize_input($_POST["re_password"]);
			$email = sanitize_input($_POST["email"]);

			if (empty($name) || empty($surname) || empty($username) || empty($password) || empty($re_password) || empty($email)) {
				throw new Exception("Wszystkie pola są wymagane!");
			}

			if ($password !== $re_password) {
				throw new Exception("Hasła nie są identyczne!");
			}

			$stmt = $con->prepare("SELECT COUNT(*) FROM str_user WHERE username = ?");
			$stmt->bind_param('s', $username);
			$stmt->execute();
			$stmt->bind_result($user_count);
			$stmt->fetch();
			$stmt->close();

			if ($user_count > 0) {
				throw new Exception("Użytkownik o podanej nazwie już istnieje!");
			}

			$stmt = $con->prepare("SELECT COUNT(*) FROM str_user WHERE email = ?");
			$stmt->bind_param('s', $email);
			$stmt->execute();
			$stmt->bind_result($user_count_email);
			$stmt->fetch();
			$stmt->close();

			if ($user_count_email > 0) {
				throw new Exception("Użytkownik o podanym adresie e-mail już istnieje!");
			}

			$hashed_password = password_hash($password, PASSWORD_DEFAULT);

			$role = 'user';
			$status = 0;

			$stmt = $con->prepare("INSERT INTO str_user (`name`, `surname`, `username`, `password`, `ip`, `email`, `role`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('sssssssi', $name, $surname, $username, $hashed_password, $ip, $email, $role, $status);
			
			if ($stmt->execute()) {
				$status = '<div class="success"><p>Konto zostało utworzone pomyślnie!</p></div>';

				$stmt = $con->prepare("INSERT INTO str_user_check (`opis`, `ip`, `kolor`, `IdUser`) VALUES (?, ?, ?, ?)");
				$opis = "Nowe konto zostało utworzone";
				$color = "green";
				$IdUser = 1; 
				$stmt->bind_param('sssi', $opis, $ip, $color, $IdUser);
				$stmt->execute();
			} else {
				throw new Exception("Nie udało się utworzyć konta. Spróbuj ponownie.");
			}
		}
	} catch (Exception $e) {
		$status = '<div class="error"><p>' . $e->getMessage() . '</p></div>';
		
		$stmt = $con->prepare("INSERT INTO str_user_check (`opis`, `ip`, `kolor`, `IdUser`) VALUES (?, ?, ?, ?)");
		$opis = $e->getMessage();
		$color = "red";
		$IdUser = 1;
		$stmt->bind_param('sssi', $opis, $ip, $color, $IdUser);
		$stmt->execute();
	}
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Utwórz konto | <?= $website_name; ?></title>
		<meta name="description" content="">
		<meta name="keywords" content="<?= $website_keywords; ?>">
		<link rel="apple-touch-icon" href="../assets/img/favicon.ico">
		<link rel="icon" href="../assets/img/favicon.ico">
		<link rel="shortcut icon" href="../assets/img/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="../assets/css/forms.css" rel="stylesheet">
	</head>
	<body>
		<div class="form-container">
			<img class="form-img" src="../assets/img/logo.png">
			<h1>Utwórz konto</h1>
			<form method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">     
				<div class="form-group mb-3">
					<input type="text" class="form-control" id="username" name="username" placeholder="Podaj nazwę użytkownika" required>
				</div>
				<div class="form-group mb-3">
					<input type="password" class="form-control" id="password" name="password" placeholder="Podaj hasło użytkownika" required>
				</div>
				<div class="form-group mb-3">
					<input type="password" class="form-control" id="re_password" name="re_password" placeholder="Powtórz hasło użytkownika" required>
				</div>
				<div class="form-group mb-3">
					<input type="text" class="form-control" id="name" name="name" placeholder="Podaj swoje imię" required>
				</div>
				<div class="form-group mb-3">
					<input type="text" class="form-control" id="surname" name="surname" placeholder="Podaj swoje nazwisko" required>
				</div>
				<div class="form-group mb-3">
					<input type="email" class="form-control" id="email" name="email" placeholder="Podaj swój adres e-mail" required>
				</div>
				<button type="submit" class="btn btn-primary">ZAPISZ</button>
				<div class="status"><?php echo $status; ?></div>
				<div class="form-footer">
					<p>Wróć do <a href="<?= $website_path; ?>admin/">panelu logowania</a></p>
					<!--<p>Wróć do <a href="dashboard/konta.php">panelu administratora</a></p>-->
				</div>
			</form>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
