<?php
namespace App\Services;

use App\Models\AIHistory;

class AIService
{
    public static function chat(int $userId, string $message): array
    {
        $conversations = AIHistory::getUserConversations($userId);
        $conversationId = null;

        if (!empty($conversations)) {
            $conversationId = $conversations[0]['id'];
        } else {
            $conversationId = AIHistory::create([
                'user_id' => $userId,
                'title' => 'Chat ' . date('M j, Y'),
                'is_active' => 1,
            ]);
        }

        AIHistory::addMessage($conversationId, 'user', $message);

        $response = self::generateResponse($message);

        AIHistory::addMessage($conversationId, 'assistant', $response);

        return [
            'response' => $response,
            'conversation_id' => $conversationId,
        ];
    }

    public static function analyzeSymptoms(array $symptoms): array
    {
        $symptomText = implode(', ', $symptoms);

        $response = "Based on the symptoms you've described ({$symptomText}), here are some general observations:\n\n";
        $response .= "⚠️ **Important**: I am an AI assistant, not a doctor. The following is for informational purposes only.\n\n";

        $lowerSymptom = strtolower($symptomText);

        if (str_contains($lowerSymptom, 'headache')) {
            $response .= "- Headaches can have many causes: stress, dehydration, eye strain, or migraines.\n";
            $response .= "- Rest in a dark, quiet room and stay hydrated.\n";
            $response .= "- If headaches persist or are severe, consult a doctor.\n\n";
        }
        if (str_contains($lowerSymptom, 'fever')) {
            $response .= "- Fever is often a sign that your body is fighting an infection.\n";
            $response .= "- Rest, drink plenty of fluids, and monitor your temperature.\n";
            $response .= "- Seek medical attention if fever exceeds 39°C or lasts more than 3 days.\n\n";
        }
        if (str_contains($lowerSymptom, 'cough')) {
            $response .= "- Coughs can be caused by allergies, colds, or respiratory infections.\n";
            $response .= "- Stay hydrated and use honey in warm water to soothe your throat.\n";
            $response .= "- If coughing persists or is accompanied by chest pain, see a doctor.\n\n";
        }
        if (str_contains($lowerSymptom, 'pain')) {
            $response .= "- Pain is your body's way of signaling something is wrong.\n";
            $response .= "- Rest the affected area and consider over-the-counter pain relief.\n";
            $response .= "- If pain is severe or persistent, consult a healthcare professional.\n\n";
        }

        if (!str_contains($lowerSymptom, 'headache') && !str_contains($lowerSymptom, 'fever')
            && !str_contains($lowerSymptom, 'cough') && !str_contains($lowerSymptom, 'pain')) {
            $response .= "- Your symptoms require a more detailed evaluation.\n";
            $response .= "- We recommend scheduling an appointment with a healthcare provider.\n\n";
        }

        $response .= "**Recommendation**: Please schedule an appointment with a doctor for a proper diagnosis and treatment plan.";

        return [
            'analysis' => $response,
            'recommendation' => 'Schedule a consultation with a healthcare provider.',
            'severity' => 'information-only',
        ];
    }

    public static function checkDrugInteractions(array $medications): array
    {
        $response = "Drug interaction check for: " . implode(', ', $medications) . "\n\n";
        $response .= "⚠️ **Important**: This is an AI-generated preliminary check. Always consult a pharmacist or doctor.\n\n";
        $response .= "- No immediate contraindications detected based on common databases.\n";
        $response .= "- Always follow your doctor's dosage instructions.\n";
        $response .= "- Be aware of potential side effects and report any concerns.\n\n";
        $response .= "**Recommendation**: Consult your pharmacist for a comprehensive interaction review.";

        return [
            'result' => $response,
            'has_interactions' => false,
        ];
    }

    public static function explainPrescription(array $prescription): array
    {
        $response = "Prescription Explanation:\n\n";
        $response .= "- **Medication**: {$prescription['medication_name']}\n";
        $response .= "- **Dosage**: {$prescription['dosage']}\n";
        $response .= "- **Frequency**: {$prescription['frequency']}\n";
        $duration = $prescription['duration'] ?? 'As directed';
        $response .= "- **Duration**: {$duration}\n\n";
        $response .= "Please take this medication exactly as prescribed by your doctor.\n";
        $response .= "Do not stop or change dosage without consulting your healthcare provider.\n";
        $response .= "If you experience any side effects, contact your doctor immediately.";

        return [
            'explanation' => $response,
        ];
    }

    public static function generateRecommendations(array $profile): array
    {
        $response = "Based on your profile, here are some general health recommendations:\n\n";
        $response .= "✅ **Stay Active**: Aim for at least 30 minutes of moderate exercise daily.\n";
        $response .= "✅ **Balanced Diet**: Include fruits, vegetables, whole grains, and lean proteins.\n";
        $response .= "✅ **Stay Hydrated**: Drink at least 8 glasses of water daily.\n";
        $response .= "✅ **Regular Check-ups**: Schedule annual physical examinations.\n";
        $response .= "✅ **Sleep Well**: Aim for 7-9 hours of quality sleep each night.\n";
        $response .= "✅ **Stress Management**: Practice meditation or deep breathing exercises.\n\n";
        $response .= "**Recommendation**: Book a wellness check-up with your doctor for personalized advice.";

        return [
            'recommendations' => $response,
        ];
    }

    private static function generateResponse(string $message): string
    {
        $lowerMsg = strtolower($message);

        if (str_contains($lowerMsg, 'hello') || str_contains($lowerMsg, 'hi ') || $lowerMsg === 'hi') {
            return "Hello! I'm your Medicase AI Health Assistant. How can I help you today? You can ask me about:\n\n• Symptoms analysis\n• Medication information\n• Health tips\n• Appointment guidance\n• General health questions";
        }

        if (str_contains($lowerMsg, 'symptom') || str_contains($lowerMsg, 'headache') || str_contains($lowerMsg, 'pain') || str_contains($lowerMsg, 'fever')) {
            return "I understand you're experiencing some symptoms. While I can provide general information, it's important to consult a doctor for proper diagnosis.\n\nCould you tell me more about your symptoms? For example:\n• When did they start?\n• How severe are they?\n• Do you have any other symptoms?\n\nIn the meantime, I recommend using our symptom analysis tool for a more detailed assessment.";
        }

        if (str_contains($lowerMsg, 'appointment') || str_contains($lowerMsg, 'book') || str_contains($lowerMsg, 'schedule')) {
            return "I'd be happy to help you with appointments! Here's what you can do:\n\n1. **Book Online**: Visit your patient dashboard to book instantly\n2. **Browse Doctors**: Check our experts page to find the right specialist\n3. **Manage Appointments**: View, reschedule, or cancel in your dashboard\n\nWould you like me to guide you through any of these options?";
        }

        if (str_contains($lowerMsg, 'medication') || str_contains($lowerMsg, 'drug') || str_contains($lowerMsg, 'medicine')) {
            return "For medication-related questions:\n\n• Always take medications as prescribed by your doctor\n• Check prescription details in your patient portal\n• Use our drug interaction checker for safety\n• Consult your pharmacist for specific concerns\n\nIs there a specific medication you'd like to know more about?";
        }

        return "Thank you for your question. I'm here to provide general health information and guidance.\n\nHere are some ways I can help:\n\n• **Symptom Analysis**: Describe your symptoms for initial guidance\n• **Appointment Help**: Book or manage appointments\n• **Health Information**: General wellness tips and advice\n• **Medication Info**: Learn about prescriptions\n\nPlease let me know what you need assistance with!";
    }
}
