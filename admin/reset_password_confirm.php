<?php
	require("../functions.php");
	require("../params.php");

	if (file_exists('../config.php')) {
		require("../config.php");
	} else {
		header("Location: ../konfiguracja/?option=1");
		exit();
	}

	$status = "";
	$new_password = "";
	$re_password = "";
	$token = "";
	
	$ip = $_SERVER['REMOTE_ADDR'];

	if (isset($_GET['token'])) {
		$token = sanitize_input($_GET['token']);

		try {
			$stmt = $con->prepare("SELECT IdUser FROM str_user_tokens WHERE token = ? AND expiry > NOW()");
			$stmt->bind_param('s', $token);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($result->num_rows === 0) {
				$status = '<div class="error"><p>* Token jest nieprawidłowy lub wygasł.</p></div>';
			} else {
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
					if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
						throw new Exception('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
					}
			
					$new_password = sanitize_input($_POST['new_password']);
					$re_password = sanitize_input($_POST['re_password']);

					if (empty($new_password) || empty($re_password)) {
						$status = '<div class="error"><p>* Oba pola hasła są wymagane.</p></div>';
					} 
					elseif ($new_password !== $re_password) {
						$status = '<div class="error"><p>* Hasła nie są zgodne!</p></div>';
					} 
					else {
						$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
						$row = $result->fetch_array();
						$IdUser = $row['IdUser'];
						try {
							$stmt = $con->prepare("UPDATE str_user SET password = ? WHERE IdUser = ?");
							$stmt->bind_param('si', $hashed_password, $IdUser);

							if ($stmt->execute()) {
								$stmt = $con->prepare("DELETE FROM str_user_tokens WHERE token = ?");
								$stmt->bind_param('s', $token);
								$stmt->execute();

								$status = '<div class="success"><p>Hasło zostało zaktualizowane pomyślnie!</p></div>';
								
								$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
								$opis = 'Zmieniono hasło do konta użytkownika z URL';
								$kolor = 'green';
								$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
								$stmt->execute();
							} else {
								throw new Exception('Wystąpił problem podczas aktualizacji hasła.');
							}
						} catch (Exception $e) {
							$status = '<div class="error"><p>' . $e->getMessage() . '</p></div>';
						}
					}
				}
			}
		} catch (Exception $e) {
			$status = '<div class="error"><p>Wystąpił błąd: ' . $e->getMessage() . '</p></div>';
		}
	} else {
		$status = '<div class="error"><p>* Brak tokenu.</p></div>';
	}
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Zmiana hasła | <?= $website_name; ?></title>
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
			<h1>Zmiana hasła</h1>
			<form method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">     
				<div class="form-group mb-3">
					<input type="password" class="form-control" id="new_password" name="new_password" placeholder="Podaj nowe hasło" required>
				</div>
				<div class="form-group mb-3">
					<input type="password" class="form-control" id="re_password" name="re_password" placeholder="Powtórz nowe hasło" required>
				</div>
				<button type="submit" class="btn btn-primary">DALEJ</button>
				<div class="status"><?php echo $status; ?></div>
				<div class="form-footer">
					<p>Wróć do <a href="<?= $website_path; ?>admin/">panelu logowania</a></p>
				</div>
			</form>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
