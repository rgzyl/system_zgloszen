<?php
    session_start();

    if (file_exists('../config.php')) {
        require("../config.php");
    } else {
        header("Location: ../konfiguracja/?option=1");
    }

    require('../functions.php');
    require('../params.php');
    require('../vendor/autoload.php');

    $status = "";
    $IdUser = $_SESSION["IdUser"];

    if (!isset($_SESSION['IdUser'])) {
        header('Location: index.php');
        exit;
    }

    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        header('Location: dashboard/index.php');
        exit();
    }

    $twofa = new \RobThree\Auth\TwoFactorAuth($website_name);


    $stmt = $con->prepare("SELECT 2fa_secret FROM str_user WHERE IdUser = ?");
    $stmt->bind_param('i', $IdUser);
    $stmt->execute();
    $stmt->bind_result($secret);
    $stmt->fetch();
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!verify_csrf_token(sanitize_input($_POST['csrf_token']))) {
                die('Błąd CSRF. Proszę odświeżyć stronę i spróbować ponownie.');
            }
			
            $number1 = sanitize_input($_POST['number1']);
            $number2 = sanitize_input($_POST['number2']);
            $number3 = sanitize_input($_POST['number3']);
            $number4 = sanitize_input($_POST['number4']);
            $number5 = sanitize_input($_POST['number5']);
            $number6 = sanitize_input($_POST['number6']);
            $code = $number1.$number2.$number3.$number4.$number5.$number6;

            if ($twofa->verifyCode($secret, $code)) {
                $_SESSION['2fa_verified'] = true;
                $_SESSION['authenticated'] = true;
                header('Location: dashboard/index.php');
                exit;
            } else {
                $status = '<div class="error"><p>* Kod 2FA jest nieprawidłowy!</p></div>';
            }
        } catch (Exception $e) {
            $status = '<div class="error"><p>Błąd: ' . $e->getMessage() . '</p></div>';
        }
    }
?>
 <!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Weryfikacja dwuetapowa | <?= $website_name; ?></title>
		<meta name="description" content="">
		<meta name="keywords" content="<?= $website_keywords; ?>">
		<link rel="apple-touch-icon" href="../../assets/img/favicon.ico">
		<link rel="icon" href="../../assets/img/favicon.ico">
		<link rel="shortcut icon" href="../../assets/img/favicon.ico">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
        <style>
			@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap");
			
            body, html {
                height: 100%;
                margin: 0;
                background-color: #070d1f;
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: "Poppins", sans-serif;
            }

            .main-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                row-gap: 32px;
                text-align: center;
            }

            .opt-number {
                font-weight: 600;
                font-size: 2rem;
                color: #ffffff;
            }
			
            .opt-title {
                font-weight: 600;
                font-size: 1.5rem;
                color: #ffffff;
				margin-bottom: 0.5rem;
            }

            .opt-subtitle {
                color: #b3b3b3;
                font-weight: 400;
                font-size: 1rem;
            }

            #otp-input {
                display: flex;
                column-gap: 8px;
            }

            #otp-input input {
                text-align: center;
                padding: 10px;
                border: 1px solid #adadad;
                border-radius: 4px;
                outline: none;
                height: 64px;
                width: 50px;
                background-color: #fafafa;
                color: #070d1f;
                font-size: 2rem;
                font-weight: 600;
                font-family: "Poppins", sans-serif;
            }

            #otp-input input:focus {
                border: 1px solid #ffffff;
            }

            button {
                font-size: 1.2rem;
                font-weight: 600;
                background-color: #ffffff;
                color: #070d1f;
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                text-decoration: none;
                border: none;
                cursor: pointer;
            }

            button:hover {
                background-color: #e0e0e0;
            }
        </style>
    </head>
    <body>
		<form method="POST">
		<input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
		<div id="status"></div>
		<div class="container main-container">
            <div class="text-container">
                <div class="opt-title">Weryfikacja dwuetapowa (MFA)</div>
                <div class="opt-subtitle">Wprowadź jednorazowy kod weryfikacyjny, aby uzyskać dostęp do swojego konta</div>
            </div>
            <div id="otp-input">
                <input class="opt-number" name="number1" type="text" maxlength="1" autocomplete="on" pattern="\d*" required />
                <input class="opt-number" name="number2" type="text" maxlength="1" autocomplete="off" pattern="\d*" required />
                <input class="opt-number" name="number3" type="text" maxlength="1" autocomplete="off" pattern="\d*" required />
                <input class="opt-number" name="number4" type="text" maxlength="1" autocomplete="off" pattern="\d*" required />
                <input class="opt-number" name="number5" type="text" maxlength="1" autocomplete="off" pattern="\d*" required />
                <input class="opt-number" name="number6" type="text" maxlength="1" autocomplete="off" pattern="\d*" required />
                <input id="otp-value" type="hidden" name="otp" />
            </div>
            <button type="submit" name="submit" id="submit" class="btn-white">Wyślij kod</button>
        </div>
		</form>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
		<script>
			window.addEventListener("load", function () {
				const OTPContainer = document.querySelector("#otp-input");
				const inputs = OTPContainer.querySelectorAll("input:not(#otp-value)");
				
				const firstInput = inputs[0];
				firstInput.addEventListener("paste", (event) => {
					event.preventDefault();
					const pastedData = event.clipboardData.getData("text").trim();

					if (/^\d{6}$/.test(pastedData)) {
						for (let i = 0; i < 6; i++) {
							if (i < pastedData.length) {
								setInputValue(inputs[i], pastedData[i]);
							} else {
								resetInput(inputs[i]);
							}
						}
					} else {
						inputs.forEach(resetInput);
					}
				});

				inputs.forEach((input, index) => {
					input.addEventListener("input", (e) => handleInput(input, e.target.value, index, inputs));
					input.addEventListener("keydown", (e) => handleKeyDown(e, e.key, input, index, inputs));
					input.addEventListener("keyup", (e) => handleKeyUp(e, e.key, input, index, inputs));
					input.addEventListener("focus", (e) => e.target.select());
				});
				
				const isValidInput = (inputValue) => {
					return Number(inputValue) === 0 && inputValue !== "0" ? false : true;
				};

				const setInputValue = (inputElement, inputValue) => {
					inputElement.value = inputValue;
				};

				const resetInput = (inputElement) => {
					setInputValue(inputElement, "");
				};

				const focusNext = (inputs, curIndex) => {
					const nextElement = curIndex < inputs.length - 1 ? inputs[curIndex + 1] : inputs[curIndex];
					nextElement.focus();
					nextElement.select();
				};

				const focusPrev = (inputs, curIndex) => {
					const prevElement = curIndex > 0 ? inputs[curIndex - 1] : inputs[curIndex];
					prevElement.focus();
					prevElement.select();
				};

				const focusIndex = (inputs, index) => {
					const element = index < inputs.length - 1 ? inputs[index] : inputs[inputs.length - 1];
					element.focus();
					element.select();
				};

				const handleValidMultiInput = (inputElement, inputValue, curIndex, inputs) => {
					const inputLength = inputValue.length;
					const numInputs = inputs.length;

					const endIndex = Math.min(curIndex + inputLength - 1, numInputs - 1);
					const inputsToChange = Array.from(inputs).slice(curIndex, endIndex + 1);
					inputsToChange.forEach((input, index) => setInputValue(input, inputValue[index]));
					focusIndex(inputs, endIndex);
				};

				const handleInput = (inputElement, inputValue, curIndex, inputs) => {
					if (!isValidInput(inputValue)) return handleInvalidInput(inputElement);
					if (inputValue.length === 1) handleValidSingleInput(inputElement, inputValue, curIndex, inputs);
					else handleValidMultiInput(inputElement, inputValue, curIndex, inputs);
				};

				const handleValidSingleInput = (inputElement, inputValue, curIndex, inputs) => {
					setInputValue(inputElement, inputValue.slice(-1));
					focusNext(inputs, curIndex);
				};

				const handleInvalidInput = (inputElement) => {
					resetInput(inputElement);
				};

				const handleKeyDown = (event, key, inputElement, curIndex, inputs) => {
					if (key === "Delete") {
						resetInput(inputElement);
						focusPrev(inputs, curIndex);
					}
					if (key === "ArrowLeft") {
						event.preventDefault();
						focusPrev(inputs, curIndex);
					}
					if (key === "ArrowRight") {
						event.preventDefault();
						focusNext(inputs, curIndex);
					}
				};

				const handleKeyUp = (event, key, inputElement, curIndex, inputs) => {
					if (key === "Backspace") focusPrev(inputs, curIndex);
				};
			});
		</script>
    </body>
</html>