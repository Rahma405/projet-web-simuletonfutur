<?php

class ChatbotC
{
    private array $reponses = [
        'bonjour'       => 'Bonjour ! Comment puis-je vous aider aujourd\'hui ?',
        'salut'         => 'Salut ! Que puis-je faire pour vous ?',
        'bonsoir'       => 'Bonsoir ! Je suis là pour vous aider.',
        'hey'           => 'Hey ! Bienvenue sur la messagerie. Posez-moi vos questions !',
        'salam'         => 'Wa alaykom essalam ! Comment puis-je vous aider ?',
        'aide'          => 'Voici ce que je peux faire :
- Répondre à vos questions sur la messagerie
- Vous guider pour créer une conversation
- Vous expliquer les fonctionnalités (recherche, export PDF, messages lu/non lu)
Tapez un mot-clé pour en savoir plus !',
        'help'          => 'Je suis le chatbot de la messagerie ! Tapez "aide" pour voir les commandes disponibles.',
        'conversation'  => 'Pour créer une conversation, cliquez sur "+ Nouvelle conversation" depuis la page des conversations. Choisissez un destinataire et un sujet.',
        'recherche'     => 'La fonctionnalité de recherche vous permet de trouver des messages par mot-clé. Accédez-y depuis le menu "Recherche".',
        'pdf'           => 'Vous pouvez exporter une conversation en PDF ! Ouvrez une conversation et cliquez sur "Exporter PDF" en haut.',
        'export'        => 'Pour exporter une conversation, ouvrez-la et cliquez sur le bouton "Exporter PDF" en haut à droite.',
        'message'       => 'Pour envoyer un message, ouvrez une conversation et tapez votre texte dans la zone en bas, puis cliquez sur "Envoyer".',
        'supprimer'     => 'Vous pouvez supprimer vos propres messages en cliquant sur "Supprimer" sous le message.',
        'modifier'      => 'Vous pouvez modifier vos propres messages en cliquant sur "Modifier" sous le message.',
        'lu'            => 'Les messages sont automatiquement marqués comme "Lu" quand le destinataire ouvre la conversation.',
        'non lu'        => 'Les messages non lus apparaissent avec un indicateur dans la liste des conversations.',
        'projet'        => 'Ce projet est un module de messagerie développé en PHP MVC pour la plateforme Simule Ton Futur - Esprit 2025-2026.',
        'esprit'        => 'Esprit School of Engineering - Workshop PHP 2025-2026.',
        'fonctionnalités' => 'Les fonctionnalités : Conversations, Messages, Lu/Non lu, Recherche, Export PDF, Chatbot, Dashboard, Blocage, Réactions, Photos.',
        'merci'         => 'De rien ! N\'hésitez pas si vous avez d\'autres questions.',
        'au revoir'     => 'Au revoir ! Bonne journée !',
        'bye'           => 'À bientôt !',
        'blague'        => 'Pourquoi les développeurs PHP n\'aiment pas la nature ? Parce qu\'il y a trop de bugs ! 😄',
        'qui es-tu'     => 'Je suis le chatbot de la messagerie Simule Ton Futur !',
    ];

    private string $reponseDefaut = 'Désolé, je n\'ai pas compris votre message. Tapez "aide" pour voir ce que je peux faire !';

    public function genererReponse(string $message): string
    {
        $message = mb_strtolower(trim($message));
        foreach ($this->reponses as $motCle => $reponse) {
            if (mb_strpos($message, $motCle) !== false) return $reponse;
        }
        return $this->reponseDefaut;
    }

    public function getMotsCles(): array { return array_keys($this->reponses); }
}
