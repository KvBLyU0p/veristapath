<?php
// Telegram bot credentials
$botToken = "7676566666:AAE0DP1B693BxiMqCQ0IIxjWyLcnAueevk8";
$chatId   = "907528874";

// Get form fields (matching your IDs/names)
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$company  = $_POST['company'] ?? '';
$subject  = $_POST['subject'] ?? '';
$message  = $_POST['message'] ?? '';

// Grab IP + location
$ip = $_SERVER['REMOTE_ADDR'];
$location = "Unknown";
$city = $state = $zip = "";

$response = @file_get_contents("http://ipinfo.io/{$ip}/json");
if ($response) {
    $data = json_decode($response, true);
    $city  = $data['city'] ?? '';
    $state = $data['region'] ?? '';
    $zip   = $data['postal'] ?? '';
    $location = "$city, $state $zip";
}

// Format Telegram message
$text = "
📄 New Form Submission
------------------------
👤 Name: $name
📧 Email: $email
🏢 Company: $company
📌 Subject: $subject
📝 Message: $message

🌍 IP: $ip
🏙️ Location: $location
";

// Send text message first
file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
    'chat_id' => $chatId,
    'text'    => $text
]));

// Handle file upload if there's an attachment (optional)
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $filePath = $_FILES['resume']['tmp_name'];
    $fileName = $_FILES['resume']['name'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.telegram.org/bot$botToken/sendDocument",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $chatId,
            'document' => new CURLFile($filePath, $_FILES['resume']['type'], $fileName)
        ]
    ]);
    curl_exec($curl);
    curl_close($curl);
}

// Redirect after success
header("Location: https://veritaspathsolutions.pages.dev/Home");
exit;
?>