<?php
/* ============================================================
   Sween Travels lead handler
   Receives contact, visa-enquiry, and newsletter forms.
   Validates input, blocks spam with a honeypot and simple rate
   limit, emails the business, sends a lightweight auto-reply,
   and stores a private CSV backup.

   Production note: PHP mail() works on many cPanel hosts, but
   SMTP through PHPMailer/Resend/SendGrid is recommended for
   stronger deliverability.
============================================================ */

// ---- Settings -------------------------------------------------
$TO_EMAIL   = getenv('SWEEN_TO_EMAIL') ?: 'sweentravelslimited@gmail.com';
$FROM_EMAIL = getenv('SWEEN_FROM_EMAIL') ?: 'no-reply@sweentravels.co.ke';
$SITE_NAME  = getenv('SWEEN_SITE_NAME') ?: 'Sween Travels';
$PRIVATE_DIR = __DIR__ . '/private';
$LOG_FILE   = $PRIVATE_DIR . '/leads.csv';
$RATE_FILE  = $PRIVATE_DIR . '/rate-limit.json';

// ---- Helpers --------------------------------------------------
function wants_json() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return stripos($accept, 'application/json') !== false
        || strtolower($xrw) === 'xmlhttprequest';
}

function safe_redirect_target() {
    $fallback = 'contact.html';
    $target = $_POST['redirect'] ?? '';

    if ($target !== '' && preg_match('/^[a-zA-Z0-9_\.\-\/]+\.html$/', $target)) {
        return $target;
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $refHost = parse_url($referer, PHP_URL_HOST);
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $path = parse_url($referer, PHP_URL_PATH);
        if ($refHost === $host && is_string($path) && preg_match('/\.html$/', $path)) {
            return $path;
        }
    }

    return $fallback;
}

function respond($ok, $message, $redirect = null) {
    if (wants_json()) {
        http_response_code($ok ? 200 : 400);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'message' => $ok ? $message : null, 'error' => $ok ? null : $message]);
        exit;
    }

    $target = $redirect ?: safe_redirect_target();
    $flag = $ok ? 'sent=1' : 'error=1';
    $separator = strpos($target, '?') === false ? '?' : '&';
    header('Location: ' . $target . $separator . $flag);
    exit;
}

function limit_text($value, $max) {
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function clean($value, $max = 500) {
    $value = trim((string) $value);
    $value = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return limit_text($value, $max);
}

function clean_message($value, $max = 3000) {
    $value = trim((string) $value);
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    return limit_text($value, $max);
}

function ensure_private_dir($dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

function append_csv($file, array $row) {
    $handle = @fopen($file, 'a');
    if (!$handle) return;
    @flock($handle, LOCK_EX);
    @fputcsv($handle, $row);
    @flock($handle, LOCK_UN);
    @fclose($handle);
}

function rate_limit_ok($file) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = hash('sha256', $ip);
    $now = time();
    $window = 3600;
    $max = 10;

    $data = [];
    if (is_file($file)) {
        $decoded = json_decode((string) @file_get_contents($file), true);
        if (is_array($decoded)) $data = $decoded;
    }

    foreach ($data as $k => $hits) {
        $data[$k] = array_values(array_filter((array) $hits, fn($ts) => $ts > $now - $window));
        if (!$data[$k]) unset($data[$k]);
    }

    $hits = $data[$key] ?? [];
    if (count($hits) >= $max) {
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return false;
    }

    $hits[] = $now;
    $data[$key] = $hits;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

function send_plain_mail($to, $subject, $body, $fromEmail, $siteName, $replyTo = null) {
    $headers  = "From: $siteName <$fromEmail>\r\n";
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers);
}

ensure_private_dir($PRIVATE_DIR);

// ---- Guard: POST only ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

// ---- Honeypot: real users never fill this --------------------
if (!empty($_POST['bot-field'])) {
    respond(true, 'Thank you.');
}

if (!rate_limit_ok($RATE_FILE)) {
    respond(false, 'Too many submissions. Please wait and try again.');
}

// ---- Which form? ---------------------------------------------
$formName = clean($_POST['form-name'] ?? 'contact', 50);
$allowedForms = ['contact', 'visa-enquiry', 'newsletter'];
if (!in_array($formName, $allowedForms, true)) {
    respond(false, 'Invalid form submission.');
}

$privacyConsent = ($_POST['privacy-consent'] ?? '') === 'yes';
if (!$privacyConsent) {
    respond(false, 'Please confirm that you agree to our Privacy Policy before submitting.');
}

if ($formName === 'newsletter') {
    $email = clean($_POST['email'] ?? '', 254);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, 'Please enter a valid email address.');
    }

    $subject = "[$SITE_NAME] New newsletter subscriber";
    $body = "New newsletter signup:\n\nEmail: $email\nTime: " . date('Y-m-d H:i:s') . "\n";

    $sent = send_plain_mail($TO_EMAIL, $subject, $body, $FROM_EMAIL, $SITE_NAME, $email);

    append_csv($LOG_FILE, [date('c'), 'newsletter', '', $email, '', '', 'privacy-consent:yes', $_SERVER['REMOTE_ADDR'] ?? '']);

    // Confirmation only. For marketing newsletters, use a provider with double opt-in.
    send_plain_mail(
        $email,
        "[$SITE_NAME] Subscription received",
        "Hello,\n\nThanks for subscribing to Sween Travels updates. If this was not you, please ignore this email.\n\nRegards,\n$SITE_NAME",
        $FROM_EMAIL,
        $SITE_NAME
    );

    respond($sent, $sent ? 'Subscribed.' : 'We saved your subscription but email delivery failed.');
}

// ---- Contact / visa enquiry forms ----------------------------
$name    = clean($_POST['name'] ?? '', 120);
$email   = clean($_POST['email'] ?? '', 254);
$phone   = clean($_POST['phone'] ?? '', 60);
$subject = clean($_POST['subject'] ?? '', 150);
$message = clean_message($_POST['message'] ?? '', 3000);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    respond(false, 'Please fill in your name, a valid email, and a message.');
}

$mailSubject = "[$SITE_NAME] New " . ($formName === 'visa-enquiry' ? 'visa enquiry' : 'enquiry')
    . ($subject !== '' ? ": $subject" : '');

$mailBody  = "New website lead:\n\n";
$mailBody .= "Form:    $formName\n";
$mailBody .= "Name:    $name\n";
$mailBody .= "Email:   $email\n";
$mailBody .= "Phone:   " . ($phone !== '' ? $phone : '-') . "\n";
$mailBody .= "Subject: " . ($subject !== '' ? $subject : '-') . "\n";
$mailBody .= "Time:    " . date('Y-m-d H:i:s') . "\n";
$mailBody .= "IP:      " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n\n";
$mailBody .= "Message:\n$message\n";

$sent = send_plain_mail($TO_EMAIL, $mailSubject, $mailBody, $FROM_EMAIL, $SITE_NAME, "$name <$email>");

append_csv($LOG_FILE, [date('c'), $formName, $name, $email, $phone, $subject, $message, 'privacy-consent:yes', $_SERVER['REMOTE_ADDR'] ?? '']);

send_plain_mail(
    $email,
    "[$SITE_NAME] We received your enquiry",
    "Hello $name,\n\nThank you for contacting Sween Travels. We have received your enquiry and our team will get back to you shortly.\n\nRegards,\n$SITE_NAME\nPhone: (+254) 759 187 912",
    $FROM_EMAIL,
    $SITE_NAME
);

if ($sent) {
    respond(true, 'Message sent.');
}

respond(false, 'We saved your message but email delivery failed. We will still see it.');
