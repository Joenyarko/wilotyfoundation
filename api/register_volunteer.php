<?php
// api/register_volunteer.php
// AJAX API endpoint for volunteer registrations

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Volunteer.php';
require_once __DIR__ . '/../config/Mailer.php';

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

    $name = sanitize_input($input['name'] ?? '');
    $email = sanitize_input($input['email'] ?? '');
    $phone = sanitize_input($input['phone'] ?? '');
    $location = sanitize_input($input['location'] ?? '');
    $skills = sanitize_input($input['skills'] ?? '');
    $why_volunteer = sanitize_input($input['why_volunteer'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($location) || empty($skills) || empty($why_volunteer)) {
        throw new Exception("Please fill in all the required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    $volunteerModel = new Volunteer();
    $volunteer_id = $volunteerModel->create($name, $email, $phone, $location, $skills, $why_volunteer);

    if ($volunteer_id) {
        // Send email notification to admin
        $mailer = new Mailer();
        $subject = "New Volunteer Application: $name";
        $body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #ff6b00; padding: 20px; text-align: center;'>
                <h2 style='color: #ffffff; margin: 0;'>New Volunteer Alert!</h2>
            </div>
            <div style='padding: 30px; background-color: #fafafa;'>
                <p style='font-size: 16px; line-height: 1.5;'>Great news! <strong>$name</strong> is willing to offer their time and skills to support the Wiloty Foundation.</p>
                
                <h3 style='border-bottom: 2px solid #ff6b00; padding-bottom: 5px; color: #ff6b00;'>Applicant Details</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><a href='mailto:$email' style='color: #0065f2;'>$email</a></td></tr>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$phone</td></tr>
                    <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Location:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$location</td></tr>
                </table>

                <h3 style='border-bottom: 2px solid #ff6b00; padding-bottom: 5px; color: #ff6b00;'>Skills & Expertise</h3>
                <p style='background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>$skills</p>

                <h3 style='border-bottom: 2px solid #ff6b00; padding-bottom: 5px; color: #ff6b00;'>Why They Want to Join</h3>
                <p style='background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; font-style: italic;'>\"".nl2br($why_volunteer)."\"</p>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <p style='font-size: 14px; color: #777;'>Log into your admin dashboard to approve or review this application.</p>
                </div>
            </div>
        </div>";
        $mailer->sendEmail('wilotyfoundation@gmail.com', $subject, $body);

        echo json_encode([
            "success" => true,
            "message" => "Your volunteer application has been submitted successfully! Check your email for confirmation.",
            "volunteer_id" => $volunteer_id
        ]);
    } else {
        throw new Exception("Failed to register volunteer. Please try again.");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
