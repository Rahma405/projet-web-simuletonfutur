<?php
require_once __DIR__ . '/layouts/admin_guard.php';
require_once __DIR__ . '/../../controller/ExperienceC.php';
$ctrl = new ExperienceC();
$id   = (int)($_GET['id'] ?? 0);
if ($id) { $ctrl->delete($id); $_SESSION['message'] = ['type' => 'success', 'texte' => 'Expérience supprimée.']; }
header('Location: list_experiences.php'); exit;
