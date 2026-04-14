<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

$ctrl = new UtilisateurC();
$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $u = $ctrl->getById($id);
    if ($u && $ctrl->deleteUtilisateur($id)) {
        $_SESSION['message'] = ['type'=>'success','texte'=>"✅ Utilisateur \"{$u->getPrenom()} {$u->getNom()}\" supprimé."];
    } else {
        $_SESSION['message'] = ['type'=>'danger','texte'=>"❌ Impossible de supprimer cet utilisateur."];
    }
}
header('Location: list_utilisateurs.php');
exit;
