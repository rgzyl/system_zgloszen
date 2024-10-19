<?php
    session_start();

    if (!isset($_SESSION["IdUser"])) {
        header("Location: ../index.php");
        exit();
    }

    if (!isset($_SESSION['2fa_verified']) || $_SESSION['2fa_verified'] !== true) {
        header("Location: ../2fa.php");
        exit();
    }
?>
