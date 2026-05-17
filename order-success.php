<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$orderNum = $_GET['order'] ?? '';
$order    = $orderNum ? get_order_by_number($orderNum) : null;

if (!$order) redirect(url('index.php'));

$pageTitle = current_lang() === 'pl' ? 'Zamówienie przyjęte' : 'Order Confirmed';

require_once __DIR__ . '/includes/header.php';

$items   = json_decode($order['items'] ?? '[]', true);
$addr    = json_decode($order['shipping_address'] ?? '{}', true);
$bankAcc = get_setting('bank_account', '');
$bankName= get_setting('bank_name', '');
?>

<div class="container section-sm">
  <div class="success-box">
    <div class="success-icon">🎉</div>
    <h1><?= t('success.title') ?></h1>
    <p style="color:var(--stone);margin-bottom:1.2rem"><?= t('success.subtitle') ?></p>

    <div style="background:var(--sand);border-radius:8px;padding:1rem;margin-bottom:1.2rem">
      <div style="font-size:.8rem;color:var(--stone);text-transform:uppercase;letter-spacing:.06em"><?= t('success.order_num') ?></div>
      <div style="font-size:1.4rem;font-weight:700;font-family:var(--font-head);color:var(--clay)"><?= h($order['order_number']) ?></div>
    </div>

    <p style="color:var(--stone);font-size:.9rem"><?= t('success.email_info') ?>: <strong><?= h($order['customer_email']) ?></strong></p>

    <!-- Bank transfer details -->
    <?php if ($order['payment_method'] === 'transfer' && $bankAcc): ?>
      <div class="transfer-box">
        <h3 style="margin-bottom:.8rem"><?= t('success.transfer_title') ?></h3>
        <?php if ($bankName): ?>
          <div class="transfer-row">
            <span class="transfer-label"><?= t('success.transfer_bank') ?>:</span>
            <span><?= h($bankName) ?></span>
          </div>
        <?php endif; ?>
        <div class="transfer-row">
          <span class="transfer-label"><?= t('success.transfer_acc') ?>:</span>
          <strong><?= h($bankAcc) ?></strong>
        </div>
        <div class="transfer-row">
          <span class="transfer-label"><?= t('success.transfer_title2') ?>:</span>
          <strong><?= h($order['order_number']) ?></strong>
        </div>
        <div class="transfer-row">
          <span class="transfer-label"><?= t('success.transfer_amount') ?>:</span>
          <strong style="color:var(--terracotta)"><?= format_price($order['total']) ?></strong>
        </div>
      </div>
    <?php elseif ($order['payment_method'] === 'transfer'): ?>
      <div class="alert alert-info">
        <?= current_lang() === 'pl'
          ? 'Dane do przelewu zostaną przesłane e-mailem po potwierdzeniu zamówienia.'
          : 'Bank transfer details will be sent by e-mail after order confirmation.' ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <?= current_lang() === 'pl'
          ? 'Twoje zamówienie zostało przekazane do realizacji. Bramka płatności zostanie aktywowana przez administratora.'
          : 'Your order has been forwarded for processing. The payment gateway will be activated by the administrator.' ?>
      </div>
    <?php endif; ?>

    <!-- Order items summary -->
    <?php if (!empty($items)): ?>
      <div style="text-align:left;margin-top:1.5rem">
        <h4 style="margin-bottom:.8rem;font-size:.9rem;color:var(--stone);text-transform:uppercase;letter-spacing:.06em">
          <?= current_lang() === 'pl' ? 'Zamówione produkty' : 'Ordered items' ?>
        </h4>
        <?php foreach ($items as $item): ?>
          <div style="display:flex;justify-content:space-between;gap:.5rem;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.9rem">
            <span><?= h(current_lang() === 'pl' ? $item['name'] : ($item['name_en'] ?: $item['name'])) ?> × <?= $item['qty'] ?></span>
            <strong><?= format_price($item['price'] * $item['qty']) ?></strong>
          </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;padding:.6rem 0;font-weight:700;color:var(--clay)">
          <span><?= t('cart.order_total') ?></span>
          <span><?= format_price($order['total']) ?></span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Shipping address -->
    <?php if ($addr): ?>
      <div style="text-align:left;margin-top:1rem;background:var(--sand);border-radius:8px;padding:1rem;font-size:.88rem">
        <div style="font-weight:700;margin-bottom:.4rem;color:var(--clay)">
          <?= current_lang() === 'pl' ? 'Adres dostawy' : 'Shipping address' ?>
        </div>
        <div><?= h($addr['first_name'] . ' ' . $addr['last_name']) ?></div>
        <div><?= h($addr['street']) ?></div>
        <div><?= h($addr['postcode'] . ' ' . $addr['city']) ?></div>
        <div><?= h($addr['country'] ?? '') ?></div>
      </div>
    <?php endif; ?>

    <a href="<?= url('shop.php') ?>" class="btn btn-primary btn-lg" style="margin-top:1.5rem">
      <i class="fas fa-shopping-bag"></i> <?= t('success.continue') ?>
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
