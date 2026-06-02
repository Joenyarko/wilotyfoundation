<?php
// api/donate.php
// AJAX API endpoint for donation pledges

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../config/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit();
}

try {
    // Standard input reading (supports AJAX JSON and urlencoded)
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    $name = sanitize_input($input['name'] ?? '');
    $email = sanitize_input($input['email'] ?? '');
    $type = sanitize_input($input['type'] ?? '');
    $amount = isset($input['amount']) ? (float)$input['amount'] : null;
    $item_description = sanitize_input($input['item_description'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($type)) {
        throw new Exception("Please provide your name, email, and donation type.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    if ($type !== 'money' && $type !== 'item') {
        throw new Exception("Invalid donation type specified.");
    }

    if ($type === 'money') {
        $paystack_reference = sanitize_input($input['paystack_reference'] ?? '');
        if (empty($paystack_reference)) {
            throw new Exception("Payment reference missing. Payment could not be verified.");
        }

        // Prevent Replay Attacks: Check if this transaction was already processed
        $donationModel = new Donation();
        if ($donationModel->getByTxRef($paystack_reference)) {
            throw new Exception("This payment has already been processed.");
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

        // Use verified data
        $tx_ref = $paystack_reference;
        $amount = $result['data']['amount'] / 100; 

    } else {
        if (empty($item_description)) {
            throw new Exception("Please describe the physical items you wish to donate.");
        }
        $amount = null;
        $tx_ref = null;
    }

    // Insert record as pending initially
    $donationModel = new Donation();
    $donation_id = $donationModel->create($name, $email, $type, $amount, $item_description, 'pending', $tx_ref);

    if ($donation_id) {
        // If money, automatically complete it to trigger the donor Thank You email securely
        if ($type === 'money') {
            $donationModel->updateStatus($tx_ref, 'completed');
        }
        // Send email notification to admin
        $mailer = new Mailer();
        $subject = "New Donation Pledge: $name";
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #00b312; padding: 20px; text-align: center;'>
                <h2 style='color: #ffffff; margin: 0;'>New Donation Pledge!</h2>
            </div>
            <div style='padding: 30px; background-color: #fafafa;'>
                <p style='font-size: 16px; line-height: 1.5;'>Fantastic news! <strong>$name</strong> has just pledged a donation to the Wiloty Foundation.</p>
                
                <h3 style='border-bottom: 2px solid #00b312; padding-bottom: 5px; color: #00b312;'>Donor Details</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><a href='mailto:$email' style='color: #0065f2;'>$email</a></td></tr>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Donation Type:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>" . ucfirst($type) . "</td></tr>
                </table>";
        
        if ($type === 'money') {
            $body .= "
                <h3 style='border-bottom: 2px solid #00b312; padding-bottom: 5px; color: #00b312;'>Amount Pledged</h3>
                <p style='background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; font-size: 18px; font-weight: bold;'>GHS " . number_format($amount, 2) . "</p>";
        } else {
            $body .= "
                <h3 style='border-bottom: 2px solid #00b312; padding-bottom: 5px; color: #00b312;'>Items Pledged</h3>
                <p style='background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; font-style: italic;'>\"".nl2br($item_description)."\"</p>";
        }

        $body .= "
                <div style='text-align: center; margin-top: 30px;'>
                    <p style='font-size: 14px; color: #777;'>Log into your admin dashboard to review and confirm this pledge.</p>
                </div>
            </div>
        </div>";

        $mailer->sendEmail('wilotyfoundation@gmail.com', $subject, $body);

        $response = [
            "success" => true,
            "message" => $type === 'money' 
                ? "Donation pledge of GHS $amount registered successfully!" 
                : "Physical item donation pledge registered successfully!",
            "donation_id" => $donation_id
        ];

        echo json_encode($response);
    } else {
        throw new Exception("An error occurred while saving your donation. Please try again.");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
