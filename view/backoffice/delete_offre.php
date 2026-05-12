<?php
session_start();
require_once __DIR__ . '/../../controller/OffreC.php';

$ctrl = new OffreC();
$id   = (int)($_GET['id'] ?? 0);

if ($id > 0 && $ctrl->deleteOffre($id)) {
    $_SESSION['message'] = ['type' => 'success', 'texte' => 'Offre supprimée avec succès.'];
} else {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Erreur lors de la suppression ou offre introuvable.'];
}

header('Location: list_offres.php');
exit;
