<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

header('Content-Type: application/json; charset=UTF-8');

$ctrl = new UtilisateurC();
$token = trim($_GET['token'] ?? '');

if ($token === '') {
    echo json_encode(['status' => 'invalid']);
    exit;
}

$status = $ctrl->getQrLoginStatus($token);

if ($status === 'approved') {
    $utilisateur = $ctrl->consumeQrLoginToken($token);

    if ($utilisateur) {
        $_SESSION['user'] = [
            'id' => $utilisateur->getIdUtilisateur(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
            'statut' => $utilisateur->getStatut(),
        ];
        $_SESSION['message'] = [
            'type' => 'success',
            'texte' => 'Connexion QR reussie. Bienvenue ' . $utilisateur->getPrenom() . ' !',
        ];
        unset($_SESSION['qr_login_token'], $_SESSION['qr_login_email']);

        echo json_encode([
            'status' => 'approved',
            'authenticated' => true,
            'redirect' => $utilisateur->getRole() === 'admin'
                ? '../backoffice/list_utilisateurs.php'
                : '../../index.php',
        ]);
        exit;
    }
}

echo json_encode([
    'status' => $status,
    'authenticated' => false,
]);
