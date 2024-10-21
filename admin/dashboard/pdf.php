<?php
	require('../../vendor/autoload.php');
	require('../../config.php');
	require('auth.php');
	use Dompdf\Dompdf;
	
	try {
		mysqli_query($con, "SET @row_number = 0;");

		$stmt = $con->prepare("SELECT @row_number := @row_number + 1 AS lp, imie, nazwisko, adres, kod, nazwa, telefon, mail FROM str_zgloszenie LEFT JOIN str_wies ON str_zgloszenie.IdWies = str_wies.IdWies;");
		$stmt->execute();
		$result = $stmt->get_result();

		$html = '
		<!DOCTYPE html>
		<html lang="pl">
		<head>
			<meta charset="UTF-8">
			<meta name="title" content="Zaakceptowane zgłoszenia">
			<meta name="author" content="Twoja firma">
			<meta name="subject" content="Lista zaakceptowanych zgłoszeń">
			<meta name="keywords" content="zgłoszenia, PDF, Dompdf, lista, zaakceptowane zgłoszenia">
			<meta name="description" content="PDF z listą zaakceptowanych zgłoszeń">
			<title>Zaakceptowane zgłoszenia</title>
			<style>
				body {
					font-family: DejaVu Sans, sans-serif;
					font-size: 12px;
				}
				table {
					width: 100%;
					border-collapse: collapse;
				}
				th, td {
					padding: 8px;
					border: 1px solid #ddd;
					text-align: left;
					vertical-align: middle;
				}
				th {
					background-color: #f4f4f4;
					color: #333;
					font-size: 14px;
				}
				tr:nth-child(even) {
					background-color: #f9f9f9;
				}
				h2 {
					text-align: center;
					font-size: 24px;
					margin-bottom: 20px;
				}
				td:nth-child(1) {
					width: 5%;
					text-align: center;
				}
				td:nth-child(2), td:nth-child(3) {
					width: 15%;
				}
				td:nth-child(4) {
					width: 25%;
				}
				td:nth-child(5), td:nth-child(6) {
					width: 10%;
				}
				td:nth-child(7), td:nth-child(8) {
					width: 10%;
				}
				@page {
					margin: 20mm 10mm;
				}
			</style>
		</head>
		<body>
			<h2>Zaakceptowane zgłoszenia</h2>
			<table>
				<thead>
					<tr>
						<th>Lp</th>
						<th>Imię</th>
						<th>Nazwisko</th>
						<th>Adres</th>
						<th>Kod pocztowy</th>
						<th>Miejscowość</th>
						<th>Telefon</th>
						<th>E-mail</th>
					</tr>
				</thead>
				<tbody>';

		while ($row = $result->fetch_assoc()) {
			$html .= '
				<tr>
					<td>' . htmlspecialchars($row['lp']) . '</td>
					<td>' . htmlspecialchars($row['imie']) . '</td>
					<td>' . htmlspecialchars($row['nazwisko']) . '</td>
					<td>' . htmlspecialchars($row['adres']) . '</td>
					<td>' . htmlspecialchars($row['kod']) . '</td>
					<td>' . htmlspecialchars($row['nazwa']) . '</td>
					<td>' . htmlspecialchars($row['telefon']) . '</td>
					<td>' . htmlspecialchars($row['mail']) . '</td>
				</tr>';
		}

		$html .= '
				</tbody>
			</table>
		</body>
		</html>';

		$dompdf = new Dompdf();

		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();

		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="export.pdf"');
		echo $dompdf->output();
	} catch (Exception $e) {
		echo "Error: " . htmlspecialchars($e->getMessage());
	}
?>
