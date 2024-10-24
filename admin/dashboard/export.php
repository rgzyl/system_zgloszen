<?php
	require('../../vendor/autoload.php');
	require('../../config.php');
	require('auth.php');
	
	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	use PhpOffice\PhpSpreadsheet\Style\Border;
	use PhpOffice\PhpSpreadsheet\Cell\DataType;

	try {
		mysqli_query($con, "SET @row_number = 0;");

		$stmt = $con->prepare("SELECT @row_number := @row_number + 1 AS lp, imie, nazwisko, adres, kod, nazwa, telefon, mail FROM str_zgloszenie LEFT JOIN str_wies ON str_zgloszenie.IdWies = str_wies.IdWies;");		
		$stmt->execute();
		$result = $stmt->get_result();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A1', 'Lp');
		$sheet->setCellValue('B1', 'Imię');
		$sheet->setCellValue('C1', 'Nazwisko');
		$sheet->setCellValue('D1', 'Adres');
		$sheet->setCellValue('E1', 'Kod pocztowy');
		$sheet->setCellValue('F1', 'Miejscowość');
		$sheet->setCellValue('G1', 'Telefon');
		$sheet->setCellValue('H1', 'E-mail');

		$sheet->getStyle('A1:H1')->getFont()->setBold(true);
		$sheet->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

		$rowNumber = 2; 
		while ($row = $result->fetch_assoc()) {
			$sheet->setCellValueExplicit('A' . $rowNumber, htmlspecialchars($row['lp']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('B' . $rowNumber, htmlspecialchars($row['imie']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('C' . $rowNumber, htmlspecialchars($row['nazwisko']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('D' . $rowNumber, htmlspecialchars($row['adres']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('E' . $rowNumber, htmlspecialchars($row['kod']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('F' . $rowNumber, htmlspecialchars($row['nazwa']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('G' . $rowNumber, htmlspecialchars($row['telefon']), DataType::TYPE_STRING);
			$sheet->setCellValueExplicit('H' . $rowNumber, htmlspecialchars($row['mail']), DataType::TYPE_STRING);

			$sheet->getStyle('A' . $rowNumber . ':H' . $rowNumber)
				->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

			$rowNumber++;
		}

		foreach (range('A', 'H') as $columnID) {
			$sheet->getColumnDimension($columnID)->setAutoSize(true);
		}

		$filename = 'export_' . date('Y-m-d_H-i-s') . '.xlsx';

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');
	} catch (Exception $e) {
		echo "Error: " . htmlspecialchars($e->getMessage());
	}
?>
