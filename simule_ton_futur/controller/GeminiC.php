<?php

class GeminiC
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = 'GEMINI_API_KEY_HERE';
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    }

    public function genererDescription(string $titrePoste, string $competences): string
    {
        $prompt = "Tu es un expert en ressources humaines et rédaction de CV professionnels. "
            . "Génère une description de profil professionnel courte (3-4 phrases maximum) pour un CV. "
            . "Le poste visé est : \"$titrePoste\". ";

        if (!empty($competences)) {
            $prompt .= "Les compétences principales sont : $competences. ";
        }

        $prompt .= "La description doit être en français, professionnelle, percutante et à la première personne. "
            . "Ne mets pas de guillemets autour de la réponse. Réponds uniquement avec la description, sans titre ni explication.";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 300,
            ]
        ];

        $ch = curl_init($this->apiUrl . '?key=' . $this->apiKey);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException("Erreur cURL : $error");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("Erreur API Gemini (HTTP $httpCode)");
        }

        $data = json_decode($response, true);

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new RuntimeException("Réponse vide de l'API Gemini");
        }

        return trim($text);
    }
}
