<?php
// api/join_event.php
// AJAX API endpoint for event registration joins

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit();
}

try {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    $event_id = isset($input['event_id']) ? (int)$input['event_id'] : 0;
    $name = sanitize_input($input['name'] ?? '');
    $email = sanitize_input($input['email'] ?? '');
    $phone = sanitize_input($input['phone'] ?? '');

    // Validation
    if ($event_id <= 0 || empty($name) || empty($email) || empty($phone)) {
        throw new Exception("Please provide your name, email, and contact number.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    $eventModel = new Event();
    
    // Check if event is paid
    $event_details = $eventModel->getById($event_id);
    if (!$event_details) {
        throw new Exception("Event not found.");
    }

    $payment_status = 'n/a';
    $tx_ref = null;

    if ($event_details['is_free'] == 0 && $event_details['price'] > 0) {
        $paystack_reference = sanitize_input($input['paystack_reference'] ?? '');
        if (empty($paystack_reference)) {
            throw new Exception("Payment reference missing. Payment could not be verified.");
        }

        // Verify transaction with Paystack API securely
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($paystack_reference);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ]);
        $response_json = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("Payment verification service unreachable.");
        }

        $result = json_decode($response_json, true);
        if (!$result || !isset($result['status']) || $result['status'] !== true || $result['data']['status'] !== 'success') {
            throw new Exception("Payment verification failed. If you were charged, please contact support.");
        }

        // Check exact amount
        $paid_amount = $result['data']['amount'] / 100;
        if (abs($paid_amount - $event_details['price']) > 0.01) { // Floating point safe check
            throw new Exception("Payment amount mismatch. Paid GHS $paid_amount, but event costs GHS {$event_details['price']}.");
        }

        $payment_status = 'completed';
        $tx_ref = $paystack_reference;
    }

    $registration_id = $eventModel->joinEvent($event_id, $name, $email, $phone, $payment_status, $tx_ref);

    if ($registration_id) {
        echo json_encode([
            "success" => true,
            "message" => "Seat confirmed! We sent a registration confirmation email to you.",
            "registration_id" => $registration_id
        ]);
    } else {
        throw new Exception("Failed to join event. Please try again.");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
