<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Dashboard';

// Stats
$stats = [
    'orders_new'    => (int)db_query("SELECT COUNT(*) FROM orders WHERE order_status = 'new'")->fetchColumn(),
    'orders_total'  => (int)db_query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'revenue'       => (float)db_query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
    'products'      => (int)db_query("SELECT COUNT(*) FROM products WHERE active = 1")->fetchColumn(),
    'custom_new'    => (int)db_query("SELECT COUNT(*) FROM custom_orders WHERE status = 'new'")->fetchColumn(),
];

$recentOrders = db_fetch_all("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8");
$recentCustom = db_fetch_all("SELECT * FROM custom_orders ORDER BY created_at DESC LIMIT 5");

// Revenue last 7 days
$revenue7 = db_fetch_all("SELECT DATE(created_at) as day, SUM(total) as total FROM orders WHERE created_at >= date('now','-7 days') GROUP BY DATE(created_at) ORDER BY day");

require __DIR__ . '/includes/admin-header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-shopping-bag" style="color:var(--terracotta)"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['orders_new'] ?></div>
      <div class="stat-label">Nowe zamówienia</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-chart-line" style="color:var(--success)"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['orders_total'] ?></div>
      <div class="stat-label">Wszystkie zamówienia</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon clay"><i class="fas fa-coins" style="color:var(--clay)"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= format_price($stats['revenue']) ?></div>
      <div class="stat-label">Przychód (opłacone)</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon sage"><i class="fas fa-box" style="color:var(--sage)"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['products'] ?></div>
      <div class="stat-label">Aktywne produkty</div>
    </div>
  </div>
</div>

<!-- Alerts -->
<?php if ($stats['orders_new'] > 0): ?>
  <div class="alert alert-warning">
    <i class="fas fa-bell"></i>
    Masz <strong><?= $stats['orders_new'] ?></strong> nowych zamówień do obsłużenia.
    <a href="<?= BASE_PATH ?>/admin/orders.php?status=new" style="font-weight:700;margin-left:.5rem">Przejdź →</a>
  </div>
<?php endif; ?>
<?php if ($stats['custom_new'] > 0): ?>
  <div class="alert alert-info">
    <i class="fas fa-paint-brush"></i>
    <strong><?= $stats['custom_new'] ?></strong> nowych zamówień indywidualnych czeka na odpowiedź.
    <a href="<?= BASE_PATH ?>/admin/custom-orders.php" style="font-weight:700;margin-left:.5rem">Przejdź →</a>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.2rem;align-items:start">
  <!-- Recent orders -->
  <div class="card">
    <div class="card-header">
      <h3>Ostatnie zamówienia</h3>
      <a href="<?= BASE_PATH ?>/admin/orders.php" class="btn btn-outline btn-sm">Wszystkie</a>
    </div>
    <?php if (empty($recentOrders)): ?>
      <p style="color:var(--stone);font-size:.9rem">Brak zamówień.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nr</th><th>Klient</th><th>Kwota</th><th>Status</th><th>Data</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $ord): ?>
              <tr>
                <td><strong><?= h($ord['order_number']) ?></strong></td>
                <td><?= h($ord['customer_name']) ?></td>
                <td><strong><?= format_price($ord['total']) ?></strong></td>
                <td><?php
                  $statusMap = ['new'=>'Nowe','pending'=>'Oczekuje','paid'=>'Opłacone','shipped'=>'Wysłane','done'=>'Gotowe','cancelled'=>'Anulowane'];
                  $statusClass = ['new'=>'status-new','pending'=>'status-pending','paid'=>'status-paid','shipped'=>'status-shipped','done'=>'status-done','cancelled'=>'status-cancelled'];
                  $st = $ord['order_status'];
                  echo '<span class="status ' . ($statusClass[$st] ?? '') . '">' . ($statusMap[$st] ?? $st) . '</span>';
                ?></td>
                <td style="font-size:.8rem;color:var(--stone)"><?= date('d.m.Y', strtotime($ord['created_at'])) ?></td>
                <td><a href="<?= BASE_PATH ?>/admin/order-view.php?id=<?= $ord['id'] ?>" class="btn btn-outline btn-sm">Podgląd</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick actions + Custom orders -->
  <div>
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><h3>Szybkie akcje</h3></div>
      <div style="display:flex;flex-direction:column;gap:.6rem">
        <a href="<?= BASE_PATH ?>/admin/product-edit.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> Dodaj produkt
        </a>
        <a href="<?= BASE_PATH ?>/admin/orders.php" class="btn btn-secondary">
          <i class="fas fa-shopping-bag"></i> Zamówienia
        </a>
        <a href="<?= BASE_PATH ?>/admin/custom-orders.php" class="btn btn-secondary">
          <i class="fas fa-paint-brush"></i> Zam. indywidualne
        </a>
        <a href="<?= BASE_PATH ?>/admin/settings.php" class="btn btn-secondary">
          <i class="fas fa-cog"></i> Ustawienia
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Zam. indywidualne</h3></div>
      <?php if (empty($recentCustom)): ?>
        <p style="color:var(--stone);font-size:.9rem">Brak zapytań.</p>
      <?php else: ?>
        <?php foreach ($recentCustom as $co): ?>
          <div style="padding:.7rem 0;border-bottom:1px solid var(--border);font-size:.85rem">
            <div style="font-weight:600"><?= h($co['customer_name']) ?></div>
            <div style="color:var(--stone)"><?= h($co['product_type']) ?> — <?= date('d.m', strtotime($co['created_at'])) ?></div>
            <span class="status status-<?= $co['status'] === 'new' ? 'new' : 'pending' ?>"><?= $co['status'] === 'new' ? 'Nowe' : $co['status'] ?></span>
          </div>
        <?php endforeach; ?>
        <a href="<?= BASE_PATH ?>/admin/custom-orders.php" class="btn btn-outline btn-sm" style="margin-top:.8rem;width:100%;justify-content:center">Wszystkie</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
