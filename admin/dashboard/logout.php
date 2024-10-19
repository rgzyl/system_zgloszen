<?php
	require("../../config.php");
	require("../../params.php");
	session_start();
    session_unset();
    session_destroy();
    if (isset($_COOKIE['rememberme'])) {
        list($userId, $token) = explode(':', $_COOKIE['rememberme']);
        $userId = mysqli_real_escape_string($con, $userId);
        $token = mysqli_real_escape_string($con, $token);
        mysqli_query($con, "DELETE FROM str_user_tokens WHERE IdUser = '$userId' AND token = '$token'") or die(mysqli_error($con));
        setcookie('rememberme', '', time() - 3600, "/");
    }
    header("Location: $website_path"."admin");
?>
