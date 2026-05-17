<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart-functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
          (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') ||
          str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

$action   = $_POST['action'] ?? '';
$id       = (int)($_POST['id'] ?? 0);
$qty      = max(1, (int)($_POST['qty'] ?? 1));
$redirect = $_POST['redirect'] ?? url('cart.php');

function json_response(bool $ok, string $msg = '', array $extra = []): never {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function cart_extras(): array {
    $subtotal = cart_subtotal();
    $shipping = cart_shipping();
    $total    = cart_total();
    $free     = SHIPPING_FREE_ENABLED && SHIPPING_FREE_FROM > 0
        ? min(100, ($subtotal / SHIPPING_FREE_FROM) * 100)
        : 100;
    return [
        'count'              => cart_count(),
        'subtotal_formatted' => format_price($subtotal),
        'shipping_formatted' => $shipping > 0 ? format_price($shipping) : (current_lang() === 'pl' ? 'GRATIS' : 'FREE'),
        'total_formatted'    => format_price($total),
        'free_percent'       => round($free),
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirect);
}

if (!verify_csrf()) {
    if ($isAjax) json_response(false, 'Invalid CSRF token');
    redirect($redirect);
}

switch ($action) {
    case 'add':
        $ok = cart_add($id, $qty);
        $msg = $ok
            ? (current_lang() === 'pl' ? 'Produkt dodany do koszyka' : 'Product added to cart')
            : (current_lang() === 'pl' ? 'Nie można dodać produktu' : 'Cannot add product');
        if ($isAjax) json_response($ok, $msg, cart_extras());
        redirect($redirect);

    case 'update':
        cart_update($id, $qty);
        if ($isAjax) json_response(true, '', cart_extras());
        redirect(url('cart.php'));

    case 'remove':
        cart_remove($id);
        if ($isAjax) json_response(true, '', cart_extras());
        redirect(url('cart.php'));

    default:
        if ($isAjax) json_response(false, 'Unknown action');
        redirect($redirect);
}
