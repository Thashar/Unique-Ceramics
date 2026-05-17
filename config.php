<?php
// ================================================
// UNIQUE CERAMICS — KONFIGURACJA / CONFIGURATION
// ================================================

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// ---- Ścieżka bazowa / Base path ----
// Jeśli strona jest w podkatalogu np. /ceramics, wpisz '/ceramics'
// If the site is in a subdirectory e.g. /ceramics, enter '/ceramics'
define('BASE_PATH', '');

// ---- URL strony (auto-detect) ----
$_prot = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('SITE_URL', $_prot . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_PATH);
unset($_prot);

// ---- Ścieżki plików / File paths ----
define('ROOT_DIR',   __DIR__);
define('DB_PATH',    ROOT_DIR . '/data/shop.db');
define('UPLOAD_DIR', ROOT_DIR . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ---- Dane firmy / Company info ----
define('SITE_NAME',      'Unique Ceramics');
define('SITE_PHONE',     '+48 668 443 706');
define('SITE_EMAIL',     'kontakt@uniqueceramics.pl');
define('SITE_INSTAGRAM', 'https://www.instagram.com/unique.ceramics/');
define('SITE_FACEBOOK',  '');

// ---- Waluta / Currency ----
define('CURRENCY',        'PLN');
define('CURRENCY_SYMBOL', 'zł');

// ---- Wysyłka / Shipping ----
define('SHIPPING_COST',          18.00);
define('SHIPPING_FREE_FROM',     300.00);
define('SHIPPING_FREE_ENABLED',  true);

// ---- Sesja / Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_name('UC_SHOP');
    ini_set('session.cookie_httponly', '1');
    session_start();
}

// ---- Domyślny język / Default language ----
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'pl';
}

// ---- Helpers ----
function url(string $path = ''): string {
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset(string $path = ''): string {
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}

function upload_url(string $filename): string {
    return UPLOAD_URL . ltrim($filename, '/');
}

function h(mixed $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function format_price(float $amount): string {
    return number_format($amount, 2, ',', ' ') . ' ' . CURRENCY_SYMBOL;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && hash_equals(csrf_token(), $token);
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function is_admin_logged(): bool {
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void {
    if (!is_admin_logged()) {
        redirect(url('admin/login.php'));
    }
}

function generate_order_number(): string {
    return 'UC-' . strtoupper(date('Ymd')) . '-' . strtoupper(substr(uniqid(), -5));
}

function t(string $key): string {
    global $lang;
    $keys = explode('.', $key);
    $val = $lang ?? [];
    foreach ($keys as $k) {
        if (!is_array($val) || !array_key_exists($k, $val)) return $key;
        $val = $val[$k];
    }
    return is_string($val) ? $val : $key;
}

function current_lang(): string {
    return $_SESSION['lang'] ?? 'pl';
}

function other_lang(): string {
    return current_lang() === 'pl' ? 'en' : 'pl';
}
