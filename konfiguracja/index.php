<?php
	require('../params.php');
	require('../functions.php');

	if (!file_exists('../config.php')) {
		$error = ''; 
?>
<?php
    function db_connect($hostname, $username, $password, $database) {
        $con = new mysqli($hostname, $username, $password, $database);

        if ($con->connect_error) {
            throw new Exception('Błąd połączenia z bazą danych: ' . $con->connect_error);
        }

        if (!$con->set_charset("utf8")) {
            throw new Exception('Nie można ustawić kodowania znaków: ' . $con->error);
        }

        return $con;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
        }

        $step = isset($_GET['option']) ? sanitize_input($_GET['option']) : '1';

        if ($step == '1') {
            $hostname = sanitize_input($_POST['hostname']);
            $username = sanitize_input($_POST['username']);
            $password = sanitize_input($_POST['password']);
            $database = sanitize_input($_POST['database']);

            if (empty($hostname) || empty($username) || empty($database)) {
                $error = "Wszystkie pola (oprócz hasła) muszą być wypełnione.";
            } else {
                try {
                    $con = db_connect($hostname, $username, $password, $database);
                    header("Location: ?option=2&hostname=" . urlencode($hostname) . "&username=" . urlencode($username) . "&password=" . urlencode($password) . "&database=" . urlencode($database));
                    exit();
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        if ($step == '2') {
            $hostname = sanitize_input($_GET['hostname']);
            $username = sanitize_input($_GET['username']);
            $password = sanitize_input($_GET['password']);
            $database = sanitize_input($_GET['database']);

            try {
                $con = db_connect($hostname, $username, $password, $database);

                $sql_file = '../sql/dump.sql';

                if (file_exists($sql_file)) {
                    $sql = file_get_contents($sql_file);
                    $queries = explode(';', $sql);
                    $error = false;

                    $con->begin_transaction(); 
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if (!empty($query)) {
                            if (!$con->query($query)) {
                                $con->rollback();
                                throw new Exception("Błąd przy wykonywaniu zapytania: " . $con->error);
                            }
                        }
                    }
                    $con->commit();
                    header("Location: ?option=3&hostname=" . urlencode($hostname) . "&username=" . urlencode($username) . "&password=" . urlencode($password) . "&database=" . urlencode($database));
                    exit();
                } else {
                    throw new Exception("Plik dump.sql nie został znaleziony.");
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if ($step == '3') {
            $hostname = sanitize_input($_GET['hostname']);
            $username = sanitize_input($_GET['username']);
            $password = sanitize_input($_GET['password']);
            $database = sanitize_input($_GET['database']);

            $db_username = sanitize_input($_POST['db_username']);
            $db_password = sanitize_input($_POST['db_password']);
            $db_name = sanitize_input($_POST['db_name']);
            $db_surname = sanitize_input($_POST['db_surname']);
			$db_email = sanitize_input($_POST['db_email']);
            $db_data = date("Y-m-d H:i:s");
            $db_ip = sanitize_input($_SERVER['REMOTE_ADDR']);

            if (empty($db_username) || empty($db_password) || empty($db_name) || empty($db_surname)) {
                $error = "Wszystkie pola muszą być wypełnione.";
            } else {
                try {
                    $con = db_connect($hostname, $username, $password, $database);

                    $hashed_password = password_hash($db_password, PASSWORD_BCRYPT);

                    $stmt = $con->prepare("INSERT INTO str_user (username, password, name, surname, email, ip, data, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', '0')");
                    $stmt->bind_param("sssssss", $db_username, $hashed_password, $db_name, $db_surname, $db_email, $db_ip, $db_data);

                    if ($stmt->execute()) {
                        header("Location: ?option=4&hostname=" . urlencode($hostname) . "&username=" . urlencode($username) . "&password=" . urlencode($password) . "&database=" . urlencode($database));
                        exit();
                    } else {
                        throw new Exception("Błąd przy dodawaniu użytkownika: " . $stmt->error);
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        if ($step == '4') {
            $hostname = sanitize_input($_GET['hostname']);
            $username = sanitize_input($_GET['username']);
            $password = sanitize_input($_GET['password']);
            $database = sanitize_input($_GET['database']);

            try {
                $content = "<?php\n";
                $content .= "\t\$hostname = '" . sanitize_input($hostname) . "';\n";
                $content .= "\t\$username = '" . sanitize_input($username) . "';\n";
                $content .= "\t\$password = '" . sanitize_input($password) . "';\n";
                $content .= "\t\$database = '" . sanitize_input($database) . "';\n\n";
                $content .= "\t\$con = mysqli_connect(\"\$hostname\",\"\$username\",\"\$password\",\"\$database\");\n\n";
                $content .= "\tif (!mysqli_set_charset(\$con, \"utf8mb4\")) {\n";
                $content .= "\t    mysqli_error(\$con);\n";
                $content .= "\t    exit();\n";
                $content .= "\t} else {\n";
                $content .= "\t    return \$con;\n";
                $content .= "\t}\n";
                $content .= "?>";

                file_put_contents("../config.php", $content);
                header("Location: $website_path"."admin");
            } catch (Exception $e) {
                $error = "Nie udało się zapisać pliku konfiguracyjnego: " . $e->getMessage();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Panel konfiguracyjny | <?= $website_name; ?></title>
		<meta name="description" content="">
		<meta name="keywords" content="<?= $website_keywords; ?>">
		<link rel="apple-touch-icon" href="../assets/img/favicon.ico">
		<link rel="icon" href="../assets/img/favicon.ico">
		<link rel="shortcut icon" href="../assets/img/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
			@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap");
			
			html, body {
				height: 100%;
				margin: 0;
			}

			.container {
				flex: 1; 
			}
			
			body {
				display: flex;
				flex-direction: column;
				background-color: #f5f5f5;
				font-family: 'Poppins', sans-serif;
				margin: 0;
				padding: 0;
			}
			.header, .footer {
				background-color: #070D1F;
				color: white;
				text-align: center;
				padding: 20px 0;
			}
			.header h1, .footer p {
				margin: 0;
			}
			.header h1 {
				font-size: 2.5rem;
			}
			.footer p {
				font-size: 0.875rem;
			}
			.container {
				max-width: 700px;
				margin: 50px auto;
			}
			.card {
				padding: 30px;
				border-radius: 10px;
				box-shadow: 0 6px 12px rgba(0,0,0,0.1);
				background-color: white;
			}
			.form-label {
				font-weight: 600;
			}
			.btn-primary {
				background-color: #007bff;
				border: none;
				border-radius: 5px;
				padding: 12px 20px;
				font-weight: 600;
			}
			.btn-primary:hover {
				background-color: #0056b3;
			}
			.alert {
				border-radius: 5px;
			}
			h2 {
				font-size: 2rem;
				margin-bottom: 20px;
				font-weight: 600;
			}
			p {
				font-size: 1rem;
				line-height: 1.5;
			}
			
			.btn-blue {
				color:#ffffff; 
				background-color:#070D1F; 
				border:#070D1F;		
			}
			
			.btn-blue:hover {
				background-color: #3664F4;
				color: #ffffff;
				border: #3664F4;
			}

			hr.custom-hr {
				border: 0;
				height: 3px;
				background-color: #070D1F;
				opacity: 1;
				width: 15%;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<h1>PANEL KONFIGURACYJNY</h1>
		</div>
		<div class="container">
			<div class="card">
				<?php if (!empty($error)): ?>
					<div class="alert alert-danger" role="alert">
						<?php echo htmlspecialchars($error); ?>
					</div>
				<?php endif; ?>
				<?php 
					if(isset($_GET['option'])){
						if($_GET['option'] == "1"){
				?>
				<h5 style="font-weight:800;">Krok 1: Połączenie z bazą danych</h5>
				<hr class="custom-hr" />
				<form method="POST" action="">
					<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
					<div class="mb-3">
						<label for="hostname" class="form-label">Nazwa hosta (hostname)</label>
						<input type="text" class="form-control" id="hostname" name="hostname" required placeholder="np. localhost" value="<?php echo htmlspecialchars(isset($hostname) ? $hostname : ''); ?>">
						<small class="form-text text-muted">Podaj adres serwera bazy danych, zazwyczaj `localhost`.</small>
					</div>
					<div class="mb-3">
						<label for="username" class="form-label">Nazwa użytkownika (username)</label>
						<input type="text" class="form-control" id="username" name="username" required placeholder="np. root" value="<?php echo htmlspecialchars(isset($username) ? $username : ''); ?>">
						<small class="form-text text-muted">Podaj nazwę użytkownika do bazy danych.</small>
					</div>
					<div class="mb-3">
						<label for="password" class="form-label">Hasło (password)</label>
						<input type="password" class="form-control" id="password" name="password" placeholder="Twoje hasło">
						<small class="form-text text-muted">Podaj hasło do bazy danych. Jeśli nie ma hasła, pozostaw pole puste.</small>
					</div>
					<div class="mb-3">
						<label for="database" class="form-label">Nazwa bazy danych (database)</label>
						<input type="text" class="form-control" id="database" name="database" required placeholder="np. employee" value="<?php echo htmlspecialchars(isset($database) ? $database : ''); ?>">
						<small class="form-text text-muted">Podaj nazwę bazy danych, którą chcesz skonfigurować.</small>
					</div>
					<button type="submit" class="btn btn-blue">DALEJ</button>
				</form>
				<?php
						} elseif($_GET['option'] == '2'){
				?>
				<h5 style="font-weight:800;">Krok 2: Utwórz bazę danych</h5>
				<hr class="custom-hr" />
				<p>W tym kroku zostanie utworzona baza danych, która będzie przechowywać wszystkie niezbędne informacje do prawidłowego działania twojej aplikacji internetowej. Proces jest szybki i zautomatyzowany. Wystarczy kliknąć poniższy przycisk, a system zajmie się resztą.</p>
				<form method="POST" action="">
					<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
					<button type="submit" class="btn btn-blue">DALEJ</button>
				</form>
				<?php
						} elseif($_GET['option'] == '3'){ 
				?>
				<h5 style="font-weight:800;">Krok 3: Dodaj użytkownika do bazy danych</h5>
				<hr class="custom-hr" />
				<form method="POST" action="">
					<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
					<div class="mb-3">
						<label for="db_username" class="form-label">Nazwa użytkownika</label>
						<input type="text" class="form-control" name="db_username" required placeholder="np. admin">
						<small class="form-text text-muted">Wprowadź nazwę użytkownika, którą chcesz dodać do bazy danych.</small>
					</div>
					<div class="mb-3">
						<label for="db_password" class="form-label">Hasło użytkownika</label>
						<input type="password" class="form-control" name="db_password" required placeholder="Twoje hasło">
						<small class="form-text text-muted">Wprowadź hasło, które będzie przypisane do tego użytkownika.</small>
					</div>
					<div class="mb-3">
						<label for="db_name" class="form-label">Imię użytkownika</label>
						<input type="text" class="form-control" name="db_name" required placeholder="np. Jan">
						<small class="form-text text-muted">Podaj imię użytkownika, które będzie zapisane w bazie danych.</small>
					</div>
					<div class="mb-3">
						<label for="db_surname" class="form-label">Nazwisko użytkownika</label>
						<input type="text" class="form-control" name="db_surname" required placeholder="np. Kowalski">
						<small class="form-text text-muted">Podaj nazwisko użytkownika, które będzie zapisane w bazie danych.</small>
					</div>
					<div class="mb-3">
						<label for="db_email" class="form-label">Adres e-mail użytkownika</label>
						<input type="text" class="form-control" name="db_email" required placeholder="np. jan.kowalski@domena.pl">
						<small class="form-text text-muted">Podaj adres e-mail użytkownika, który będzie zapisany w bazie danych.</small>
					</div>
					<button type="submit" class="btn btn-blue">DODAJ</button>
				</form>
				<?php
						} elseif($_GET['option'] == '4'){ 

				?>
				<h5 style="font-weight:800;">Krok 4: Zakończenie konfiguracji</h5>
				<hr class="custom-hr" />
				<p>Gratulacje! Pomyślnie zakończyłeś proces konfiguracji swojej aplikacji internetowej. Twoja baza danych została utworzona, a użytkownik bazy danych został dodany. Teraz możesz rozpocząć korzystanie z aplikacji internetowej.</p>
				<p>W celu zakończenia konfiguracji kliknij przycisk poniżej, aby przejść do panelu administracyjnego i zacząć zarządzać systemem.</p>
				<form method="POST" action="">
					<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-blue">Przejdź do panelu administracyjnego</button>
					</div>
				</form>
				<?php
						} else {
							echo '<h5 style="font-weight:800; margin:0;">Błąd konfiguracji</h5>';
						}
					} else {
						echo '<h5 style="font-weight:800; margin:0;">Błąd konfiguracji</h5>';
					}
				?>
			</div>
		</div>
		<div class="footer">
			<p>&copy; <?= date('Y').' '.$website_name; ?>. Wszelkie prawa zastrzeżone.</p>
		</div>
		<div id="overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
			<div class="d-flex justify-content-center align-items-center" style="height: 100%;">
				<div class="spinner-border text-light" role="status">
					<span class="sr-only"></span>
				</div>
			</div>	
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'></script>
		<script>
			$(document).ready(function() {
				$('form').on('submit', function() {
					$('#overlay').show();
					return true;
				});
			});
		</script>
	</body>
</html>
<?php
	} else {
		header("Location: $website_path");
	}
?>
