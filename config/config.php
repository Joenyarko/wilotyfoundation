<?php
// config/config.php
// Global Application Settings for Wiloty Foundation Website

// Error reporting - disable display in production, log errors internally
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Secure session initialization helper
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        session_start();
    }
}

init_secure_session();

// Send baseline security headers for web responses
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// Load secrets if exists (loads API keys, Paystack, and can override DB/URL configs)
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// Database Credentials (uses values from secrets.php / env if defined, otherwise defaults to local XAMPP)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'wiloty_db');

if (!defined('BREVO_SMTP_USER')) define('BREVO_SMTP_USER', getenv('BREVO_SMTP_USER') ?: 'a4c4b2001@smtp-brevo.com');
if (!defined('BREVO_API_KEY')) define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
if (!defined('BREVO_SMTP_PASS')) define('BREVO_SMTP_PASS', getenv('BREVO_SMTP_PASS') ?: '');
if (!defined('PAYSTACK_PUBLIC_KEY')) define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: '');
if (!defined('PAYSTACK_SECRET_KEY')) define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');

// Brevo API Settings (Primary)
define('BREVO_SENDER_EMAIL', 'info@wilotyfoundation.org');
define('BREVO_SENDER_NAME', 'Wiloty Foundation');

// SMTP Settings (Fallback PHPMailer config via environment variables)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'no-reply@wilotyfoundation.org');
define('SMTP_FROM_NAME', 'Wiloty Foundation Notifications');

// Global Configurations
if (!defined('SITE_URL')) define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/jow');
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Create uploads directory if it does not exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Security Configuration
if (!defined('RECAPTCHA_SITE_KEY')) define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '');
if (!defined('RECAPTCHA_SECRET_KEY')) define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');
define('CSRF_SESSION_KEY', 'csrf_token');

// Helper to set restricted CORS headers for API endpoints
function send_api_cors_headers($allowed_methods = 'POST') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $siteOrigin = rtrim(SITE_URL, '/');
    $parsedSite = parse_url($siteOrigin);
    $expectedOrigin = isset($parsedSite['scheme'], $parsedSite['host'])
        ? $parsedSite['scheme'] . '://' . $parsedSite['host'] . (isset($parsedSite['port']) ? ':' . $parsedSite['port'] : '')
        : $siteOrigin;

    if ($origin !== '' && hash_equals($expectedOrigin, $origin)) {
        header("Access-Control-Allow-Origin: " . $expectedOrigin);
        header("Vary: Origin");
    } else {
        header("Access-Control-Allow-Origin: " . $expectedOrigin);
    }
    header("Access-Control-Allow-Methods: " . $allowed_methods);
    header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
}

// Helper to generate CSRF token
function generate_csrf_token() {
    init_secure_session();
    if (empty($_SESSION[CSRF_SESSION_KEY]) || !is_string($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

// Helper to verify CSRF token
function verify_csrf_token($token) {
    init_secure_session();
    if (!is_string($token) || $token === '') {
        return false;
    }
    if (isset($_SESSION[CSRF_SESSION_KEY]) && is_string($_SESSION[CSRF_SESSION_KEY]) && hash_equals($_SESSION[CSRF_SESSION_KEY], $token)) {
        return true;
    }
    return false;
}

// Helper to enforce CSRF token on POST requests
function require_csrf_token() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die("CSRF security check failed. Please refresh the page and try again.");
    }
}

// File-backed IP rate limiting helper
function check_rate_limit($action, $max_attempts = 10, $window_seconds = 60) {
    $rate_dir = BASE_PATH . '/cache/ratelimit/';
    if (!is_dir($rate_dir)) {
        @mkdir($rate_dir, 0755, true);
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = hash('sha256', $action . '|' . $ip);
    $file = $rate_dir . $key . '.json';
    $now = time();

    $fp = @fopen($file, 'c+');
    if (!$fp) {
        return true;
    }

    $allowed = true;
    if (flock($fp, LOCK_EX)) {
        $raw = stream_get_contents($fp);
        $timestamps = $raw ? json_decode($raw, true) : [];
        if (!is_array($timestamps)) {
            $timestamps = [];
        }
        // Keep only timestamps within the active window
        $timestamps = array_values(array_filter($timestamps, function ($ts) use ($now, $window_seconds) {
            return is_int($ts) && ($now - $ts) < $window_seconds;
        }));

        if (count($timestamps) >= $max_attempts) {
            $allowed = false;
        } else {
            $timestamps[] = $now;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($timestamps));
        }
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $allowed;
}

// Security input sanitization function
function sanitize_input($data) {
    if (!is_string($data)) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Allowlist-based HTML sanitizer for rich text content (prevents Stored XSS)
function sanitize_rich_html($html) {
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    $allowedTags = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote',
        'a', 'span', 'img', 'div', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td'
    ];
    $allowedAttrs = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        '*'   => ['class']
    ];

    if (!class_exists('DOMDocument')) {
        return strip_tags($html, '<p><br><b><strong><i><em><u><s><h1><h2><h3><h4><ul><ol><li><blockquote>');
    }

    $dom = new DOMDocument();
    $prevUseErrors = libxml_use_internal_errors(true);
    $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prevUseErrors);

    $body = $dom->getElementsByTagName('body')->item(0);
    if (!$body) {
        return '';
    }

    $sanitizeNode = function (DOMNode $node) use (&$sanitizeNode, $allowedTags, $allowedAttrs) {
        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                $sanitizeNode($child);
            }
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);
            if (!in_array($tag, $allowedTags, true)) {
                $node->parentNode->removeChild($node);
                return;
            }

            if ($node->hasAttributes()) {
                $tagAllowed = array_merge($allowedAttrs['*'], $allowedAttrs[$tag] ?? []);
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    $attrName = strtolower($attr->nodeName);
                    $attrVal = trim($attr->nodeValue);
                    if (!in_array($attrName, $tagAllowed, true)) {
                        $attrsToRemove[] = $attr->nodeName;
                        continue;
                    }
                    if (in_array($attrName, ['href', 'src'], true)) {
                        $normalized = preg_replace('/[\x00-\x20]+/', '', strtolower($attrVal));
                        if (strpos($normalized, 'javascript:') === 0 || strpos($normalized, 'data:') === 0 || strpos($normalized, 'vbscript:') === 0) {
                            $attrsToRemove[] = $attr->nodeName;
                        }
                    }
                }
                foreach ($attrsToRemove as $rem) {
                    $node->removeAttribute($rem);
                }
                if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        } elseif ($node->nodeType === XML_COMMENT_NODE) {
            $node->parentNode->removeChild($node);
        }
    };

    $sanitizeNode($body);

    $cleanHtml = '';
    foreach ($body->childNodes as $child) {
        $cleanHtml .= $dom->saveHTML($child);
    }
    return $cleanHtml;
}

// Centralized secure image upload handler (verifies size, extension, real MIME type, and image headers)
function handle_secure_image_upload($file, &$error, $max_size = 5242880) {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Image upload failed with error code: " . (int)$file['error'];
        return null;
    }

    $fileTmpPath = $file['tmp_name'] ?? '';
    $fileName = $file['name'] ?? '';
    $fileSize = (int)($file['size'] ?? 0);

    if ($fileSize <= 0 || $fileSize > $max_size) {
        $error = "Upload failed. Image size must not exceed " . round($max_size / 1048576, 1) . " MB.";
        return null;
    }

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedMimeMap = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];

    if (!array_key_exists($fileExtension, $allowedMimeMap)) {
        $error = "Upload failed. Allowed image extensions: JPG, JPEG, PNG, WEBP, GIF.";
        return null;
    }

    // Verify actual MIME type from file contents
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $fileTmpPath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($detectedMime !== $allowedMimeMap[$fileExtension]) {
            $error = "Upload failed. File content does not match allowed image format.";
            return null;
        }
    }

    // Verify valid image dimensions / structure
    if (@getimagesize($fileTmpPath) === false) {
        $error = "Upload failed. The uploaded file is not a valid image.";
        return null;
    }

    $newFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $destPath = UPLOAD_DIR . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        $error = "There was an error saving the uploaded image.";
        return null;
    }

    return 'uploads/' . $newFileName;
}

require_once __DIR__ . '/Mailer.php';

// Helper to send emails immediately (synchronously)
function send_email($to, $subject, $body, $from_type = 'admin') {
    try {
        require_once __DIR__ . '/Mailer.php';
        $mailer = new Mailer();
        
        $from_email = ($from_type === 'info') ? 'info@wilotyfoundation.org' : 'admin@wilotyfoundation.org';

        // Apply global beautiful Wiloty template if the email doesn't already have one
        if (strpos($body, 'max-width: 600px;') === false) {
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; border: 1px solid #eaeaea; border-radius: 8px; padding: 20px;'>
                <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px dashed #eaeaea; margin-bottom: 20px;'>
                    <h2 style='margin: 0; color: #ff6b00;'>Wiloty Foundation</h2>
                    <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>Building Impact Through Community</p>
                </div>
                <div style='font-size: 15px; line-height: 1.6;'>
                    $body
                </div>
                <div style='border-top: 2px dashed #eaeaea; padding-top: 20px; margin-top: 20px; text-align: center; font-size: 12px; color: #999;'>
                    <p style='font-weight: bold; color: #666; margin-bottom: 5px;'>Wiloty Foundation</p>
                    <p><a href='mailto:info@wilotyfoundation.org' style='color: #999;'>info@wilotyfoundation.org</a></p>
                </div>
            </div>";
        }

        return $mailer->sendEmail($to, $subject, $body, $from_email);
    } catch (Exception $e) {
        error_log("Failed to send email to $to: " . $e->getMessage());
        return false;
    }
}
