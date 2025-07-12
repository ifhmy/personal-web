<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// بيانات النموذج
$name    = isset($_POST['name'])    ? strip_tags(trim($_POST['name'])) : "";
$email   = isset($_POST['email'])   ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : "";
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : "Contact Form Message";
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : "";

// تحقق من reCAPTCHA v3
$recaptcha_secret = '6LdS42crAAAAAGgMDeLn_eViuKpgufB6JykJ5cMP';
$recaptcha_token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

if (empty($recaptcha_token)) {
    echo '<div class="alert alert-danger" style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
          <i class="bi bi-exclamation-triangle-fill" style="font-size: 24px; margin-right: 10px;"></i>
          Please complete the CAPTCHA verification.
        </div>';
    exit;
}

// التحقق من صحة reCAPTCHA مع Google
$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_token
];

$recaptcha_options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($recaptcha_data)
    ]
];

$recaptcha_context = stream_context_create($recaptcha_options);
$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
$recaptcha = json_decode($recaptcha_result);

if (!$recaptcha->success || $recaptcha->score < 0.5) {
    echo '<div class="alert alert-danger" style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
          <i class="bi bi-exclamation-triangle-fill" style="font-size: 24px; margin-right: 10px;"></i>
          CAPTCHA verification failed. Please try again.
        </div>';
    exit;
}

// تحقق من صحة البيانات
if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'cpanel.freehosting.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@ifhmy.com';
        $mail->Password   = 'Ahmed.120';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('support@ifhmy.com', $name);
        $mail->addAddress('support@ifhmy.com');
        $mail->addReplyTo($email, $name);

        $mail->Subject = $subject;
        $mail->Body    = "Name: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

        $mail->send();
        
        echo '<div class="alert alert-success" style="padding: 20px; background: #d4edda; color: #155724; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
              <i class="bi bi-check-circle-fill" style="font-size: 24px; margin-right: 10px;"></i>
              Your message has been sent successfully! I will contact you soon.
            </div>
            <div style="text-align: center; margin-top: 20px;">
              <a href="http://ifhmy.com" class="btn btn-primary" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                Back to Home Page
              </a>
            </div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-danger" style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
              <i class="bi bi-exclamation-triangle-fill" style="font-size: 24px; margin-right: 10px;"></i>
              There was an error sending your message. Please try again later.
            </div>';
    }
} else {
    echo '<div class="alert alert-warning" style="padding: 20px; background: #fff3cd; color: #856404; border-radius: 8px; text-align: center; margin: 20px auto; max-width: 600px;">
          <i class="bi bi-exclamation-circle-fill" style="font-size: 24px; margin-right: 10px;"></i>
          Please fill in all fields correctly.
        </div>';
}
?>
