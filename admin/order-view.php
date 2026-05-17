<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$id    = (int)($_GET['id'] ?? 0);
$order = get_order_by_id($id);
if (!$order) redirect(url('admin/orders.php'));

$pageTitle = 'Zamówienie ' . $order['order_number'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $newStatus = $_POST['order_status']   ?? $order['order_status'];
        $newPay    = $_POST['payment_status'] ?? $order['payment_status'];
        db_update('orders', ['order_status' => $newStatus, 'payment_status' => $newPay, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    } elseif ($action === 'add_note') {
        $note = trim($_POST['note'] ?? '');
        if ($note) {
            $existing = $order['notes'] ? $order['notes'] . "\n\n" : '';
            db_update('orders', ['notes' => $existing . '[' . date('d.m.Y H:i') . '] ' . $note, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        }
    }
    redirect(url('admin/order-view.php?id=' . $id . '&updated=1'));
}

$order  = get_order_by_id($id);
$items  = json_decode($order['items'] ?? '[]', true);
$addr   = json_decode($order['shipping_address'] ?? '{}', true);

$statusOptions = ['new'=>'Nowe','pending'=>'Oczekuje','paid'=>'Opłacone','shipped'=>'Wysłane','done'=>'Zrealizowane','cancelled'=>'Anulowane'];
$payStatus     = ['pending'=>'Oczekuje','paid'=>'Opłacone','failed'=>'Nieudana','refunded'=>'Zwrócona'];
$statusClasses = ['new'=>'status-new','pending'=>'status-pending','paid'=>'status-paid','shipped'=>'status-shipped','done'=>'status-done','cancelled'=>'status-cancelled'];
$payMethods    = ['transfer'=>'Przelew bankowy','payu'=>'PayU','przelewy24'=>'Przelewy24','stripe'=>'Stripe (karta)'];

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= h($order['order_number']) ?></h1>
  <div style="display:flex;gap:.6rem;flex-wrap:wrap">
    <span class="status <?= $statusClasses[$order['order_status']] ?? '' ?>" style="font-size:.9rem;padding:.3rem .9rem">
      <?= $statusOptions[$order['order_status']] ?? $order['order_status'] ?>
    </span>
    <a href="<?= BASE_PATH ?>/admin/orders.php" class="btn btn-outline">← Lista zamówień</a>
  </div>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Zaktualizowano.</div><?php endif; ?>

<div class="order-detail-grid">
  <!-- Left: items + notes -->
  <div>
    <!-- Order items -->
    <div class="card">
      <div class="card-header"><h3>Zamówione produkty</h3></div>
      <table>
        <thead><tr><th>Produkt</th><th>Cena jedn.</th><th>Ilość</th><th>Łącznie</th></tr></thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:.7rem">
                  <?php if (!empty($item['image']) && $item['image'] !== 'placeholder.jpg'): ?>
                    <div class="table-img"><img src="<?= upload_url($item['image']) ?>" alt=""></div>
                  <?php endif; ?>
                  <div>
                    <div style="font-weight:600"><?= h($item['name']) ?></div>
                    <?php if (!empty($item['slug'])): ?>
                      <a href="<?= BASE_PATH ?>/product.php?slug=<?= $item['slug'] ?>" target="_blank"
                         style="font-size:.75rem;color:var(--stone)">Podgląd →</a>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td><?= format_price($item['price']) ?></td>
              <td><?= $item['qty'] ?></td>
              <td><strong><?= format_price($item['price'] * $item['qty']) ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:600;padding:.7rem 1rem">Suma częściowa:</td>
            <td><?= format_price($order['subtotal']) ?></td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:right;font-weight:600;padding:.3rem 1rem">Wysyłka:</td>
            <td><?= $order['shipping_cost'] > 0 ? format_price($order['shipping_cost']) : 'GRATIS' ?></td>
          </tr>
          <tr style="font-size:1.1rem">
            <td colspan="3" style="text-align:right;font-weight:700;padding:.7rem 1rem;color:var(--clay)">DO ZAPŁATY:</td>
            <td><strong><?= format_price($order['total']) ?></strong></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Notes -->
    <div class="card">
      <div class="card-header"><h3>Notatki</h3></div>
      <?php if ($order['notes']): ?>
        <div style="background:var(--sand);border-radius:8px;padding:1rem;white-space:pre-line;font-size:.88rem;margin-bottom:1rem">
          <?= h($order['notes']) ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_note">
        <div class="form-group">
          <label>Dodaj notatkę (wewnętrzną)</label>
          <textarea name="note" rows="3" placeholder="Np. klient dzwonił, paczka wysłana..."></textarea>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-plus"></i> Dodaj</button>
      </form>
    </div>
  </div>

  <!-- Right: customer + status -->
  <div>
    <!-- Customer info -->
    <div class="card">
      <div class="card-header"><h3>Dane klienta</h3></div>
      <div class="order-info-row"><span class="order-info-label">Imię i nazwisko:</span><strong><?= h($order['customer_name']) ?></strong></div>
      <div class="order-info-row"><span class="order-info-label">E-mail:</span><a href="mailto:<?= h($order['customer_email']) ?>"><?= h($order['customer_email']) ?></a></div>
      <?php if ($order['customer_phone']): ?>
        <div class="order-info-row"><span class="order-info-label">Telefon:</span><a href="tel:<?= h($order['customer_phone']) ?>"><?= h($order['customer_phone']) ?></a></div>
      <?php endif; ?>
    </div>

    <!-- Shipping address -->
    <?php if ($addr): ?>
      <div class="card">
        <div class="card-header"><h3>Adres dostawy</h3></div>
        <div style="font-size:.9rem;line-height:1.8">
          <strong><?= h($addr['first_name'] ?? '') ?> <?= h($addr['last_name'] ?? '') ?></strong><br>
          <?= h($addr['street'] ?? '') ?><br>
          <?= h($addr['postcode'] ?? '') ?> <?= h($addr['city'] ?? '') ?><br>
          <?= h($addr['country'] ?? '') ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Payment info -->
    <div class="card">
      <div class="card-header"><h3>Płatność</h3></div>
      <div class="order-info-row"><span class="order-info-label">Metoda:</span><?= h($payMethods[$order['payment_method']] ?? $order['payment_method']) ?></div>
      <div class="order-info-row"><span class="order-info-label">Status:</span>
        <span class="status <?= $order['payment_status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
          <?= $payStatus[$order['payment_status']] ?? $order['payment_status'] ?>
        </span>
      </div>
      <div class="order-info-row"><span class="order-info-label">Zamówione:</span><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
    </div>

    <!-- Update status -->
    <div class="card">
      <div class="card-header"><h3>Zmień status</h3></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_status">
        <div class="form-group">
          <label>Status zamówienia</label>
          <select name="order_status">
            <?php foreach ($statusOptions as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= $order['order_status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Status płatności</label>
          <select name="payment_status">
            <?php foreach ($payStatus as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= $order['payment_status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">
          <i class="fas fa-save"></i> Zapisz zmiany
        </button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
