<?php
/**
 * Contact Form Handler — Cyber Aspis IT-Security
 * PHPMailer via Hostinger SMTP (smtp.hostinger.com:465)
 * SMTP_PASS wird als Docker-Env-Variable übergeben
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

// Nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// JSON-Body parsen
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Honeypot-Check (verstecktes "website"-Feld muss leer sein)
if (!empty($body['website'])) {
    // Silent drop — kein Fehler zurückgeben
    echo json_encode(['success' => true]);
    exit;
}

// Felder validieren
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

// SMTP-Passwort aus Docker-Env-Variable
$smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS');
if (!$smtpPass) {
    error_log('[contact.php] SMTP_PASS env variable not set');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error']);
    exit;
}

// E-Mail-Betreff und Body aufbauen
$serviceLabel = $service !== '' ? htmlspecialchars($service, ENT_QUOTES, 'UTF-8') : '(nicht angegeben)';
$subject = 'Neue Anfrage über cyber-aspis.de — ' . $serviceLabel;

$bodyText = "Neue Kontaktanfrage über cyber-aspis.de\n\n"
    . "Name:        " . $name . "\n"
    . "E-Mail:      " . $email . "\n"
    . "Service:     " . $serviceLabel . "\n"
    . "Nachricht:\n" . $message . "\n\n"
    . "---\n"
    . "Zeitstempel: " . (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i:s T');

// PHPMailer konfigurieren
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
