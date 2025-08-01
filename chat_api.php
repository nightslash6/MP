<?php
session_start();
header('Content-Type: application/json');

// TEMP: Disable CSRF during testing
// In production, re-enable CSRF protection
// if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Invalid CSRF token']);
//     exit;
// }

require 'vendor/autoload.php';

use OpenAI\Client;

$userInput = json_decode(file_get_contents('php://input'), true)['message'] ?? '';

if (empty($userInput)) {
    echo json_encode(['reply' => 'Please enter a message.']);
    exit;
}

$openai = Client::factory()
    ->withApiKey('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')
    ->make();

try {
    $response = $openai->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful cybersecurity assistant.'],
            ['role' => 'user', 'content' => $userInput],
        ],
        'temperature' => 0.7,
        'max_tokens' => 300,
    ]);

    $reply = $response->choices[0]->message->content ?? 'No reply generated.';
    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to contact OpenAI: ' . $e->getMessage()]);
}
