<?php
session_start();
require_once __DIR__ . '/../../controller/ProfilC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Veuillez vous connecter pour supprimer votre profil.'];
    header('Location: login.php');
    exit;
}

if (($_SESSION['user']['statut'] ?? 'actif') === 'en_attente') {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => "Votre compte est en attente. Cette action n'est pas encore autorisee."];
    header('Location: list_profils.php');
    exit;
}

$ctrl = new ProfilC();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $profil = $ctrl->getById($id);
    if ($profil) {
        $sessionUser = $_SESSION['user'];
        $estAdmin = ($sessionUser['role'] ?? '') === 'admin';
        $estProprietaire = (int) $sessionUser['id'] === (int) $profil->getIdUtilisateur();

        if ($estAdmin || $estProprietaire) {
            if ($ctrl->deleteProfil($id)) {
                $_SESSION['message'] = ['type' => 'success', 'texte' => 'Le profil a ete supprime avec succes.'];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Impossible de supprimer ce profil.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Vous ne pouvez supprimer que votre propre profil.'];
        }
    }
}

header('Location: list_profils.php');
exit;
