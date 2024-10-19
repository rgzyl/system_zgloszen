<?php 
    require("../../config.php");

    $stmt = $con->prepare("SELECT * FROM str_user WHERE IdUser = ?");
    $stmt->bind_param("i", $IdUser);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
?>
    <header class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="../../assets/img/logo.png" alt="Logo" width="40" height="40" class="d-inline-block">  
                <?= htmlspecialchars($website_name); ?>
            </a>
            <ul class="navbar-nav ms-auto">
				<li class="nav-item">
					<a class="nav-link" href="<?= $website_path; ?>" role="button"><i class="bi bi-box-arrow-in-up-right"></i></a>
				</li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item person">Witaj, <?= htmlspecialchars($row['name']) . ' ' . htmlspecialchars($row['surname']); ?></a>
                        </li>
                        <li>
                            <a class="dropdown-item item" href="reset-haslo.php"><i class="bi bi-lock"></i> Zmień hasło</a>
                        </li>
                        <li>
                            <a class="dropdown-item item" href="uwierzytelnianie.php"><i class="bi bi-shield-lock"></i> Uwierzytelnianie</a>
                        </li>
                        <li>
                            <a class="dropdown-item item" href="logout.php"><i class="bi bi-box-arrow-left"></i> Wyloguj się</a>
                        </li>
                    </ul>
                </li>
			</ul>
        </div>
    </header>
<?php 
    }
    $stmt->close();
?>

