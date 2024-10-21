<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['admin']);
}

function logout() {
    session_unset();
    session_destroy();
}
?>
