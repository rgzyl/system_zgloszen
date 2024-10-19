<?php
	session_start();

	require("../functions.php");
	require("../params.php");

	if (file_exists('../config.php')) {
		require("../config.php");
	} else {
		header("Location: ../konfiguracja/?option=1");
		exit();  
	}

	$status = "";
	$email = "";

	$number1 = rand(1, 10);
	$number2 = rand(1, 10);
	
	$ip = $_SERVER['REMOTE_ADDR'];

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		try {
			if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
				throw new Exception('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
			}

			$recaptcha_answer = sanitize_input($_POST['number1']) + sanitize_input($_POST['number2']);

			if (sanitize_input($_POST['recaptcha']) != $recaptcha_answer) {
				$status = '<div class="error"><p>* Nieprawidłowa odpowiedź na pytanie zabezpieczające!</p></div>';
			} else {
				$email = sanitize_input($_POST['email']);
				
				$stmt = $con->prepare("SELECT IdUser FROM str_user WHERE email = ?");
				$stmt->bind_param('s', $email);
				$stmt->execute();
				$result = $stmt->get_result();

				if ($result->num_rows > 0) {
					$row = $result->fetch_array();
					$IdUser = $row['IdUser'];

					$token = bin2hex(random_bytes(16));
					$expiry = date('Y-m-d H:i:s', time() + (3 * 60 * 60)); 

					$stmt = $con->prepare("INSERT INTO str_user_tokens (IdUser, token, expiry) VALUES (?, ?, ?)");
					$stmt->bind_param('iss', $IdUser, $token, $expiry);
					$stmt->execute();

					$reset_link = "$website_url/admin/zmiana-hasla?token=$token";
					$subject = "$website_name - prośba o zresetowanie hasła";
$message = "Witaj,
Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta w serwisie '$website_name'. Aby ustawić nowe hasło, kliknij poniższy link:
$reset_link
Jeśli nie prosiłeś(aś) o resetowanie hasła, zignoruj tę wiadomość. Twoje hasło pozostanie bez zmian.
Link do zmiany hasła jest ważny przez 3 godziny.

Pozdrawiamy, $website_name
";

					if (!mail($email, $subject, $message)) {
						throw new Exception('Wystąpił błąd podczas wysyłania wiadomości e-mail.');
					}
					$status = '<div class="success"><p>Link do resetowania hasła został wysłany na Twój adres e-mail.</p></div>';
					
					$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
					$opis = 'Aktywowano URL do przywracania hasła';
					$kolor = 'orange';
					$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
					$stmt->execute();
				} else {
					$status = '<div class="error"><p>* E-mail nie istnieje w systemie!</p></div>';
					
					$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
					$opis = 'Nieudana próba resetowania hasła dla nieistniejącego adresu e-mail';
					$kolor = 'red';
					$IdUser = 1;
					$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
					$stmt->execute();
				}
			}
		} catch (Exception $e) {
			$status = '<div class="error"><p>' . $e->getMessage() . '</p></div>';
		}
	}
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Przywracanie hasła | <?= $website_name; ?></title>
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
			<h1>Przywracanie hasła</h1>
			<form method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">     
				<div class="form-group">
					<label for="email" class="form-label">Adres e-mail</label>
					<input type="email" class="form-control" id="email" name="email" placeholder="Wprowadź swój adres e-mail" required>
					<div class="form-text text-muted">Podaj adres e-mail przypisany do konta.</div>
				</div>
				<div class="form-group mb-3">
					<label for="recaptcha" class="recaptcha-label">Rozwiąż równanie: <strong><?php echo "$number1 + $number2 = ?"; ?></strong></label>
					<input type="hidden" name="number1" value="<?php echo "$number1"; ?>" >
					<input type="hidden" name="number2" value="<?php echo "$number2"; ?>" >
					<input type="text" class="form-control" id="recaptcha" name="recaptcha" placeholder="Podaj odpowiedź" pattern="\d*" required>
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