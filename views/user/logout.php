<?php
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base = preg_replace('#/public$#', '', $base);
$base = rtrim($base, '/');

session_start();
session_unset();
session_destroy();
header('Location: ' . $base . '/public/index.php?route=/');
exit;
