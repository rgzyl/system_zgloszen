<?php
    require("../functions.php");
    require("../params.php");

    if (file_exists('../config.php')) {
        require("../config.php");
    } else {
        header("Location: ../konfiguracja/?option=1");
    }

    date_default_timezone_set('Europe/Berlin');
    session_start();

    //ini_set('session.cookie_lifetime', 86400 * 30);

    if (isset($_SESSION["IdUser"])) {
        header("Location: dashboard/index.php");
        exit();
    }

    if (!isset($_SESSION["IdUser"]) && isset($_COOKIE['rememberme'])) {
        list($userId, $token) = explode(':', $_COOKIE['rememberme']);
        $userId = $con->real_escape_string($userId);
        $token = $con->real_escape_string($token);

        $stmt = $con->prepare("SELECT * FROM str_user_tokens WHERE IdUser = ? AND token = ? AND expiry > NOW()");
        $stmt->bind_param('is', $userId, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_array()) {
            $_SESSION["IdUser"] = $userId;
            $_SESSION["2fa_verified"] = true; 
            header("Location: dashboard/index.php");
            exit();
        } else {
            setcookie('rememberme', '', time() - 3600, '/');
        }
    }

    $status = "";
    $ip = $_SERVER['REMOTE_ADDR'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
                die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
            }

            $username = sanitize_input($_POST["username"]);
            $password = sanitize_input($_POST["password"]);

            $stmt = $con->prepare("SELECT attempt, data_attempt FROM str_user WHERE BINARY username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result_attempts = $stmt->get_result();
            $row_attempts = $result_attempts->fetch_array();

            if ($row_attempts) {
                $attempts = $row_attempts['attempt'];
                $last_attempt_time = $row_attempts['data_attempt'];

                if ($attempts >= 5 && strtotime($last_attempt_time) > strtotime('-3 hours')) {
                    $status = '<div class="error"><p>* Konto zablokowane na 3 godziny! Spróbuj ponownie później.</p></div>';
                } else {
                    $stmt = $con->prepare("SELECT * FROM str_user WHERE username = ?");
                    $stmt->bind_param('s', $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_array();

                    if ($row && password_verify($password, $row['password'])) {
                        $IdUser = $row["IdUser"];
                        $_SESSION["IdUser"] = $IdUser;

                        $stmt = $con->prepare("UPDATE str_user SET attempt = 0 WHERE IdUser = ?");
                        $stmt->bind_param('i', $IdUser);
                        $stmt->execute();

                        if (isset($_POST['remember'])) {
                            $token = bin2hex(random_bytes(16));
                            $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));

                            setcookie('rememberme', "$IdUser:$token", time() + (86400 * 30), "/", "", true, true);

                            $stmt = $con->prepare("INSERT INTO str_user_tokens (IdUser, token, expiry) VALUES (?, ?, ?)");
                            $stmt->bind_param('iss', $IdUser, $token, $expiry);
                            $stmt->execute();
                        }

                        $stmt = $con->prepare("SELECT 2fa_secret FROM str_user WHERE IdUser = ?");
                        $stmt->bind_param('i', $IdUser);
                        $stmt->execute();
                        $result_2fa = $stmt->get_result();
                        $row_2fa = $result_2fa->fetch_array();

                        if (!empty($row_2fa['2fa_secret'])) {
                            header("Location: 2fa.php");
                            exit();
                        } else {
                            $_SESSION['2fa_verified'] = true; 
                            header("Location: dashboard/index.php");
                            exit();
                        }
                    } else {
                        $IdUser = $row['IdUser'];
                        $status = '<div class="error"><p>* Nazwa użytkownika lub hasło jest nieprawidłowe!</p></div>';

                        $stmt = $con->prepare("UPDATE str_user SET attempt = attempt + 1, data_attempt = NOW() WHERE IdUser = ?");
                        $stmt->bind_param('i', $IdUser);
                        $stmt->execute();

                        $stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
                        $opis = 'Nieudana próba logowania - błędne hasło';
                        $kolor = 'red';
                        $stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
                        $stmt->execute();
                    }
                }
            } else {
                $status = '<div class="error"><p>* Nazwa użytkownika lub hasło jest nieprawidłowe!</p></div>';

                $stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
                $opis = 'Nieudana próba logowania - nieznany użytkownik';
                $kolor = 'red';
                $IdUser = 1;
                $stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
                $stmt->execute();
            }
        } catch (Exception $e) {
            $status = '<div class="error"><p>Błąd: ' . $e->getMessage() . '</p></div>';
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Panel logowania | <?= $website_name; ?></title>
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
			<h1>Panel logowania</h1>
			<form method="POST">
				<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">     
				<div class="form-group mb-3">
					<input type="text" class="form-control" id="username" name="username" placeholder="Nazwa użytkownika" required>
				</div>
				<div class="form-group mb-3">
					<input type="password" class="form-control" id="password" name="password" placeholder="Hasło" required>
					<div class="form-text text-muted" style="text-align: right;"><a style="text-decoration: none; color: inherit;" href="<?= $website_path; ?>admin/przywracanie-hasla">Nie pamiętam hasła</a></div>
				</div>
				<input type="checkbox" name="remember" id="remember" checked hidden>
				<button type="submit" class="btn btn-primary">ZALOGUJ SIĘ</button>
				<div class="status"><?php echo $status; ?></div>
				<div class="form-footer">
					<p>Nie masz konta? <a href="<?= $website_path; ?>admin/utworz-konto">Utwórz je teraz!</a></p>
					<p>Wróć do <a href="<?= $website_path; ?>">strony głównej</a></p>
				</div>
			</form>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>