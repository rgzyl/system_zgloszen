<?php 
    function generate_csrf_token() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    function verify_csrf_token($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    function sanitize_input($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
?>