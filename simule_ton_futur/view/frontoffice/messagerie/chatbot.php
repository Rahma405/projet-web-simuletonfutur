<?php
require_once 'header_messagerie.php';
require_once __DIR__ . '/../../../controller/ChatbotC.php';

$chatbot = new ChatbotC();

if (!isset($_SESSION['chatbot_history'])) {
    $_SESSION['chatbot_history'] = [
        ['from' => 'bot', 'contenu' => 'Bonjour ' . htmlspecialchars($userPseudo) . ' ! Je suis le chatbot de la messagerie. Tapez "aide" pour voir ce que je peux faire.', 'date' => date('H:i')]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_bot'])) {
    $message = trim($_POST['contenu'] ?? '');
    if ($message !== '') {
        $_SESSION['chatbot_history'][] = ['from' => 'user', 'contenu' => htmlspecialchars($message), 'date' => date('H:i')];
        $_SESSION['chatbot_history'][] = ['from' => 'bot', 'contenu' => $chatbot->genererReponse($message), 'date' => date('H:i')];
        header('Location: chatbot.php');
        exit;
    }
}

if (isset($_GET['clear'])) { unset($_SESSION['chatbot_history']); header('Location: chatbot.php'); exit; }

$history = $_SESSION['chatbot_history'];
?>

<div class="msg-page">
    <div class="msg-page-header">
        <a href="conversations.php" class="msg-btn msg-btn-outline">&#8592; Retour</a>
        <div class="msg-conv-title">
            <h1>&#129302; Chatbot</h1>
            <span class="msg-conv-sujet">Assistant intelligent de la messagerie</span>
        </div>
        <a href="chatbot.php?clear=1" class="msg-btn msg-btn-sm msg-btn-danger">Effacer l'historique</a>
    </div>

    <div class="chatbot-suggestions">
        <span class="chatbot-suggestion-label">Suggestions :</span>
        <button type="button" class="chatbot-chip" onclick="sendQuick('aide')">Aide</button>
        <button type="button" class="chatbot-chip" onclick="sendQuick('fonctionnalités')">Fonctionnalités</button>
        <button type="button" class="chatbot-chip" onclick="sendQuick('conversation')">Conversation</button>
        <button type="button" class="chatbot-chip" onclick="sendQuick('recherche')">Recherche</button>
        <button type="button" class="chatbot-chip" onclick="sendQuick('pdf')">Export PDF</button>
        <button type="button" class="chatbot-chip" onclick="sendQuick('blague')">Blague</button>
    </div>

    <div class="msg-thread" id="msg-thread">
        <?php foreach ($history as $msg): ?>
            <div class="msg-bubble-wrap <?= $msg['from'] === 'user' ? 'msg-right' : 'msg-left' ?>">
                <div class="msg-bubble <?= $msg['from'] === 'user' ? 'msg-bubble-moi' : 'msg-bubble-bot' ?>">
                    <div class="msg-bubble-meta">
                        <span class="msg-bubble-pseudo"><?= $msg['from'] === 'user' ? htmlspecialchars($userPseudo) : '&#129302; Chatbot' ?></span>
                        <span class="msg-bubble-date"><?= $msg['date'] ?></span>
                    </div>
                    <div class="msg-bubble-contenu"><?= nl2br($msg['contenu']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="" class="msg-send-form" id="chatbot-form">
        <textarea name="contenu" id="chatbot-input" class="msg-textarea" placeholder="Posez votre question au chatbot..." rows="2"></textarea>
        <input type="submit" name="envoyer_bot" value="Envoyer &#10148;" class="msg-btn">
    </form>
</div>

<script>
    const thread = document.getElementById('msg-thread');
    if (thread) thread.scrollTop = thread.scrollHeight;
    function sendQuick(text) { document.getElementById('chatbot-input').value = text; document.getElementById('chatbot-form').submit(); }
    document.getElementById('chatbot-input').addEventListener('keydown', function(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); document.getElementById('chatbot-form').submit(); } });
</script>

<?php require_once 'footer.php'; ?>
