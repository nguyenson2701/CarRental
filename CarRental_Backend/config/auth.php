<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

function isStaff() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 2;
}

function isCustomer() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 3;
}

function requireLogin($redirect) {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit();
    }
}

function requireAdmin($redirect) {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: $redirect");
        exit();
    }
}

function requireAdminOrStaff($redirect) {
    if (!isLoggedIn() || (!isAdmin() && !isStaff())) {
        header("Location: $redirect");
        exit();
    }
}

function requireCustomer($redirect) {
    if (!isLoggedIn() || !isCustomer()) {
        header("Location: $redirect");
        exit();
    }
}
?>