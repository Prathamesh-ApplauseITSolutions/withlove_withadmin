<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 🔹 Sanitize inputs
    $name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $phone   = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
    $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
    $gender  = trim(filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS));
    $date    = trim(filter_input(INPUT_POST, 'donationDate', FILTER_SANITIZE_SPECIAL_CHARS));
    $mode    = trim(filter_input(INPUT_POST, 'donationMode', FILTER_SANITIZE_SPECIAL_CHARS));
    $availability = trim(filter_input(INPUT_POST, 'availability', FILTER_SANITIZE_SPECIAL_CHARS));
    $skills  = trim(filter_input(INPUT_POST, 'skills', FILTER_SANITIZE_SPECIAL_CHARS));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

   // Validation
        if (strlen($name) < 3) {
            $error_message = "Name must contain at least 3 letters";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address";
        } elseif (strlen($phone) != 10) {
            $error_message = "Mobile Number must be exactly 10 digits";
        } elseif (strlen($address) < 5) {
            $error_message = "Address must contain at least 5 characters";
        } elseif (empty($gender)) {
            $error_message = "Please select your gender";
        } elseif (empty($date)) {
            $error_message = "Please select a date";
        } elseif (empty($mode)) {
            $error_message = "Please select donation mode";
        } elseif (strlen($availability) < 2) {
            $error_message = "Please specify your availability";
        } elseif (strlen($skills) < 2) {
            $error_message = "Please mention at least one skill";
        } elseif (strlen($message) < 5) {
            $error_message = "Additional information must contain at least 5 characters";
        } else {
            // Send email using PHPMailer
            try {
                $mail = new PHPMailer(true);
            } catch (Exception $e) {
                $error_message = "❌ Error: PHPMailer not available. " . $e->getMessage();
            }
        }
    
    // 🔹 Send email
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'applauseitdev@gmail.com';
        $mail->Password   = 'okyc smgd vhdk vyah'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('applauseitdev@gmail.com', 'Volunteer Form');
        $mail->addReplyTo($email, $name);
        $mail->addAddress('pkumathkar279@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Volunteer Application - " . $name;

        $mail->Body = "
            <h3>New Volunteer Application Submission</h3>
            <p><b>Full Name:</b> {$name}</p>
            <p><b>Email:</b> {$email}</p>
            <p><b>Phone:</b> {$phone}</p>
            <p><b>Address:</b> {$address}</p>
            <p><b>Gender:</b> {$gender}</p>
            <p><b>Available Date:</b> {$date}</p>
            <p><b>Donation Mode:</b> {$mode}</p>
            <p><b>Availability:</b> {$availability}</p>
            <p><b>Skills:</b> {$skills}</p>
            <p><b>Additional Information:</b><br>" . nl2br($message) . "</p>
        ";

        $mail->send();

        header("Location: volunteer.html?success=1");
        exit();

    } catch (Exception $e) {
        echo "Message could not be sent. Error: " . $mail->ErrorInfo;
    }
}