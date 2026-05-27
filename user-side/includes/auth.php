<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login($redirect_to_auth_page = 'login.php') {
    if (!is_logged_in()) {
        header("Location: " . $redirect_to_auth_page);
        exit();
    }
}

function require_admin($redirect_to_auth_page = '../user-side/login.php') {
    if (!is_admin()) {
        header("Location: " . $redirect_to_auth_page);
        exit();
    }
}
?>
