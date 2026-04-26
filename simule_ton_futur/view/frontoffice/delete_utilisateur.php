<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Veuillez vous connecter pour supprimer votre compte.'];
    header('Location: login.php');
    exit;
}

$ctrl = new UtilisateurC();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sessionUser = $_SESSION['user'];
$estAdmin = ($sessionUser['role'] ?? '') === 'admin';

if ($id) {
    $u = $ctrl->getById($id);
    if ($u && ($estAdmin || (int) $sessionUser['id'] === $id) && $ctrl->deleteUtilisateur($id)) {
        $_SESSION['message'] = ['type' => 'success', 'texte' => 'Utilisateur supprime avec succes.'];
        if ((int) $sessionUser['id'] === $id) {
            unset($_SESSION['user']);
            header('Location: ../../index.php');
            exit;
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'texte' => "Impossible de supprimer cet utilisateur."];
    }
}

header('Location: list_utilisateurs.php');
exit;
