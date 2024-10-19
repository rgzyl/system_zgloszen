<?php
	require("../../../config.php");
	require("../../../functions.php");

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
			die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
        }
		
		$nazwa = sanitize_input($_POST['nazwa']);
		$id = (int) $_GET['id'];

		$stmt = $con->prepare("UPDATE str_zgloszenie SET stat = 1 WHERE IdZgloszenie = ?");
		$stmt->bind_param("i", $id);

		if ($stmt->execute()) {
			header("Location: ../zgloszenia.php?status=ok");
		} else {
			header("Location: ../zgloszenia.php?status=error");
		}

		$stmt->close();
	} else {
		header("Location: ../zgloszenia.php?status=invalid_request");
	}
?>
