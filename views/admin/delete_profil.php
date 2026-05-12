<?php
session_start();
require_once __DIR__ . '/../../controllers/ProfilC.php';

$ctrl = new ProfilC();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    if ($ctrl->deleteProfil($id)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => "Profil #$id supprime avec succes."];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'texte' => "Impossible de supprimer ce profil."];
    }
}

header('Location: ' . $baseUrl . '/public/index.php?route=/admin/profiles');
exit;
