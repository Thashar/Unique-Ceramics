<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Zamówienia';

// Filters
$status  = $_GET['status'] ?? '';
$search  = trim($_GET['s']  ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$sql    = 'SELECT * FROM orders WHERE 1=1';
$params = [];
if ($status) { $sql .= ' AND order_status = ?'; $params[] = $status; }
if ($search) { $sql .= ' AND (order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)';
    $s = "%$search%"; $params = array_merge($params, [$s, $s, $s]); }
$total   = (int)db_query("SELECT COUNT(*) FROM ($sql) x", $params)->fetchColumn();
$paging  = paginate($total, $perPage, $page);
$sql    .= " ORDER BY created_at DESC LIMIT {$paging['limit']} OFFSET {$paging['offset']}";
$orders  = db_fetch_all($sql, $params);

$statusOptions = [
    ''          => 'Wszystkie',
    'new'       => 'Nowe',
    'pending'   => 'Oczekuje',
    'paid'      => 'Opłacone',
    'shipped'   => 'Wysłane',
    'done'      => 'Zrealizowane',
    'cancelled' => 'Anulowane',
];
$statusLabels = $statusOptions;
$statusClasses = ['new'=>'status-new','pending'=>'status-pending','paid'=>'status-paid','shipped'=>'status-shipped','done'=>'status-done','cancelled'=>'status-cancelled'];

$payMethods = ['transfer'=>'Przelew','payu'=>'PayU','przelewy24'=>'P24','stripe'=>'Stripe'];

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= $pageTitle ?> (<?= $total ?>)</h1>
</div>

<?php if (isset($_GET['updated'])): ?>
  <div class="alert alert-success">Status zamówienia zaktualizowany.</div>
<?php endif; ?>

<!-- Filters -->
<form class="filters-bar" method="get">
  <input type="search" name="s" placeholder="Nr zamówienia, klient, e-mail..." value="<?= h($search) ?>">
  <select name="status">
    <?php foreach ($statusOptions as $val => $lbl): ?>
      <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Filtruj</button>
  <a href="<?= BASE_PATH ?>/admin/orders.php" class="btn btn-outline">Wyczyść</a>
</form>

<!-- Status tabs -->
<div class="admin-tabs" style="margin-bottom:1rem">
  <?php foreach ($statusOptions as $val => $lbl): ?>
    <a href="?status=<?= $val ?>" class="admin-tab <?= $status === $val ? 'active' : '' ?>"><?= $lbl ?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <?php if (empty($orders)): ?>
    <p style="text-align:center;padding:2rem;color:var(--stone)">Brak zamówień.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Nr</th><th>Klient</th><th>Kwota</th><th>Płatność</th><th>Status zam.</th><th>Status płat.</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $ord): ?>
            <tr>
              <td><a href="<?= BASE_PATH ?>/admin/order-view.php?id=<?= $ord['id'] ?>" style="font-weight:700;color:var(--terracotta)"><?= h($ord['order_number']) ?></a></td>
              <td>
                <div style="font-weight:600"><?= h($ord['customer_name']) ?></div>
                <div style="font-size:.75rem;color:var(--stone)"><?= h($ord['customer_email']) ?></div>
              </td>
              <td><strong><?= format_price($ord['total']) ?></strong></td>
              <td><?= h($payMethods[$ord['payment_method']] ?? $ord['payment_method']) ?></td>
              <td><span class="status <?= $statusClasses[$ord['order_status']] ?? '' ?>"><?= $statusLabels[$ord['order_status']] ?? $ord['order_status'] ?></span></td>
              <td>
                <span class="status <?= $ord['payment_status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
                  <?= $ord['payment_status'] === 'paid' ? 'Opłacone' : 'Oczekuje' ?>
                </span>
              </td>
              <td style="font-size:.8rem;color:var(--stone)"><?= date('d.m.Y H:i', strtotime($ord['created_at'])) ?></td>
              <td>
                <a href="<?= BASE_PATH ?>/admin/order-view.php?id=<?= $ord['id'] ?>" class="btn btn-outline btn-sm">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($paging['pages'] > 1): ?>
      <div class="pagination" style="margin-top:1rem">
        <?php for ($p = 1; $p <= $paging['pages']; $p++): ?>
          <?php if ($p === $paging['current']): ?>
            <span class="active"><?= $p ?></span>
          <?php else: ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
