<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

if (!empty($body['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

$name    = trim($body['name']    ?? '');
$email   = trim($body['email']   ?? '');
$service = trim($body['service'] ?? '');
$message = trim($body['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

// Passwort aus Datei lesen (umgeht PHP-FPM env-Variable Probleme)
$passFile = '/run/smtp_pass';
if (!file_exists($passFile)) {
    error_log('[contact.php] smtp_pass file not found');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error']);
    exit;
}
$smtpPass = trim(file_get_contents($passFile));

$serviceLabel = $service !== '' ? htmlspecialchars($service, ENT_QUOTES, 'UTF-8') : '(nicht angegeben)';
$subject = 'Neue Anfrage ueber cyber-aspis.de - ' . $serviceLabel;
$bodyText = "Neue Kontaktanfrage ueber cyber-aspis.de\n\n"
    . "Name:        " . $name . "\n"
    . "E-Mail:      " . $email . "\n"
    . "Service:     " . $serviceLabel . "\n"
    . "Nachricht:\n" . $message . "\n\n"
    . "---\n"
    . "Zeitstempel: " . (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i:s T');

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kontakt@cyber-aspis.de';
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom('kontakt@cyber-aspis.de', 'Cyber Aspis Website');
    $mail->addAddress('kontakt@cyber-aspis.de', 'Georgios Papagiannis');
    $mail->addReplyTo($email, $name);
    $mail->Subject = $subject;
    $mail->Body    = $bodyText;
    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('[contact.php] Mailer error: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Mail delivery failed']);
}
