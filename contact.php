<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Erlaubte Werte des Formularfelds "service" (select#cf-service in index.html).
 * Muss mit den <option value="...">-Werten dort uebereinstimmen.
 */
const CONTACT_SERVICE_WHITELIST = [
    '1a Quick-Check remote',
    '1b Aufnahme und Beratung vor Ort',
    '1c Vor-Ort-Check klein',
    '1e Nachprüfung nach Behebung',
    'Sicherheits-Unterweisung',
    'Jahres-Check',
    'Vulnerability Assessment',
    'Penetrationstest',
    'Backup-Strategie Setup',
    'Security-Policy Beratung',
    'AI-Quick-Check',
    'AI-Coding-Quick-Check',
    'Technische Datenschutz-Prüfung',
    'Notfall-Unterstützung',
    'Paket Security Starter',
    'Paket KMU Shield',
    'Vormerkung Laufende Überwachung',
    'Sonstiges',
];

/**
 * Normalisiert den Formularwert "service" gegen die Whitelist.
 * '' (und der vom Frontend gesendete Platzhalter 'Nicht angegeben') bleiben '',
 * unbekannte Werte werden zu 'Sonstiges' (kein Abweisen, damit keine Anfrage verloren geht).
 */
function normalizeService(mixed $raw): string
{
    if (!is_string($raw)) {
        return '';
    }
    $value = trim($raw);
    if ($value === '' || $value === 'Nicht angegeben') {
        return '';
    }
    return in_array($value, CONTACT_SERVICE_WHITELIST, true) ? $value : 'Sonstiges';
}

// Test-Hook: CLI-Skript kann die Funktion laden, ohne den Handler auszufuehren.
if (PHP_SAPI === 'cli' && defined('CONTACT_PHP_TEST_MODE')) {
    return;
}

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
$service = normalizeService($body['service'] ?? '');
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
