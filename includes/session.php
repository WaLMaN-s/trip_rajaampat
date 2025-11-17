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

function require_login() {
    if (!is_logged_in()) {
        // Simpan URL yang ingin diakses untuk redirect setelah login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect(SITE_URL . '/public/login.php');
    }
}

function require_admin() {
    if (!is_logged_in()) {
        redirect(SITE_URL . '/public/login.php');
    }
    if (!is_admin()) {
        alert('Akses ditolak! Anda bukan admin.', 'danger');
        redirect(SITE_URL . '/public/index.php');
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_name() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function get_user_email() {
    return $_SESSION['user_email'] ?? '';
}

function set_user_session($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

function destroy_session() {
    session_unset();
    session_destroy();
}