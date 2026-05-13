<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/CvC.php';
$ctrl = new CvC();
$id   = (int)($_GET['id'] ?? 0);
if ($id) { $ctrl->delete($id); $_SESSION['message'] = ['type'=>'success','texte'=>'CV supprimé.']; }
header('Location: list_cvs.php'); exit;
