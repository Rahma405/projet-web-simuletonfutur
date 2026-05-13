<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sessionUser = $_SESSION['user'] ?? null;
if (!$sessionUser || ($sessionUser['role'] ?? '') !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
