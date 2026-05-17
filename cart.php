<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart-functions.php';

$pageTitle = current_lang() === 'pl' ? 'Koszyk' : 'Cart';
$cart = cart_get();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-sm">
  <h1 class="mb-3"><?= t('cart.title') ?> <?php if (!empty($cart)): ?>(<?= cart_count() ?>)<?php endif; ?></h1>

  <?php if (empty($cart)): ?>
    <div style="text-align:center;padding:5rem 0">
      <div style="font-size:4rem;margin-bottom:1rem">🛍️</div>
      <h2 style="margin-bottom:.8rem"><?= t('cart.empty') ?></h2>
      <a href="<?= url('shop.php') ?>" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag"></i> <?= t('cart.empty_cta') ?>
      </a>
    </div>
  <?php else: ?>
    <div class="cart-layout">
      <!-- Cart items -->
      <div>
        <?php
        $subtotal = cart_subtotal();
        $shipping = cart_shipping();
        $total    = cart_total();
        $freeFrom = SHIPPING_FREE_FROM;
        $freeLeft = max(0, $freeFrom - $subtotal);
        $freePct  = SHIPPING_FREE_ENABLED && $freeFrom > 0 ? min(100, ($subtotal / $freeFrom) * 100) : 100;
        ?>

        <!-- Free shipping progress -->
        <?php if (SHIPPING_FREE_ENABLED): ?>
          <div class="cart-free-ship">
            <?php if ($freeLeft > 0): ?>
              <span><?= t('cart.free_ship_info') ?> <strong><?= format_price($freeLeft) ?></strong> <?= current_lang() === 'pl' ? 'do darmowej wysyłki' : 'more for free shipping' ?></span>
            <?php else: ?>
              <span>🎉 <?= t('cart.free_ship_done') ?></span>
            <?php endif; ?>
            <div class="free-ship-bar"><div class="free-ship-fill" style="width:<?= $freePct ?>%"></div></div>
          </div>
        <?php endif; ?>

        <table class="cart-table">
          <thead>
            <tr>
              <th><?= t('cart.product') ?></th>
              <th><?= t('cart.price') ?></th>
              <th><?= t('cart.qty') ?></th>
              <th><?= t('cart.total') ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $productId => $item): ?>
              <tr>
                <!-- Product -->
                <td>
                  <div class="cart-item">
                    <div class="cart-item-img">
                      <?php if ($item['image'] !== 'placeholder.jpg'): ?>
                        <img src="<?= upload_url($item['image']) ?>" alt="<?= cart_item_name($item) ?>">
                      <?php else: ?>
                        <div class="placeholder-img">🏺</div>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="cart-item-name">
                        <a href="<?= url('product.php?slug=' . $item['slug']) ?>"><?= cart_item_name($item) ?></a>
                      </div>
                      <div style="font-size:.8rem;color:var(--stone)"><?= format_price($item['price']) ?> / <?= t('common.pieces') ?></div>
                    </div>
                  </div>
                </td>
                <!-- Price -->
                <td><?= format_price($item['price']) ?></td>
                <!-- Qty -->
                <td>
                  <input type="number" class="qty-cart-input qty-input-small"
                         data-id="<?= $productId ?>"
                         value="<?= $item['qty'] ?>"
                         min="1" max="<?= $item['stock'] ?>">
                </td>
                <!-- Total -->
                <td><strong><?= format_price($item['price'] * $item['qty']) ?></strong></td>
                <!-- Remove -->
                <td>
                  <button class="remove-item" data-id="<?= $productId ?>" title="<?= h(t('cart.remove')) ?>">
                    <i class="fas fa-times"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="margin-top:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.6rem">
          <a href="<?= url('shop.php') ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> <?= t('cart.continue') ?>
          </a>
        </div>
      </div>

      <!-- Order summary -->
      <div class="cart-summary">
        <h3><?= current_lang() === 'pl' ? 'Podsumowanie' : 'Order Summary' ?></h3>

        <div class="summary-row">
          <span><?= t('cart.subtotal') ?></span>
          <span class="cart-subtotal-val"><?= format_price($subtotal) ?></span>
        </div>
        <div class="summary-row">
          <span><?= t('cart.shipping') ?></span>
          <span class="cart-shipping-val">
            <?= $shipping > 0 ? format_price($shipping) : '<span style="color:var(--success);font-weight:700">' . t('cart.free') . '</span>' ?>
          </span>
        </div>
        <div class="summary-row total">
          <span><?= t('cart.order_total') ?></span>
          <span class="cart-total-val"><?= format_price($total) ?></span>
        </div>

        <?php if (SHIPPING_FREE_ENABLED && $freeLeft > 0): ?>
          <div class="summary-shipping-note">
            <i class="fas fa-truck"></i>
            <?= current_lang() === 'pl'
              ? 'Dodaj produkty za ' . format_price($freeLeft) . ' — dostaniesz darmową wysyłkę!'
              : 'Add ' . format_price($freeLeft) . ' more for free shipping!' ?>
          </div>
        <?php endif; ?>

        <a href="<?= url('checkout.php') ?>" class="btn btn-primary btn-lg btn-block" style="margin-top:1.2rem">
          <i class="fas fa-lock"></i> <?= t('cart.checkout') ?>
        </a>

        <div style="text-align:center;margin-top:.8rem;font-size:.78rem;color:var(--stone)">
          <i class="fas fa-shield-alt"></i>
          <?= current_lang() === 'pl' ? 'Bezpieczna płatność' : 'Secure payment' ?>
        </div>

        <!-- Payment icons -->
        <div style="display:flex;justify-content:center;gap:.4rem;margin-top:.7rem;flex-wrap:wrap;font-size:.7rem;color:var(--stone)">
          <span style="background:var(--sand);padding:.2rem .5rem;border-radius:4px">Przelew</span>
          <span style="background:var(--sand);padding:.2rem .5rem;border-radius:4px">PayU</span>
          <span style="background:var(--sand);padding:.2rem .5rem;border-radius:4px">P24</span>
          <span style="background:var(--sand);padding:.2rem .5rem;border-radius:4px">Stripe</span>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
