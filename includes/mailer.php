<?php
require_once __DIR__ . '/../config.php';
// Load Composer's autoloader
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static function createMailer() {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->setFrom(SMTP_USER, 'CodeMaster Academy');
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return null;
        }
    }

    public static function sendNotification($data) {
        $mail = self::createMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress(ADMIN_EMAIL);
            $mail->Subject = 'New Course Interest';
            
            $body = "New interest in courses:\n\n";
            $body .= "Name: " . $data['name'] . "\n";
            $body .= "Email: " . $data['email'] . "\n";
            $body .= "Course Interest: " . $data['interest'] . "\n";
            
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendConfirmation($email, $name) {
        $mail = self::createMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($email, $name);
            $mail->Subject = 'Welcome to CodeMaster Academy';
            
            $body = "Dear " . $name . ",\n\n";
            $body .= "Thank you for your interest in CodeMaster Academy courses.\n";
            $body .= "We have received your inquiry and will contact you shortly.\n\n";
            $body .= "Best regards,\nCodeMaster Academy Team";
            
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }
}
?> 