<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\RagDocument;

class OpenRouterService
{
    public static function chat(string $message): array
    {
        $config = require __DIR__ . '/../../config/app.php';

        $apiKey = '';
        $model = '';

        try {
            $apiKey = Setting::get('openrouter_api_key');
            $model = Setting::get('openrouter_model');
        } catch (\Exception $e) {
            $apiKey = '';
            $model = '';
        }

        if (empty($apiKey)) {
            $apiKey = $config['openrouter_api_key'] ?: getenv('OPENROUTER_API_KEY');
        }
        if (empty($apiKey)) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile) && is_readable($envFile)) {
                $env = parse_ini_file($envFile);
                $apiKey = $env['OPENROUTER_API_KEY'] ?? '';
            }
        }

        if (empty($model)) {
            $model = $config['openrouter_model'] ?? 'openai/gpt-oss-120b:free';
        }

        // Build system prompt with RAG context
        $systemPrompt = 'You are Althia, a helpful and friendly AI health assistant. Provide clear, accurate, and compassionate responses about healthcare topics. Always remind users to consult a real doctor for medical advice. Keep responses concise and helpful.';

        try {
            $ragEnabled = Setting::get('rag_enabled', '1');
            if ($ragEnabled === '1') {
                $docs = RagDocument::search($message);
                if (!empty($docs)) {
                    $context = "Here is reference information from our knowledge base that may help answer the user's question:\n\n";
                    foreach ($docs as $doc) {
                        $context .= "--- {$doc['title']} ---\n{$doc['content']}\n\n";
                    }
                    $systemPrompt .= "\n\n$context";
                }
            }
        } catch (\Exception $e) {
            // RAG unavailable — proceed without
        }

        if (empty($apiKey)) {
            return [
                'response' => 'AI service is not configured. Please contact the administrator.',
            ];
        }

        $payload = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ],
            'max_tokens' => 500,
        ]);

        try {
            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'HTTP-Referer: ' . ($config['url'] ?? 'https://oumaima.ddev.site'),
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Althia/1.0',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            return [
                'response' => 'Sorry, I encountered a connection issue. Please try again later.',
            ];
        }

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
