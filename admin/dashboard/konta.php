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
	
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
		if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
			die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
        }
		
		$idd = sanitize_input($_POST['IdUser']);
		$nazwa = sanitize_input($_POST['nazwa']);
		$ip = $_SERVER['REMOTE_ADDR'];

		try {
			$stmt = $con->prepare("UPDATE str_user SET status = 1 WHERE IdUser = ?");
			$stmt->bind_param("i", $idd);
			$stmt->execute();

			$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
			$opis = "Usunięto konto użytkownika - $nazwa";
			$kolor = 'red'; 
			$stmt->bind_param("sssi", $opis, $ip, $kolor, $IdUser);
			$stmt->execute();

			$status = 
			'<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Sukces!</strong> Konto użytkownika - '.$nazwa.', zostało pomyślnie usunięte.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';

		} catch (Exception $e) {

			$status = 
			'<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Błąd!</strong> Konto nie zostało usunięte.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';
		}

		$stmt->close();
    }
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
		if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
			die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
		}
		
		$idd = sanitize_input($_POST['IdUser']); 
		$nazwa = sanitize_input($_POST['nazwa']); 
		$password = date("dmY");
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$hash_password = password_hash($password, PASSWORD_DEFAULT);

		$stmt = $con->prepare("UPDATE str_user SET password = ? WHERE IdUser = ?");
		$stmt->bind_param('si', $hash_password, $idd);
		
		if ($stmt->execute()) {
			
			$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
			$opis = "Zresetowano hasło użytkownika - $nazwa";
			$kolor = 'orange';
			$stmt->bind_param('sssi', $opis, $ip, $kolor, $IdUser);
			$stmt->execute();
			
			$stmt = $con->prepare("INSERT INTO str_user_check (opis, ip, kolor, IdUser) VALUES (?, ?, ?, ?)");
			$opis = "Administrator zresetował hasło do konta użytkownika";
			$kolor = 'orange';
			$stmt->bind_param('sssi', $opis, $ip, $kolor, $idd);
			$stmt->execute();

			$status = 
			'<div class="alert alert-success alert-dismissible fade show" role="alert">
				<strong>Sukces!</strong> Hasło użytkownika - '.$nazwa.', zostało zresetowane.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';
		} else {
			$status = 
			'<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Błąd!</strong> Reset hasła nie powiódł się.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';
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
		<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css" />
		<link rel="stylesheet" href="../../assets/css/style.css">
	</head>
	<body>
		<?php include("header.php"); ?>
		<div class="container-fluid">
			<div class="row">
				<?php include("navbar.php"); ?>
				<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
					<?php echo "<div>".$status."</div>" ?>
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
						<h1 class="h2">Konta użytkowników</h1>
						<a href="../register.php" role="button" class="btn btn-blue">DODAJ</a>
					</div>
					<form method="POST" action="">
					<div class="modal fade" id="dodajModal" tabindex="-1" aria-labelledby="dodajModalLabel" aria-hidden="true">
					  <div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
						  <div class="modal-header">
							<h5 class="modal-title" id="dodajModalLabel">Dodaj nową kategorię</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						  </div>
						  <div class="modal-body">
							 <div class="mb-3">
							   <input type="text" name="nazwa" class="form-control" placeholder="Podaj nazwę" required>
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
					<div class="table-responsive">
						<table id="tabelka" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>ID</th>
									<th>Nazwa użytkownika</th>
									<th>Rola</th>
									<th>Imię</th>
									<th>Nazwisko</th>
									<th>Adres e-mail</th>
									<th>Akcja</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$stmt = $con->prepare("SELECT * FROM str_user WHERE role != ? AND status = ? AND IdUser != ?");
								$role = 'system';
								$status = 0;
								$id = $IdUser;
								$stmt->bind_param('sii', $role, $status, $id);  
								$stmt->execute();
								$result = $stmt->get_result();

								$x = 1;
								while ($row = $result->fetch_assoc()) {
							?>
									<tr>
										<td><?= $x++; ?></td>
										<td>
											<span data-bs-toggle="modal" data-bs-target="#searchModal<?= htmlspecialchars($row['IdUser']); ?>" style="cursor:pointer; color: #070D1F;">
												<?= htmlspecialchars($row['username']); ?>
											</span>
										</td>
										<td><?= htmlspecialchars($row['role']); ?></td>
										<td><?= htmlspecialchars($row['name']); ?></td>
										<td><?= htmlspecialchars($row['surname']); ?></td>
										<td><?= htmlspecialchars($row['email']); ?></td>
										<td>
											<a data-bs-toggle="modal" data-bs-target="#editModal<?= htmlspecialchars($row['IdUser']); ?>" style="text-decoration:none; color:green; cursor:pointer;">
												<i class="bi bi-arrow-repeat"></i>
											</a>
											<a data-bs-toggle="modal" data-bs-target="#deleteModal<?= htmlspecialchars($row['IdUser']); ?>" style="text-decoration:none; color:red; cursor:pointer;">
												<i class="bi bi-trash"></i>
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
		<?php 
			$stmt = $con->prepare("SELECT * FROM str_user");
			$stmt->execute();
			$result = $stmt->get_result();
			while($row = $result->fetch_assoc()){
		?>
		<form method="post">
			<div class="modal fade" id="editModal<?= htmlspecialchars($row['IdUser']); ?>" tabindex="-1" aria-labelledby="editModalLabel<?= htmlspecialchars($row['IdUser']); ?>" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="editModalLabel<?= htmlspecialchars($row['IdUser']); ?>">Zresetuj hasło</h5>
							<input name="csrf_token" value="<?= generate_csrf_token(); ?>" hidden />
							<input name="nazwa" value="<?= htmlspecialchars($row['username']); ?>" hidden />
							<input name="IdUser" value="<?= htmlspecialchars($row['IdUser']); ?>" hidden />
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<p>Czy na pewno chcesz zresetować hasło użytkownika - <strong><?= htmlspecialchars($row['username']) . ' (' . htmlspecialchars($row['name']) . ' ' . htmlspecialchars($row['surname']) . ')'; ?></strong>?</p>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
							<button type="submit" name="edit" class="btn btn-success">Zapisz</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php 
			} 
			$stmt->close();
		?>
		<?php 
			$stmt = $con->prepare("SELECT * FROM str_user");
			$stmt->execute();
			$result = $stmt->get_result();
			while($row = $result->fetch_assoc()){
		?>
		<form method="post">
			<div class="modal fade" id="deleteModal<?= htmlspecialchars($row['IdUser']); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= htmlspecialchars($row['IdUser']); ?>" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="deleteModalLabel<?= htmlspecialchars($row['IdUser']); ?>">Usuń konto</h5>
							<input name="csrf_token" value="<?= generate_csrf_token(); ?>" hidden />
							<input name="nazwa" value="<?= htmlspecialchars($row['username']); ?>" hidden />
							<input name="IdUser" value="<?= htmlspecialchars($row['IdUser']); ?>" hidden />
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<p>Czy na pewno chcesz usunąć konto użytkownika - "<span style="font-weight:bold;"><?= htmlspecialchars($row['username']) . ' (' . htmlspecialchars($row['name']) . ' ' . htmlspecialchars($row['surname']) . ')'; ?></span>"?</p>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
							<button type="submit" name="delete" class="btn btn-danger">Usuń</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php 
			} 
			$stmt->close(); 
		?>
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
