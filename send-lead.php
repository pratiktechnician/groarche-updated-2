<?php
// Native Hostinger PHP Backend Lead Mailer for GroArche Learning Solutions
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Read JSON payload
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || (!isset($data['name']) && !isset($data['email']) && !isset($data['phone']))) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid payload"]);
    exit;
}

$to = "contact@groarche.pro";
$name = !empty($data['name']) ? $data['name'] : 'Website Visitor';
$email = !empty($data['email']) ? $data['email'] : 'N/A';
$phone = !empty($data['phone']) ? $data['phone'] : 'N/A';
$query = !empty($data['visitorQuery']) ? $data['visitorQuery'] : (!empty($data['requirement']) ? $data['requirement'] : 'General Enquiry');
$date = !empty($data['dateAndTime']) ? $data['dateAndTime'] : date('Y-m-d H:i:s');
$page = !empty($data['pageUrl']) ? $data['pageUrl'] : 'https://groarche.pro/';
$source = !empty($data['source']) ? $data['source'] : 'GroArche Website Chatbot';

$subject = "NEW GROARCHE WEBSITE ENQUIRY: " . $name;

$message = "===========================================\n";
$message .= "NEW GROARCHE WEBSITE ENQUIRY\n";
$message .= "===========================================\n\n";
$message .= "Name: " . $name . "\n";
$message .= "Email: " . $email . "\n";
$message .= "Phone: " . $phone . "\n";
$message .= "Visitor Query: " . $query . "\n";
$message .= "Date & Time: " . $date . "\n";
$message .= "Page URL: " . $page . "\n";
$message .= "Source: " . $source . "\n\n";
$message .= "===========================================\n";

$headers = "From: GroArche Website <noreply@groarche.pro>\r\n";
if ($email !== 'N/A') {
    $headers .= "Reply-To: " . $email . "\r\n";
}
$headers .= "X-Mailer: PHP/" . phpversion();

// 1. Dispatch Email to contact@groarche.pro
$mailSent = @mail($to, $subject, $message, $headers);

// 2. Append to local server JSON backup (leads_log.json)
$logFile = __DIR__ . '/leads_log.json';
$logEntry = [
    'id' => 'lead_' . time(),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'visitorQuery' => $query,
    'dateAndTime' => $date,
    'pageUrl' => $page,
    'source' => $source,
    'emailSent' => $mailSent ? true : false
];

$existing = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($existing)) $existing = [];
array_unshift($existing, $logEntry);
@file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT));

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Lead processed successfully",
    "emailSent" => $mailSent ? true : false
]);
?>
