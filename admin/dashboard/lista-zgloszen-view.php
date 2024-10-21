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
	$id = $_GET['id'];
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
			die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
        }
		
		$nazwa = sanitize_input($_POST['nazwa']);
		$id = (int) $_GET['id'];

		$stmt = $con->prepare("UPDATE str_zgloszenie SET stat = 1 WHERE IdZgloszenie = ?");
		$stmt->bind_param("i", $id);

		if ($stmt->execute()) {
			header("Location: zgloszenia.php");
		} else {
			echo "Coś poszło nie tak!";
		}

		$stmt->close();
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
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
	</head>
	<body>
		<?php include("header.php"); ?>
		<div class="container-fluid">
			<div class="row">
				<?php include("navbar.php"); ?>
				<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
					<?php
						$stmt = $con->prepare("SELECT * FROM `str_zgloszenie` LEFT JOIN str_wies ON str_zgloszenie.IdWies = str_wies.IdWies WHERE IdZgloszenie = ?"); 
						$stmt->bind_param("i", $id);   
						$stmt->execute(); 
						$result = $stmt->get_result();
						$row = $result->fetch_array();
						$IdZgloszenie = htmlspecialchars($row['IdZgloszenie']);
						$stmt->close();
					?>
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2"><?= htmlspecialchars($row['imie']).' '.htmlspecialchars($row['nazwisko']); ?></h1>
					</div>

					<div class="card shadow-sm mb-4">
						<div class="card-body">
							<div class="row mb-3">
								<div class="col-md-6">
									<p><strong>Imię i nazwisko:</strong> <?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']); ?></p>
									<p><strong>Adres zamieszkania:</strong> <?= htmlspecialchars($row['adres'] . ', ' . $row['kod'] . ' ' . $row['nazwa']); ?></p>
									<p><strong>Telefon:</strong> <?= htmlspecialchars($row['telefon']); ?></p>
								</div>
								<div class="col-md-6">
									<p><strong>E-mail:</strong> <?= htmlspecialchars($row['mail']); ?></p>
									<p><strong>Data utworzenia zgłoszenia:</strong> <?= htmlspecialchars($row['data']); ?></p>
									<p><strong>Adres IP osoby zgłaszającej:</strong> <?= htmlspecialchars($row['ip']); ?></p>
								</div>
								<div class="col-md-12">
									<p><strong>Opis:</strong> <?= htmlspecialchars($row['opis']); ?></p>
								</div>
							</div>							
							<div class="row">
								<?php 
									$stmt = $con->prepare("SELECT * FROM str_zdjecia WHERE IdZgloszenie = ?"); 
									$stmt->bind_param("i", $IdZgloszenie);
									$stmt->execute(); 
									$result = $stmt->get_result();

									if ($result->num_rows > 0) {
										while ($rw = $result->fetch_assoc()) {
								?>
											<div class="col-md-4 mb-3">
												<a data-fancybox="gallery" href="../../uploads/<?= htmlspecialchars($rw['nazwa']); ?>">
													<img src="../../uploads/<?= htmlspecialchars($rw['nazwa']); ?>" class="img-fluid rounded shadow-sm" style="width:250px;" alt="Zdjęcie zgłoszenia" />
												</a>
											</div>
								<?php 
										}
									} else {
										echo '<div class="col-md-12 mb-3"><p>Brak zdjęć do wyświetlenia.</p></div>';
									}
									$stmt->close();
								?>
							</div>
						</div>
					</div>

					<?php 
						if($row['stat'] == '0'){
					?>
					<div class="d-grid gap-2">
						<button type="button"  name="submit" data-bs-toggle="modal" data-bs-target="#checkModal<?= $id; ?>" class="btn btn-blue">Zaakceptuj zgłoszenie</button>
					</div>
					<?php } ?>
							
					<?php 
					$stmt = $con->prepare("SELECT * FROM str_zgloszenie;"); 
					$stmt->execute(); 
					$result = $stmt->get_result();

					while ($row = $result->fetch_assoc()) {
					?>
							<form method="post">
								<input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
								<div class="modal fade" id="checkModal<?= htmlspecialchars($row['IdZgloszenie']); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= htmlspecialchars($row['IdZgloszenie']); ?>" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="deleteModalLabel<?= htmlspecialchars($row['IdZgloszenie']); ?>">Zaakceptuj zgłoszenie</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<div class="mb-3">
													<p>Czy napewno chcesz zaakceptować zgłoszenie Pana/Pani "<span style='font-weight:bold;'><?= htmlspecialchars($row['imie'].' '.$row['nazwisko']); ?></span>"?</p>
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
					<?php 
					}
					$stmt->close();
					?>

				</main>
			</div>
		</div>
		<footer class="text-muted py-4 text-center">
			<div class="container">
				<p>© <?= date('Y').' '.$website_name; ?>. Wszelkie prawa zastrzeżone.</p>
			</div>
		</footer>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
	</body>
</html>
