
<?php
$phpmailer_available = false; // or true if you're using PHPMailer

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
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

   // Validation
        if (strlen($name) < 3) {
            $error_message = "Name must contain at least 3 letters";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address";
        } elseif (strlen($phone) != 10) {
            $error_message = "Mobile Number must be exactly 10 digits";
        } elseif (strlen($subject) < 3) {
            $error_message = "Subject must contain at least 3 letters";
        } elseif (strlen($message) < 5) {
            $error_message = "Message must contain at least 5 characters";
        } else {
            // Send email using PHPMailer or fallback
            if ($phpmailer_available) {
                try {
                    $mail = new PHPMailer(true);
                } catch (Exception $e) {
                    $error_message = "❌ Error: PHPMailer not available. " . $e->getMessage();
                    $phpmailer_available = false;
                }
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
        $mail->setFrom('applauseitdev@gmail.com', 'Contact Form');
        $mail->addReplyTo($email, $name);
        $mail->addAddress('pkumathkar279@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body = "
            <h3>New Contact Form Submission</h3>
            <p><b>Name:</b> {$name}</p>
            <p><b>Email:</b> {$email}</p>
            <p><b>Phone:</b> {$phone}</p>
            <p><b>Message:</b><br>" . nl2br($message) . "</p>
        ";

        $mail->send();

        header("Location: contact.html?success=1");
        exit();

    } catch (Exception $e) {
        echo "Message could not be sent. Error: " . $mail->ErrorInfo;
    }
}
