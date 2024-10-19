<?php 
	if  (file_exists('../../config.php')) {
		require("../../config.php");
	} else {
		header("Location: .../../konfiguracja/?option=1");
	}
	
    require("../../functions.php");
    require("../../params.php");
	require("auth.php");
	
	$IdUser = $_SESSION["IdUser"];
	$status = "";
	$IdConfig = 1;
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$id_strony = sanitize_input($_POST['status_strony']);
		$opis_strony = sanitize_input($_POST['opis_strony']);
		
		$array_map = [
			1 => 'Opublikowana strona',
			2 => 'Aktualizacja strony',
			3 => 'Tymczasowo zamknięte',
			4 => 'Chwilowa awaria',
			5 => 'Prace techniczne',
			6 => 'Zmiana funkcji strony',
			7 => 'Przerwa techniczna'
		];
		
		$nazwa_strony = $array_map[$id_strony];

		try {
			if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
				throw new Exception('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
			}
			
			$IdConfig = 1;
			$stmt = $con->prepare("UPDATE str_config SET nazwa = ?, opis = ?, status = ? WHERE IdConfig = ?");
			$stmt->bind_param('ssii', $nazwa_strony, $opis_strony, $id_strony, $IdConfig);
			$stmt->execute();
			
			$status = 
			'<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Sukces!</strong> Status strony został zmieniony.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';

		} catch (Exception $e) {
			$status = '<div class="error"><p>' . $e->getMessage() . '</p></div>';
		}
	}
	
	$stmt = $con->prepare("SELECT nazwa, opis, status FROM str_config WHERE IdConfig = ?");
	$stmt->bind_param("i", $IdConfig);
	$stmt->execute();
	$stmt->bind_result($status_strony, $opis_strony, $status_value);
	$stmt->fetch();
	$stmt->close();
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
					<div class="status"><?php echo $status; ?></div>
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2">Witaj w systemie zgłoszeń popowodziowych </h1>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="card mb-4 shadow-sm">
								<div class="card-body text-center">
									<i class="bi bi-exclamation-circle display-1 text-primary"></i>
									<h5 class="card-title mt-3">Zgłoszenia</h5>
									<a href="zgloszenia.php" class="btn mt-2">Przejdź</a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 shadow-sm">
								<div class="card-body text-center">
									<i class="bi bi-shield-check display-1 text-success"></i>
									<h5 class="card-title mt-3">Zaakceptowane</h5>
									<a href="zaakceptowane.php" class="btn mt-2">Przejdź</a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mb-4 shadow-sm">
								<div class="card-body text-center">
									<i class="bi bi-journal-text display-1 text-danger"></i>
									<h5 class="card-title mt-3">Historia logowania</h5>
									<a href="historia-logowania.php" class="btn mt-2">Przejdź</a>
								</div>
							</div>
						</div>
						<?php 
							$stmt = $con->prepare("SELECT role FROM str_user WHERE IdUser = ? LIMIT 1");
							$stmt->bind_param("i", $IdUser);
							$stmt->execute();
							$stmt->bind_result($user_role);
							$stmt->fetch();
							$stmt->close();
							if($user_role === 'admin'){
						?>
						<div class="col-md-4">
							<div class="card mb-4 shadow-sm">
								<div class="card-body text-center">
									<i class="bi bi-clipboard-pulse display-1 text-warning"></i>
									<h5 class="card-title mt-3">Status strony</h5>
									<a role="button" class="btn mt-2" data-bs-toggle="modal" data-bs-target="#statusModal">Przejdź</a>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
				</main>
			</div>
			</div>		
			<form method="POST">
				<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="statusModalLabel">Ustaw status strony</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="form-group mb-3">
									<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
									<label for="statusSelect">Wybierz status strony:</label>
									<select id="statusSelect" class="form-select" name="status_strony" onchange="updateDescription()" required>
										<option value="1" <?php if($status_value == 1) echo 'selected'; ?>>Opublikowana strona</option>
										<option value="2" <?php if($status_value == 2) echo 'selected'; ?>>Aktualizacja strony</option>
										<option value="3" <?php if($status_value == 3) echo 'selected'; ?>>Tymczasowo zamknięte</option>
										<option value="4" <?php if($status_value == 4) echo 'selected'; ?>>Chwilowa awaria</option>
										<option value="5" <?php if($status_value == 5) echo 'selected'; ?>>Prace techniczne</option>
										<option value="6" <?php if($status_value == 6) echo 'selected'; ?>>Zmiana funkcji strony</option>
										<option value="7" <?php if($status_value == 7) echo 'selected'; ?>>Przerwa techniczna</option>
									</select>
								</div>
								<div class="form-group mb-3">
									<label for="statusDescription">Opis statusu:</label>
									<textarea id="statusDescription" class="form-control" rows="4" name="opis_strony"><?php echo htmlspecialchars($opis_strony); ?></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
								<button type="submit" name="submit" class="btn btn-success">Zapisz</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		<footer class="text-muted py-4 text-center">
			<div class="container">
				<p>© 2024 <?= $website_name; ?>. Wszelkie prawa zastrzeżone.</p>
			</div>
		</footer>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script>
			function updateDescription() {
				const select = document.getElementById("statusSelect");
				const description = document.getElementById("statusDescription");

				const selectedValue = select.value;

				switch (selectedValue) {
					case "1":
						description.value = "Strona została opublikowana pomyślnie!";
						description.readOnly = true;
						break;
					case "2":
						description.value = "Obecnie prowadzimy prace konserwacyjne. Strona będzie niedostępna do [godzina/data].";
						description.readOnly = false;
						break;
					case "3":
						description.value = "Strona jest tymczasowo niedostępna. Zapraszamy wkrótce.";
						description.readOnly = false;
						break;
					case "4":
						description.value = "Doświadczamy chwilowej awarii. Pracujemy nad jak najszybszym przywróceniem strony.";
						description.readOnly = false;
						break;
					case "5":
						description.value = "Trwają planowane prace techniczne. Strona wróci do działania o [godzina/data].";
						description.readOnly = false;
						break;
					case "6":
						description.value = "Pracujemy nad aktualizacją funkcji strony. Prosimy o cierpliwość.";
						description.readOnly = false;
						break;
					case "7":
						description.value = "Planowana przerwa techniczna od [godzina] do [godzina]. Prosimy o wyrozumiałość.";
						description.readOnly = false;
						break;
					default:
						description.value = "";
						description.readOnly = false; 
				}
			}
		</script>
	</body>
</html>
