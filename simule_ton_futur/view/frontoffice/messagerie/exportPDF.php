<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$sessionUser = $_SESSION['user'] ?? null;
if (!$sessionUser) { header('Location: ../login.php'); exit; }

$userId = (int) $sessionUser['id'];
$userPseudo = trim(($sessionUser['prenom'] ?? '') . ' ' . ($sessionUser['nom'] ?? ''));

require_once __DIR__ . '/../../../lib/FPDF-master/fpdf.php';
require_once __DIR__ . '/../../../controller/ConversationC.php';
require_once __DIR__ . '/../../../controller/MessageC.php';

$convC  = new ConversationC();
$msgC   = new MessageC();

$convId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$convId || $convId <= 0) die('Identifiant de conversation invalide.');

$conv = $convC->getConversation($convId);
if (!$conv) die('Conversation introuvable.');
if ($conv['utilisateur1_id'] != $userId && $conv['utilisateur2_id'] != $userId) die('Accès refusé.');

if ($conv['utilisateur1_id'] == $userId) { $autreId = (int) $conv['utilisateur2_id']; $autrePseudo = $conv['pseudo2']; }
else { $autreId = (int) $conv['utilisateur1_id']; $autrePseudo = $conv['pseudo1']; }

$messages = $msgC->listerMessages($userId, $autreId);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

$pdf->SetFont('Helvetica', 'B', 18);
$pdf->SetTextColor(231, 76, 91);
$pdf->Cell(0, 12, 'Messagerie - Conversation', 0, 1, 'C');

$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 8, 'Sujet : ' . utf8_decode($conv['sujet']), 0, 1, 'C');
$pdf->Cell(0, 7, utf8_decode($userPseudo) . '  /  ' . utf8_decode($autrePseudo), 0, 1, 'C');
$pdf->Cell(0, 7, 'Date : ' . date('d/m/Y H:i'), 0, 1, 'C');

$pdf->Ln(6);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(6);

if (empty($messages)) {
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 10, 'Aucun message dans cette conversation.', 0, 1, 'C');
} else {
    foreach ($messages as $msg) {
        $estMoi  = ((int) $msg['expediteur_id'] === $userId);
        $pseudo  = utf8_decode($msg['pseudo_expediteur']);
        $contenu = utf8_decode($msg['contenu']);
        $date    = $msg['date_envoi'];
        $lu      = $msg['est_lu'] ? 'Lu' : 'Non lu';

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor($estMoi ? 231 : 66, $estMoi ? 76 : 153, $estMoi ? 91 : 225);
        $pdf->Cell(0, 6, $pseudo . '  -  ' . $date . '  (' . $lu . ')', 0, 1);

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->MultiCell(0, 6, $contenu);
        $pdf->Ln(4);
    }
}

$pdf->Ln(10);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('Helvetica', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 6, 'Exporte depuis Simule Ton Futur - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

$pdf->Output('D', 'conversation_' . $convId . '_' . date('Ymd_His') . '.pdf');
exit;
