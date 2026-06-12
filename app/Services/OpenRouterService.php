<?php
namespace App\Services;

class OpenRouterService
{
    public static function chat(string $message): array
    {
        $config = require __DIR__ . '/../../config/app.php';
        $apiKey = $config['openrouter_api_key'] ?: getenv('OPENROUTER_API_KEY');
        if (empty($apiKey)) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile) && is_readable($envFile)) {
                $env = parse_ini_file($envFile);
                $apiKey = $env['OPENROUTER_API_KEY'] ?? '';
            }
        }
        $model = $config['openrouter_model'] ?? 'openai/gpt-oss-120b:free';

        if (empty($apiKey)) {
            return [
                'response' => 'AI service is not configured. Please contact the administrator.',
            ];
        }

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . ($config['url'] ?? 'https://oumaima.ddev.site'),
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are Althia, a helpful and friendly AI health assistant. Provide clear, accurate, and compassionate responses about healthcare topics. Always remind users to consult a real doctor for medical advice. Keep responses concise and helpful.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                'max_tokens' => 500,
            ]),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'response' => 'Sorry, I encountered a connection issue. Please try again later.',
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !$data) {
            $errorMsg = $data['error']['message'] ?? 'Unknown error';
            return [
                'response' => 'Sorry, I had trouble processing your request. Please try again.',
            ];
        }

        $reply = $data['choices'][0]['message']['content'] ?? '';

        return [
            'response' => $reply,
        ];
    }
}
