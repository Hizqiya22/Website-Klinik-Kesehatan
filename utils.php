<?php
session_start();

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function role($r) {
    return $_SESSION['role'] === $r;
}
?>
