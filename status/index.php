<?php 
	session_start();
	if(file_exists('../config.php')){
		require("../config.php");
	} else {
		header("Location: ../konfiguracja/?option=1");
	}
	require("../params.php");
	
	$isLogged = isset($_SESSION['IdUser']) && $_SESSION['IdUser'] == true;
	
	$stmt = $con->prepare('SELECT status, nazwa, opis FROM str_config WHERE IdConfig = 1');
	$stmt->execute();
	$stmt->bind_result($status, $nazwa, $opis);
	$stmt->fetch();
	$stmt->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Status | <?= $website_name; ?></title>
	<link rel="apple-touch-icon" href="../assets/img/favicon.ico">
	<link rel="icon" href="../assets/img/favicon.ico">
	<link rel="shortcut icon" href="../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #070D1F;
            color: black;
        }
		
        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
		
        .title {
            font-size: 4rem;
            font-weight: 600;
            color: #FFFFFF;
        }
		
        .subtitle {
            font-size: 1.5rem;
            color: #FFFFFF;
            margin-bottom: 1rem;
        }

		@media (max-width: 768px) {
			.title {
				font-size: 3rem; 
			}
			.subtitle {
				font-size: 1.25rem;
			}
		}
    </style>
</head>
<body>
    <?php if ($isLogged){ ?>
        <div class="admin-bar bg-light text-dark p-2 d-flex justify-content-between align-items-center" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
            <div class="d-flex align-items-center">
                <a class="nav-link text-dark" href="../admin/dashboard/index.php" role="button">
					<i class="bi bi-box-arrow-in-up-left"></i>
					<span>Powrót do panelu administratora</span>
				</a>
            </div>
        </div>
    <?php } ?>
    <div class="container main-container">
		<?php 
			if ($status >= 2 && $status <= 7) {
				echo '<div class="title">' . htmlspecialchars($nazwa) . '</div>';
				echo '<div class="subtitle">' . htmlspecialchars($opis) . '</div>';
			} else {
				echo '<div class="title">Nieznany status</div>';
				echo '<div class="subtitle">Proszę skontaktować się z administratorem.</div>';
			}
		?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>