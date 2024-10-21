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
		<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css" />
	</head>
	<body>
		<?php include("header.php"); ?>
		<div class="container-fluid">
			<div class="row">
				<?php include("navbar.php"); ?>
				<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2">Zaakceptowane zgłoszenia</h1>
						<div>
						<a role="button" href="export.php" class="btn btn-blue">MS EXCEL</a>
						<a role="button" href="pdf.php" target="_blank" class="btn btn-blue">PDF</a>
						</div>
					</div>						

					<div class="table-responsive">
						<table id="tabelka" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>ID</th>
									<th>Imię i nazwisko</th>
									<th>Miejscowość</th>
									<th>Data utworzenia</th>
									<th>Akcja</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$stmt = $con->prepare("SELECT str_zgloszenie.IdZgloszenie, str_zgloszenie.imie, str_zgloszenie.nazwisko, str_zgloszenie.data, str_wies.nazwa FROM str_zgloszenie LEFT JOIN str_wies ON str_zgloszenie.IdWies = str_wies.IdWies WHERE stat = ? ORDER BY data DESC"); 
								$stat = 1;
								$stmt->bind_param('i', $stat); 
								$stmt->execute();
								$result = $stmt->get_result();
								$x = 1;    
								while($row = $result->fetch_assoc()) {
							?>
								<tr>
									<td><?= $x++; ?></td>
									<td>
										<a href="lista-zgloszen-view.php?id=<?= htmlspecialchars($row['IdZgloszenie']); ?>" 
										   style="text-decoration:none; color: #070D1F;">
										   <?= htmlspecialchars($row['imie']) . ' ' . htmlspecialchars($row['nazwisko']); ?>
										</a>
									</td>
									<td><?= htmlspecialchars($row['nazwa']); ?></td>
									<td><?= htmlspecialchars($row['data']); ?></td>
									<td>
										<a href="lista-zgloszen-view.php?id=<?= htmlspecialchars($row['IdZgloszenie']); ?>" 
										   style="text-decoration:none; color:blue;">
											<i class="bi bi-search"></i>
										</a>
									</td>
								</tr>
							<?php 
								} 
								$stmt->close(); 
							?>
							</tbody>
						</table>
					</div>
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
		<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
		<script>
		$(document).ready(function() {
			$('#tabelka').DataTable({
				"language": {
					"url": "//cdn.datatables.net/plug-ins/1.13.1/i18n/pl.json"
				}
			});
		});
		</script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
	</body>
</html>
