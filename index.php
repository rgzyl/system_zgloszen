<?php 
	session_start();
	if(file_exists('config.php')){
		require("config.php");
	} else {
		header("Location: konfiguracja/?option=1");
	}
	
	require("params.php");
	require("functions.php");
	
	$stmt = $con->prepare('SELECT status FROM str_config WHERE IdConfig = 1');
	$stmt->execute();
	$stmt->bind_result($status);
	$stmt->fetch();
	$stmt->close();

	$isLogged = isset($_SESSION['IdUser']) && $_SESSION['IdUser'] == true;

	if($status != 1 && !$isLogged){
		header("Location: status/");
		exit();
	}

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
			if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
				throw new Exception('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
			}

            $imie = isset($_POST['imie']) ? sanitize_input($_POST['imie']) : '';
            $nazwisko = isset($_POST['nazwisko']) ? sanitize_input($_POST['nazwisko']) : '';
            $adres = isset($_POST['adres']) ? sanitize_input($_POST['adres']) : '';
            $kod = isset($_POST['kod']) ? sanitize_input($_POST['kod']) : '';
            $miejscowosc = isset($_POST['miejscowosc']) ? sanitize_input($_POST['miejscowosc']) : '';
            $telefon = isset($_POST['telefon']) ? sanitize_input($_POST['telefon']) : '';
            $email = isset($_POST['email']) ? filter_var(sanitize_input($_POST['email']), FILTER_VALIDATE_EMAIL) : '';
            $opis = isset($_POST['opis']) ? sanitize_input($_POST['opis']) : '';
            $IdWies = isset($_POST['miejscowosc']) ? sanitize_input($_POST['miejscowosc']) : '';

            if (!$email) {
                throw new Exception("Błędny adres email.");
            }

            $ip = $_SERVER['REMOTE_ADDR']; 
            $data = date("Y-m-d H:i:s"); 
            $uploadedFiles = $_FILES['photos'];
            $uploadedFileNames = [];

			$stmt = $con->prepare("INSERT INTO str_zgloszenie (imie, nazwisko, adres, kod, IdWies, telefon, mail, opis, ip, stat) 
								   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

			if (!$stmt) {
				throw new Exception("Błąd w przygotowaniu zapytania: " . $con->error);
			}

			$stmt->bind_param('sssssssss', $imie, $nazwisko, $adres, $kod, $IdWies, $telefon, $email, $opis, $ip);

			if (!$stmt->execute()) {
				throw new Exception("Błąd wykonania zapytania: " . $stmt->error);
			}

			$last_id = $stmt->insert_id;
			$stmt->close();

            if (!empty($uploadedFiles['name'][0])) { 
                $uploadDirectory = 'uploads/';
                foreach ($uploadedFiles['name'] as $key => $fileName) {
                    $fileTmp = $uploadedFiles['tmp_name'][$key];
                    $fileSize = $uploadedFiles['size'][$key];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $validTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($fileExt, $validTypes) && $fileSize <= 3 * 1024 * 1024) { 
                        $newFileName = uniqid() . '.' . $fileExt;
                        $filePath = $uploadDirectory . $newFileName;

                        if (!move_uploaded_file($fileTmp, $filePath)) {
                            throw new Exception("Błąd podczas przesyłania pliku: " . $fileName);
                        }

                        $uploadedFileNames[] = $newFileName;

                        $stmt = $con->prepare("INSERT INTO str_zdjecia (nazwa, IdZgloszenie) VALUES (?, ?)");
                        if (!$stmt) {
                            throw new Exception("Błąd przygotowania zapytania dla plików: " . $con->error);
                        }
                        $stmt->bind_param('si', $newFileName, $last_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Błąd wykonania zapytania dla plików: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                }
            }

            $subject = "$website_name - zgłoszenie szkody";
            $message = "Dziękujemy za zgłoszenie szkody powstałej w wyniku powodzi.";
            if (!mail($email, $subject, $message)) {
                throw new Exception("Błąd podczas wysyłania wiadomości e-mail.");
            }

            header("Location: ?status=ok");

        } catch (Exception $e) {
            error_log($e->getMessage()); 
            echo '<div class="alert alert-danger">Wystąpił błąd: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
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
		<link href="assets/css/styles.css" rel="stylesheet">
		<link rel="shortcut icon" href="assets/img/favicon.ico">
		<link rel="icon" href="assets/img/favicon.ico">
		<link rel="apple-touch-icon" href="assets/img/favicon.ico">
	</head>
	<body>
		<header class="navbar navbar-expand-lg navbar-light shadow-sm">
			<div class="container-fluid">
				<a class="navbar-brand" href="<?= $website_path; ?>"><img src="assets/img/logo.png" alt="Logo" width="40" height="40" class="d-inline-block">  <?= $website_name; ?></a>
				<ul class="navbar-nav ms-auto">
					<?php if ($isLogged) { ?>
						<li class="nav-item">
							<a class="nav-link" href="admin/dashboard/index.php" role="button"><i class="bi bi-box-arrow-in-up-left"></i></a>
						</li>
					<?php } ?>	
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="<?= $website_path; ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-list"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item item" href="<?= $website_path; ?>kontakt/"><i class="bi bi-envelope"></i> Kontakt</a></li>
							<li><a class="dropdown-item item" href="<?= $website_path; ?>polityka-prywatnosci/"><i class="bi bi-shield-lock"></i> Polityka prywatności</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</header>
<?php 
	if(isset($_GET['status'])){
		if($_GET['status'] == "ok")
		{
?>

    <div class="container mt-5">
        <div class="card text-center" style="">
            <div class="card-body">
                <h4 class="card-title" style="font-weight: 600; color: #070D1F;">Formularz został przesłany!</h4>
                <div class="d-flex justify-content-center mt-3 mb-3">
                    <i class="bi bi-check-circle" style="font-size: 4rem; color: #070D1F;"></i>
                </div>
                <p style="font-size: 1rem; line-height: 1.5; color: #333;">Dziękujemy za zgłoszenie. Twoje zgłoszenie zostało pomyślnie przesłane i zostanie zweryfikowane przez Ośrodek Pomocy Społecznej w Strzegomiu.</p>
                <hr class="custom-hr-s">
                <p class="mb-0" style="font-size: 1rem; color: #333;">Jeśli masz dodatkowe pytania, skontaktuj się z nami:</p>
                <p style="font-size: 1rem; color: #333;">
                    Telefon: <a href="tel:+48746477180" style="color: #070D1F;">+48 74 647 71 80</a><br>
                    E-mail: <a href="mailto:opsstrzegom@poczta.wp.pl" style="color: #070D1F;">opsstrzegom@poczta.wp.pl</a>
                </p>
            </div>
        </div>
    </div>
	
<?php
		} elseif($_GET['status'] == "error") {
?>

    <div class="container mt-5">
        <div class="card text-center" style="">
            <div class="card-body">
                <h4 class="card-title" style="font-weight: 600; color: #070D1F;">Formularz nie został przesłany!</h4>
                <div class="d-flex justify-content-center mt-3 mb-3">
                    <i class="bi bi bi-x-circle" style="font-size: 4rem; color: #070D1F;"></i>
                </div>
                <p style="font-size: 1rem; line-height: 1.5; color: #333;">Coś poszło nie tak. Proszę spróbować wypełnić formularz ponownie! W razie dalszych problemów prosimy o kontakt mailowy w celu zgłoszenia awarii (<a style="color: #070D1F;" href="mailto:awaria@zglos.strzegom.pl">awaria@zglos.strzegom.pl</a>)</p>
            </div>
        </div>
    </div>

<?php
		}
	} else {
?>
		<h3 class="text-center mt-5" style="font-weight:800; color:#070D1F;">ZGŁOŚ SZKODY PO POWODZI</h3>
		<div class="container" style="margin-top:5px;">
			<div class="progress mt-3 mb-3" style="height: 30px;">
				<div class="progress-bar" role="progressbar" aria-label="Example with label" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
			<div class="card">
				<div class="col-12">
					<div class="">
						<form method="POST" enctype="multipart/form-data">
							<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">     
							<div class="step">
								<h5 style="font-weight:800;">Krok 1: Przeczytaj</h5>
								<hr class="custom-hr" />
								<p>Aby zgłosić szkodę w budynku/lokalu mieszkalny z terenu Gminy Strzegom wypełnij formularz.</p>
								<p style="color:red; font-weight:bold;">Osoby, które zgłosiły się do OPS Strzegom telefonicznie lub osobiście, prosimy o niewypełnianie formularza. Informacje zostały już zarejestrowane.</p>
							</div>
							<div id="userinfo" class="step" style="display: none">
								<h5 style="font-weight:800;">Krok 2: Uzupełnij dane personalne</h5>
								<hr class="custom-hr" />  
								<div class="form-row">
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="imie" class="form-label">Imię</label>
											<input type="text" name="imie" id="imie" class="form-control" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
										<div class="col-md-6 mb-3">
											<label for="nazwisko" class="form-label">Nazwisko</label>
											<input type="text" name="nazwisko" id="nazwisko" class="form-control" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="adres" class="form-label">Adres</label>
											<input type="text" name="adres" id="adres" class="form-control" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
										<div class="col-md-6 mb-3">
											<label for="kod" class="form-label">Kod pocztowy</label>
											<input type="text" name="kod" id="kod" class="form-control" value="58-150" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="miejscowosc" class="form-label">Miejscowość</label>
											<select name="miejscowosc" id="miejscowosc" class="form-select" aria-label="">
												<option value="1" selected>Strzegom</option>
												<?php 
													$stmt = $con->prepare("SELECT IdWies, nazwa FROM str_wies WHERE IdWies != ? ORDER BY nazwa ASC");
													$IdWies = 1;
													$stmt->bind_param('i', $IdWies);
													$stmt->execute();
													$result = $stmt->get_result();
													
													while ($row = $result->fetch_assoc()) {
												?>
													<option value="<?= htmlspecialchars($row['IdWies']); ?>">
														<?= htmlspecialchars($row['nazwa']); ?>
													</option>
												<?php 
													}
													$stmt->close(); 
												?>
											</select>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
										<div class="col-md-6">
											<label for="telefon" class="form-label">Numer telefonu</label>
											<input type="text" name="telefon" id="telefon" class="form-control" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="email" class="form-label">E-mail</label>
											<input type="email" name="email" id="email" class="form-control" required>
											<div class="invalid-feedback">To pole jest wymagane</div>
										</div>
									</div>
								</div>
							</div>
							<div id="photos" class="step" style="display: none">
								<div id="error-message"></div>
								<h5 style="font-weight:800;">Krok 3: Dodaj zdjęcia (nie wymagane)</h5>
								<hr class="custom-hr" />

								<div class="form-group">
									<label for="photo-upload" class="form-label">Wybierz maksymalnie 5 zdjęć (jpg, png, gif, max 3MB każde)</label>
									<input type="file" id="photo-upload" name="photos[]" class="form-control" accept=".jpg, .jpeg, .png, .gif" multiple >
									<div class="invalid-feedback">Proszę wybrać zdjęcia.</div>
								</div>
								<ul id="photo-list" class="list-group mt-3 mb-3"></ul>
							</div>
							<div id="description" class="step" style="display: none">
								<h5 style="font-weight:800;">Krok 4: Opisz szkody</h5>
								<hr class="custom-hr" />
								<div class="mb-3">
									<label for="opis" name="opis" class="form-label">Wprowadź opis szkód, które wystąpiły w wyniku powodzi.</label>
									<textarea class="form-control" name="opis" id="opis" rows="3" required></textarea>
									<div class="invalid-feedback">To pole jest wymagane</div>
								</div>
							</div>
							<div id="finalstep" class="step" style="display:none;">
								<h5 style="font-weight:800;">Krok 5: Zakończenie</h5>
								<hr class="custom-hr" />
								<p>Otrzymane zgłoszenie zostanie przekazane do Ośrodka Pomocy Społecznej w Strzegomiu, celem zweryfikowania powstałych szkód.</p>
								<div class="d-grid gap-2">
									<button class="action submit btn btn-success mb-3" style="">WYŚLIJ ZGŁOSZENIE</button>
								</div>
							</div>
						</form>
						<div>
							<button class="action back btn btn-blue" style="display: none">COFNIJ</button>
							<button class="action next btn btn-blue float-end">DALEJ</button>
							<button class="action submit btn btn-success float-end" style="display: none" hidden>ZAPISZ</button>
						</div>
						<div id="overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;">
							<div class="d-flex justify-content-center align-items-center" style="height: 100%;">
								<div class="spinner-border text-light" role="status">
									<span class="sr-only"></span>
								</div>
							</div>	
						</div>	
					</div>
				</div>
			</div>
		</div>
		<div class="footer">
			<p>&copy; <?= date('Y').' '.$website_name; ?>. Wszelkie prawa zastrzeżone.</p>
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
		<script src="assets/js/main.js"></script> 
		<script src="assets/js/photos.js"></script> 
	</body>
</html>
<?php } ?>