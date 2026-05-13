<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/GeminiC.php';

$titrePoste   = trim($_POST['titre_poste'] ?? '');
$competences  = trim($_POST['competences'] ?? '');

if (empty($titrePoste)) {
    echo json_encode(['success' => false, 'error' => 'Le titre du poste est requis.']);
    exit;
}

try {
    $gemini      = new GeminiC();
    $description = $gemini->genererDescription($titrePoste, $competences);
    echo json_encode(['success' => true, 'description' => $description]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
