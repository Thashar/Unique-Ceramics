<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Zamówienia indywidualne';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $coId   = (int)($_POST['id']     ?? 0);
    $action = $_POST['action']       ?? '';
    if ($action === 'update' && $coId) {
        $st    = $_POST['status']       ?? 'new';
        $notes = trim($_POST['admin_notes'] ?? '');
        db_update('custom_orders', ['status' => $st, 'admin_notes' => $notes], 'id = ?', [$coId]);
        redirect(url('admin/custom-orders.php?updated=1'));
    }
}

$status  = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$sql    = 'SELECT * FROM custom_orders WHERE 1=1';
$params = [];
if ($status) { $sql .= ' AND status = ?'; $params[] = $status; }
$total  = (int)db_query("SELECT COUNT(*) FROM ($sql) x", $params)->fetchColumn();
$paging = paginate($total, $perPage, $page);
$sql   .= " ORDER BY created_at DESC LIMIT {$paging['limit']} OFFSET {$paging['offset']}";
$orders = db_fetch_all($sql, $params);

$statusOptions = ['new'=>'Nowe','review'=>'W trakcie','done'=>'Zrealizowane','cancelled'=>'Anulowane'];
$statusClasses = ['new'=>'status-new','review'=>'status-review','done'=>'status-done','cancelled'=>'status-cancelled'];

// Selected order to view
$viewId = (int)($_GET['view'] ?? 0);
$viewOrder = $viewId ? db_fetch('SELECT * FROM custom_orders WHERE id = ?', [$viewId]) : null;

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= $pageTitle ?> (<?= $total ?>)</h1>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Zaktualizowano.</div><?php endif; ?>

<!-- Status tabs -->
<div class="admin-tabs">
  <a href="?" class="admin-tab <?= !$status ? 'active' : '' ?>">Wszystkie (<?= (int)db_query("SELECT COUNT(*) FROM custom_orders")->fetchColumn() ?>)</a>
  <?php foreach ($statusOptions as $val => $lbl): ?>
    <a href="?status=<?= $val ?>" class="admin-tab <?= $status === $val ? 'active' : '' ?>">
      <?= $lbl ?> (<?= (int)db_query("SELECT COUNT(*) FROM custom_orders WHERE status = ?", [$val])->fetchColumn() ?>)
    </a>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:<?= $viewOrder ? '1fr 1fr' : '1fr' ?>;gap:1.2rem;align-items:start">
  <!-- List -->
  <div class="card">
    <?php if (empty($orders)): ?>
      <p style="text-align:center;padding:2rem;color:var(--stone)">Brak zamówień.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Klient</th><th>Produkt</th><th>Budżet</th><th>Status</th><th>Data</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($orders as $co): ?>
              <tr class="<?= $viewOrder && $viewOrder['id'] === $co['id'] ? 'fw-bold' : '' ?>">
                <td>
                  <div style="font-weight:600"><?= h($co['customer_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--stone)"><?= h($co['customer_email']) ?></div>
                </td>
                <td><?= h($co['product_type']) ?><br><small style="color:var(--stone)">× <?= $co['quantity'] ?></small></td>
                <td style="font-size:.82rem"><?= h($co['budget_range'] ?: '—') ?></td>
                <td><span class="status <?= $statusClasses[$co['status']] ?? '' ?>"><?= $statusOptions[$co['status']] ?? $co['status'] ?></span></td>
                <td style="font-size:.78rem;color:var(--stone)"><?= date('d.m.Y', strtotime($co['created_at'])) ?></td>
                <td><a href="?view=<?= $co['id'] ?><?= $status ? '&status='.$status : '' ?>" class="btn btn-outline btn-sm">Szczegóły</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Detail view -->
  <?php if ($viewOrder): ?>
    <div class="card">
      <div class="card-header">
        <h3><?= h($viewOrder['customer_name']) ?></h3>
        <a href="?" class="btn btn-outline btn-sm">✕ Zamknij</a>
      </div>

      <div style="font-size:.88rem;line-height:1.9">
        <div class="order-info-row"><span class="order-info-label">E-mail:</span><a href="mailto:<?= h($viewOrder['customer_email']) ?>"><?= h($viewOrder['customer_email']) ?></a></div>
        <?php if ($viewOrder['customer_phone']): ?>
          <div class="order-info-row"><span class="order-info-label">Telefon:</span><?= h($viewOrder['customer_phone']) ?></div>
        <?php endif; ?>
        <div class="order-info-row"><span class="order-info-label">Produkt:</span><strong><?= h($viewOrder['product_type']) ?></strong></div>
        <div class="order-info-row"><span class="order-info-label">Ilość:</span><?= $viewOrder['quantity'] ?></div>
        <?php if ($viewOrder['color_preference']): ?>
          <div class="order-info-row"><span class="order-info-label">Kolor:</span><?= h($viewOrder['color_preference']) ?></div>
        <?php endif; ?>
        <?php if ($viewOrder['pattern_preference']): ?>
          <div class="order-info-row"><span class="order-info-label">Wzór:</span><?= h($viewOrder['pattern_preference']) ?></div>
        <?php endif; ?>
        <?php if ($viewOrder['dedication']): ?>
          <div class="order-info-row"><span class="order-info-label">Dedykacja:</span><?= h($viewOrder['dedication']) ?></div>
        <?php endif; ?>
        <?php if ($viewOrder['budget_range']): ?>
          <div class="order-info-row"><span class="order-info-label">Budżet:</span><?= h($viewOrder['budget_range']) ?></div>
        <?php endif; ?>
        <?php if ($viewOrder['deadline']): ?>
          <div class="order-info-row"><span class="order-info-label">Termin:</span><?= h($viewOrder['deadline']) ?></div>
        <?php endif; ?>
      </div>

      <?php if ($viewOrder['description']): ?>
        <div style="background:var(--sand);border-radius:8px;padding:.9rem;margin:1rem 0;font-size:.88rem">
          <strong style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--clay)">Opis</strong><br>
          <?= nl2br(h($viewOrder['description'])) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $viewOrder['id'] ?>">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach ($statusOptions as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= $viewOrder['status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Notatki wewnętrzne</label>
          <textarea name="admin_notes" rows="4"><?= h($viewOrder['admin_notes'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:.5rem">
          <button type="submit" class="btn btn-primary" style="flex:1"><i class="fas fa-save"></i> Zapisz</button>
          <a href="mailto:<?= h($viewOrder['customer_email']) ?>?subject=Twoje zamówienie indywidualne — Unique Ceramics"
             class="btn btn-outline"><i class="fas fa-envelope"></i> Odpowiedz</a>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
