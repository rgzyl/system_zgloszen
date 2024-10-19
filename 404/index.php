<?php
	require("../params.php");
	http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error 404 - Strona nie znaleziona</title>
	<link rel="apple-touch-icon" href="../assets/img/favicon.ico">
	<link rel="icon" href="../assets/img/favicon.ico">
	<link rel="shortcut icon" href="../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #070D1F;
            color: black;
        }
        .error-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 600;
            color: #FFFFFF;
        }
        .error-message {
            font-size: 1.5rem;
            color: #FFFFFF;
            margin-bottom: 1rem;
        }
        .btn-home {
            font-size: 1.2rem;
            font-weight: 600;
            background-color: #ffffff;
            color: #070D1F;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
        }
        .btn-home:hover {
            background-color: #e0e0e0;
            color: #070D1F;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="error-code">404</div>
        <div class="error-message">Strona, której szukasz, nie istnieje.</div>
        <a href="<?= $website_path; ?>" class="btn btn-home">Powrót do strony głównej</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
