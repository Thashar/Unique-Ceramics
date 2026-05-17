<?php
// Cart stored in $_SESSION['cart'] = [ product_id => ['qty' => n, 'price' => p, ...] ]

function cart_get(): array {
    return $_SESSION['cart'] ?? [];
}

function cart_add(int $productId, int $qty = 1): bool {
    $product = get_product_by_id($productId);
    if (!$product) return false;
    if ($product['stock'] < 1) return false;

    $cart = cart_get();
    if (isset($cart[$productId])) {
        $newQty = $cart[$productId]['qty'] + $qty;
        $cart[$productId]['qty'] = min($newQty, (int)$product['stock']);
    } else {
        $cart[$productId] = [
            'qty'         => min($qty, (int)$product['stock']),
            'price'       => (float)$product['price'],
            'name_pl'     => $product['name_pl'],
            'name_en'     => $product['name_en'],
            'image'       => get_product_main_image($product),
            'slug'        => $product['slug'],
            'stock'       => (int)$product['stock'],
        ];
    }
    $_SESSION['cart'] = $cart;
    return true;
}

function cart_update(int $productId, int $qty): void {
    $cart = cart_get();
    if (!isset($cart[$productId])) return;
    if ($qty <= 0) {
        cart_remove($productId);
        return;
    }
    $cart[$productId]['qty'] = min($qty, $cart[$productId]['stock']);
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $productId): void {
    $cart = cart_get();
    unset($cart[$productId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void {
    $_SESSION['cart'] = [];
}

function cart_count(): int {
    return array_sum(array_column(cart_get(), 'qty'));
}

function cart_subtotal(): float {
    $total = 0.0;
    foreach (cart_get() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

function cart_shipping(): float {
    if (SHIPPING_FREE_ENABLED && cart_subtotal() >= SHIPPING_FREE_FROM) return 0.0;
    return SHIPPING_COST;
}

function cart_total(): float {
    return cart_subtotal() + cart_shipping();
}

function cart_is_empty(): bool {
    return empty(cart_get());
}

function cart_item_name(array $item): string {
    $lang = current_lang();
    return h($item["name_{$lang}"] ?: $item['name_pl']);
}
