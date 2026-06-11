<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\AIService;

class AIController extends Controller
{
    public function chat(): void
    {
        $body = $this->getBody();
        $message = $body['message'] ?? '';

        if (empty($message)) {
            $this->json(['error' => 'Message is required'], 400);
            return;
        }

        $result = AIService::chat(Auth::id(), $message);
        $this->json($result);
    }

    public function analyzeSymptoms(): void
    {
        $body = $this->getBody();
        $symptoms = $body['symptoms'] ?? [];

        if (empty($symptoms)) {
            $this->json(['error' => 'Symptoms are required'], 400);
            return;
        }

        $result = AIService::analyzeSymptoms($symptoms);
        $this->json($result);
    }
}
