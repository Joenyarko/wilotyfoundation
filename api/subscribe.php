<?php
// api/subscribe.php
// AJAX API endpoint for newsletter subscriptions

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Subscriber.php';
require_once __DIR__ . '/../config/Mailer.php';

header("Content-Type: application/json");
send_api_cors_headers("POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit();
}

if (!check_rate_limit('api_subscribe', 5, 60)) {
    http_response_code(429);
    echo json_encode(["success" => false, "message" => "Too many subscription requests. Please wait a minute and try again."]);
    exit();
}

try {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    $email = sanitize_input($input['email'] ?? '');

    if (empty($email)) {
        throw new Exception("Please enter your email address.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    $subscriberModel = new Subscriber();
    $result = $subscriberModel->subscribe($email);

    if ($result) {
        // Send email notification to admin
        $mailer = new Mailer();
        $subject = "New Newsletter Subscriber";
        $body = "
            <h3>New Newsletter Subscription</h3>
            <p><strong>Email:</strong> $email</p>
        ";
        $mailer->sendEmail('wilotyfoundation@gmail.com', $subject, $body);

        echo json_encode([
            "success" => true,
            "message" => "Thank you for subscribing to our newsletter! Check your inbox for updates."
        ]);
    } else {
        throw new Exception("Failed to subscribe. Please try again later.");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
