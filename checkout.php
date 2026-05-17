<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart-functions.php';

if (cart_is_empty()) redirect(url('cart.php'));

$pageTitle = current_lang() === 'pl' ? 'Kasa' : 'Checkout';
$errors    = [];
$success   = false;

// Available payment methods
$paymentMethods = [
    'transfer' => [
        'label' => t('checkout.pay_transfer'),
        'desc'  => current_lang() === 'pl' ? 'Dane do przelewu otrzymasz po złożeniu zamówienia' : 'You will receive bank details after placing your order',
        'icon'  => '🏦',
    ],
    'payu' => [
        'label' => t('checkout.pay_payu'),
        'desc'  => current_lang() === 'pl' ? 'Karta, BLIK, przelew online — bramka PayU' : 'Card, BLIK, online transfer — PayU gateway',
        'icon'  => '💳',
    ],
    'przelewy24' => [
        'label' => t('checkout.pay_p24'),
        'desc'  => current_lang() === 'pl' ? 'Wszystkie metody płatności Przelewy24' : 'All Przelewy24 payment methods',
        'icon'  => '💳',
    ],
    'stripe' => [
        'label' => t('checkout.pay_stripe'),
        'desc'  => current_lang() === 'pl' ? 'Bezpieczna płatność kartą przez Stripe' : 'Secure card payment via Stripe',
        'icon'  => '💳',
    ],
];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = current_lang() === 'pl' ? 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.' : 'Security error. Refresh and try again.';
    } else {
        // Collect & validate fields
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $phone     = trim($_POST['phone']      ?? '');
        $street    = trim($_POST['street']     ?? '');
        $city      = trim($_POST['city']       ?? '');
        $postcode  = trim($_POST['postcode']   ?? '');
        $country   = trim($_POST['country']    ?? 'Polska');
        $note      = trim($_POST['note']       ?? '');
        $payment   = $_POST['payment']         ?? '';
        $terms     = !empty($_POST['terms']);

        if (!$firstName)                    $errors[] = t('checkout.first_name') . ' — ' . t('checkout.required');
        if (!$lastName)                     $errors[] = t('checkout.last_name')  . ' — ' . t('checkout.required');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('checkout.email') . ' — ' . t('checkout.required');
        if (!$street)                       $errors[] = t('checkout.street')     . ' — ' . t('checkout.required');
        if (!$city)                         $errors[] = t('checkout.city')       . ' — ' . t('checkout.required');
        if (!$postcode)                     $errors[] = t('checkout.postcode')   . ' — ' . t('checkout.required');
        if (!array_key_exists($payment, $paymentMethods)) $errors[] = t('checkout.payment') . ' — ' . t('checkout.required');
        if (!$terms)                        $errors[] = current_lang() === 'pl' ? 'Musisz zaakceptować regulamin' : 'You must accept the terms';

        if (empty($errors)) {
            $orderNumber = generate_order_number();
            $cart        = cart_get();
            $subtotal    = cart_subtotal();
            $shipping    = cart_shipping();
            $total       = cart_total();

            $shippingAddr = json_encode([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'street'     => $street,
                'city'       => $city,
                'postcode'   => $postcode,
                'country'    => $country,
            ]);

            $items = [];
            foreach ($cart as $pid => $item) {
                $items[] = [
                    'product_id' => $pid,
                    'name'       => $item['name_pl'],
                    'name_en'    => $item['name_en'],
                    'price'      => $item['price'],
                    'qty'        => $item['qty'],
                    'image'      => $item['image'],
                    'slug'       => $item['slug'],
                ];
            }

            $orderId = db_insert('orders', [
                'order_number'     => $orderNumber,
                'customer_name'    => $firstName . ' ' . $lastName,
                'customer_email'   => $email,
                'customer_phone'   => $phone,
                'shipping_address' => $shippingAddr,
                'items'            => json_encode($items),
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shipping,
                'total'            => $total,
                'payment_method'   => $payment,
                'payment_status'   => 'pending',
                'order_status'     => 'new',
                'notes'            => $note,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            // Reduce stock
            foreach ($cart as $pid => $item) {
                db_query('UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?', [$item['qty'], $pid]);
            }

            cart_clear();
            $_SESSION['last_order_id'] = $orderId;
            redirect(url('order-success.php?order=' . $orderNumber));
        }
    }
}

$cart     = cart_get();
$subtotal = cart_subtotal();
$shipping = cart_shipping();
$total    = cart_total();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-sm">
  <h1 class="mb-3"><?= t('checkout.title') ?></h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul style="margin:0;padding-left:1.2rem">
        <?php foreach ($errors as $e): ?>
          <li><?= h($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" class="checkout-layout" novalidate>
    <?= csrf_field() ?>

    <!-- Left: forms -->
    <div>
      <!-- Customer details -->
      <div class="checkout-form-card">
        <h3><i class="fas fa-user"></i> <?= t('checkout.customer') ?></h3>
        <div class="form-grid">
          <div class="form-group">
            <label><?= t('checkout.first_name') ?> *</label>
            <input type="text" name="first_name" value="<?= h($_POST['first_name'] ?? '') ?>" required autocomplete="given-name">
          </div>
          <div class="form-group">
            <label><?= t('checkout.last_name') ?> *</label>
            <input type="text" name="last_name"  value="<?= h($_POST['last_name']  ?? '') ?>" required autocomplete="family-name">
          </div>
          <div class="form-group">
            <label><?= t('checkout.email') ?> *</label>
            <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autocomplete="email">
          </div>
          <div class="form-group">
            <label><?= t('checkout.phone') ?></label>
            <input type="tel" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" autocomplete="tel">
          </div>
        </div>
      </div>

      <!-- Shipping address -->
      <div class="checkout-form-card">
        <h3><i class="fas fa-map-marker-alt"></i> <?= t('checkout.shipping_addr') ?></h3>
        <div class="form-grid">
          <div class="form-group full">
            <label><?= t('checkout.street') ?> *</label>
            <input type="text" name="street" value="<?= h($_POST['street'] ?? '') ?>" required autocomplete="street-address">
          </div>
          <div class="form-group">
            <label><?= t('checkout.city') ?> *</label>
            <input type="text" name="city" value="<?= h($_POST['city'] ?? '') ?>" required autocomplete="address-level2">
          </div>
          <div class="form-group">
            <label><?= t('checkout.postcode') ?> *</label>
            <input type="text" name="postcode" value="<?= h($_POST['postcode'] ?? '') ?>" required autocomplete="postal-code" placeholder="00-000">
          </div>
          <div class="form-group full">
            <label><?= t('checkout.country') ?></label>
            <select name="country" autocomplete="country-name">
              <option value="Polska"      <?= ($_POST['country'] ?? 'Polska') === 'Polska'      ? 'selected' : '' ?>>🇵🇱 Polska / Poland</option>
              <option value="Niemcy"      <?= ($_POST['country'] ?? '') === 'Niemcy'      ? 'selected' : '' ?>>🇩🇪 Niemcy / Germany</option>
              <option value="Francja"     <?= ($_POST['country'] ?? '') === 'Francja'     ? 'selected' : '' ?>>🇫🇷 Francja / France</option>
              <option value="Wielka Brytania" <?= ($_POST['country'] ?? '') === 'Wielka Brytania' ? 'selected' : '' ?>>🇬🇧 Wielka Brytania / UK</option>
              <option value="Czechy"      <?= ($_POST['country'] ?? '') === 'Czechy'      ? 'selected' : '' ?>>🇨🇿 Czechy / Czech Republic</option>
              <option value="Inne"        <?= ($_POST['country'] ?? '') === 'Inne'        ? 'selected' : '' ?>><?= current_lang() === 'pl' ? 'Inne' : 'Other' ?></option>
            </select>
          </div>
          <div class="form-group full">
            <label><?= t('checkout.note') ?></label>
            <textarea name="note" rows="3" placeholder="<?= h(t('checkout.note')) ?>"><?= h($_POST['note'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div class="checkout-form-card">
        <h3><i class="fas fa-credit-card"></i> <?= t('checkout.payment') ?></h3>
        <div class="payment-options">
          <?php foreach ($paymentMethods as $key => $method): ?>
            <label class="payment-option <?= ($_POST['payment'] ?? 'transfer') === $key ? 'selected' : '' ?>">
              <input type="radio" name="payment" value="<?= $key ?>" <?= ($_POST['payment'] ?? 'transfer') === $key ? 'checked' : '' ?>>
              <span style="font-size:1.4rem"><?= $method['icon'] ?></span>
              <div style="flex:1">
                <div class="payment-option-label"><?= h($method['label']) ?></div>
                <div class="payment-option-desc"><?= h($method['desc']) ?></div>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Terms + Submit -->
      <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:1rem">
        <input type="checkbox" name="terms" id="terms" value="1" style="width:auto" <?= !empty($_POST['terms']) ? 'checked' : '' ?> required>
        <label for="terms" style="font-size:.88rem">
          <?= t('checkout.terms_accept') ?>
          <a href="<?= url('contact.php') ?>" target="_blank" style="color:var(--terracotta)"><?= t('checkout.terms_link') ?></a> *
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block">
        <i class="fas fa-lock"></i> <?= t('checkout.place_order') ?> — <?= format_price($total) ?>
      </button>
    </div>

    <!-- Right: Order summary -->
    <div class="checkout-summary">
      <h3><?= t('checkout.order_summary') ?></h3>
      <?php foreach ($cart as $pid => $item): ?>
        <div class="checkout-item">
          <div class="checkout-item-img">
            <?php if ($item['image'] !== 'placeholder.jpg'): ?>
              <img src="<?= upload_url($item['image']) ?>" alt="<?= cart_item_name($item) ?>">
            <?php else: ?>
              <div class="placeholder-img" style="font-size:1.2rem">🏺</div>
            <?php endif; ?>
          </div>
          <div style="flex:1">
            <div class="checkout-item-name"><?= cart_item_name($item) ?></div>
            <div class="checkout-item-qty">× <?= $item['qty'] ?></div>
          </div>
          <div class="checkout-item-price"><?= format_price($item['price'] * $item['qty']) ?></div>
        </div>
      <?php endforeach; ?>

      <div class="summary-row" style="margin-top:1rem">
        <span><?= t('cart.subtotal') ?></span>
        <span><?= format_price($subtotal) ?></span>
      </div>
      <div class="summary-row">
        <span><?= t('cart.shipping') ?></span>
        <span><?= $shipping > 0 ? format_price($shipping) : '<span style="color:var(--success)">' . t('cart.free') . '</span>' ?></span>
      </div>
      <div class="summary-row total">
        <span><?= t('cart.order_total') ?></span>
        <span><?= format_price($total) ?></span>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
