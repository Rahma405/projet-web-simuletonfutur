<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/ProfilC.php';

$ctrl = new ProfilC();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    if ($ctrl->deleteProfil($id)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => "Profil #$id supprime avec succes."];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'texte' => "Impossible de supprimer ce profil."];
    }
}

header('Location: list_profils.php');
exit;
