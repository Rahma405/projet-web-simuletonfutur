<?php
session_start();

unset($_SESSION['user']);
$_SESSION['message'] = [
    'type' => 'success',
    'texte' => 'Vous etes maintenant deconnecte.',
];

header('Location: ../../index.php');
exit;
