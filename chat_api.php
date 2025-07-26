<?php // chat_api.php
session_start();
header('Content-Type: application/json');
require 'config.php';               // DB connect if you log chats
require 'vendor/autoload.php';      // composer require openai-php/client

use OpenAI\Client as OpenAI;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
    http_response_code(403); echo json_encode(['error'=>'Bad CSRF']); exit;
}

$userMsg = trim($data['message'] ?? '');
if ($userMsg==='') { echo json_encode(['reply'=>'']); exit; }

$openai = OpenAI::factory()
    ->withApiKey(getenv('OPENAI_API_KEY'))
    ->make();

$response = $openai->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role'=>'system','content'=>'You are a friendly cybersecurity assistant.'],
        ['role'=>'user','content'=>$userMsg],
    ],
    'max_tokens' => 300,
    'temperature' => 0.7,
]);

$reply = $response->choices[0]->message->content ?? 'Sorry, I had trouble answering that.';

echo json_encode(['reply'=>$reply]);

// (Optional) save to DB
/*
$conn = db_connect();
$stmt = $conn->prepare("INSERT INTO conversations (user_id,message,is_assistant) VALUES (?,?,?)");
foreach ([$userMsg=>0,$reply=>1] as $m=>$flag){
    $stmt->bind_param("isi", $_SESSION['user_id'], $m, $flag);
    $stmt->execute();
}
$stmt->close(); $conn->close();
*/