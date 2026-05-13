<?php
session_start();
require_once __DIR__ . '/../../controller/CompetenceC.php';
$ctrl = new CompetenceC();
$id   = (int)($_GET['id'] ?? 0);
if ($id) { $ctrl->delete($id); $_SESSION['message'] = ['type'=>'success','texte'=>'Compétence supprimée.']; }
header('Location: list_competences.php'); exit;
