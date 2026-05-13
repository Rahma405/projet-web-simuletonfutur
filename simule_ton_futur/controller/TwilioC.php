<?php

require_once __DIR__ . '/ConfigTwilio.php';
require_once __DIR__ . '/../config.php';

class TwilioC
{
    private array $erreurs = [];

    public function envoyerSMS(string $to, string $message): bool
    {
        $this->erreurs = [];
        if (!ConfigTwilio::ENABLED) return true;

        $sid   = ConfigTwilio::ACCOUNT_SID;
        $token = ConfigTwilio::AUTH_TOKEN;
        $from  = ConfigTwilio::TWILIO_NUMBER;
        $url   = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['To' => $to, 'From' => $from, 'Body' => $message]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "$sid:$token",
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) { $this->erreurs[] = "Erreur cURL : $error"; return false; }
        if ($httpCode >= 400) {
            $body = json_decode($response, true);
            $this->erreurs[] = "Erreur Twilio : " . ($body['message'] ?? 'Erreur inconnue');
            return false;
        }
        return true;
    }

    public function notifierNouveauMessage(int $destinataireId, string $pseudoExpediteur, string $contenuMessage): bool
    {
        try {
            $pdo  = Config::getConnexion();
            $stmt = $pdo->prepare("SELECT telephone, prenom FROM utilisateur WHERE idUtilisateur = :id");
            $stmt->execute(['id' => $destinataireId]);
            $user = $stmt->fetch();

            if (!$user || empty($user['telephone'])) return false;

            $apercu = mb_substr($contenuMessage, 0, 100);
            if (mb_strlen($contenuMessage) > 100) $apercu .= '...';
            $sms = "Messagerie STF - Nouveau message de {$pseudoExpediteur} :\n\"{$apercu}\"";
            return $this->envoyerSMS($user['telephone'], $sms);
        } catch (PDOException $e) { $this->erreurs[] = "Erreur BD : " . $e->getMessage(); return false; }
    }

    public function getErreurs(): array { return $this->erreurs; }
}
