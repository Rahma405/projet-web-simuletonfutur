<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/CandidatureC.php';

$ctrl = new CandidatureC();
$id   = (int)($_GET['id'] ?? 0);

if ($id > 0 && $ctrl->deleteCandidature($id)) {
    $_SESSION['message'] = ['type' => 'success', 'texte' => 'Candidature supprimée avec succès.'];
} else {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Erreur lors de la suppression ou candidature introuvable.'];
}

header('Location: list_candidatures.php');
exit;