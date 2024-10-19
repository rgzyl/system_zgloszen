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
		<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css" />
		<link rel="stylesheet" href="../../assets/css/style.css">
	</head>
	<body>
		<?php include("header.php"); ?>
		<div class="container-fluid">
			<div class="row">
				<?php include("navbar.php"); ?>
				<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2">Historia logowania</h1>
					</div>
					<div class="table-responsive">
						<table id="tabelka" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>ID</th>
									<th>Użytkownik</th>
									<th>Data logowania</th>
									<th>IP</th>
									<th>Opis</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$stmt = $con->prepare("SELECT role FROM str_user WHERE IdUser = ?");
									$stmt->bind_param('i', $IdUser);
									$stmt->execute();
									$stmt->bind_result($role);
									$stmt->fetch();
									$stmt->close();

									if ($role === 'admin') {
										$query = "SELECT username, str_user_check.data AS data, str_user_check.ip AS ip, opis, kolor, role 
												  FROM str_user_check 
												  LEFT JOIN str_user ON str_user_check.IdUser = str_user.IdUser 
												  ORDER BY data DESC";
									} else {
										$query = "SELECT username, str_user_check.data AS data, str_user_check.ip AS ip, opis, kolor, role 
												  FROM str_user_check 
												  LEFT JOIN str_user ON str_user_check.IdUser = str_user.IdUser 
												  WHERE str_user.IdUser = 1 OR str_user.IdUser = ? 
												  ORDER BY data DESC";
									}

									$stmt = $con->prepare($query);

									if ($role !== 'admin') {
										$stmt->bind_param('i', $IdUser);
									}

									$stmt->execute();
									$result = $stmt->get_result();

									$x = 1;
									while ($row = $result->fetch_assoc()) {
										echo '<tr>';
										echo '<td>' . htmlspecialchars($x++) . '</td>';
										echo '<td>' . htmlspecialchars($row['username']) . '</td>';
										echo '<td>' . htmlspecialchars($row['data']) . '</td>';
										echo '<td>' . htmlspecialchars($row['ip']) . '</td>';
										echo '<td><span style="color:' . htmlspecialchars($row['kolor']) . ';">' . htmlspecialchars($row['opis']) . '</span></td>';
										echo '</tr>';
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
				<p>© 2024 <?= $website_name; ?>. Wszelkie prawa zastrzeżone.</p>
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
	</body>
</html>
