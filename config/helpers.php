<?php
/**
 * config/helpers.php — fungsi bantu: escape output, flash message, CSRF, format Rupiah.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape output HTML (pencegahan XSS) — wajib untuk semua data yang ditampilkan
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Flash message (Post/Redirect/Get)
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// CSRF token untuk form POST
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        die('Token tidak valid. Silakan kembali dan coba lagi.');
    }
}

function rupiah($number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}
