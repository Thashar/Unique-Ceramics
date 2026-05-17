<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Produkty';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && verify_csrf()) {
    $id = (int)($_POST['id'] ?? 0);
    $product = get_product_by_id($id);
    if ($product) {
        // Remove images
        foreach (get_product_images($product) as $img) {
            $path = UPLOAD_DIR . $img;
            if (file_exists($path)) @unlink($path);
        }
        db_query('DELETE FROM products WHERE id = ?', [$id]);
    }
    redirect(url('admin/products.php?deleted=1'));
}

// Handle toggle featured / active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['toggle_featured','toggle_active']) && verify_csrf()) {
    $id = (int)($_POST['id'] ?? 0);
    $field = $_POST['action'] === 'toggle_featured' ? 'featured' : 'active';
    db_query("UPDATE products SET {$field} = 1 - {$field} WHERE id = ?", [$id]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Filters
$search   = trim($_GET['s']   ?? '');
$catId    = (int)($_GET['cat'] ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;

$sql    = 'SELECT p.*, c.name_pl AS cat FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];
if ($search) { $sql .= ' AND (p.name_pl LIKE ? OR p.name_en LIKE ?)'; $s = "%$search%"; $params[] = $s; $params[] = $s; }
if ($catId)  { $sql .= ' AND p.category_id = ?'; $params[] = $catId; }
$total   = (int)db_query("SELECT COUNT(*) FROM ($sql) x", $params)->fetchColumn();
$paging  = paginate($total, $perPage, $page);
$sql    .= " ORDER BY p.id DESC LIMIT {$paging['limit']} OFFSET {$paging['offset']}";
$products = db_fetch_all($sql, $params);
$categories = get_categories(false);

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= $pageTitle ?> (<?= $total ?>)</h1>
  <a href="<?= BASE_PATH ?>/admin/product-edit.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Dodaj produkt
  </a>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Produkt usunięty.</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Produkt zapisany pomyślnie!</div><?php endif; ?>

<!-- Filters -->
<form class="filters-bar" method="get">
  <input type="search" name="s" placeholder="Szukaj produktów..." value="<?= h($search) ?>">
  <select name="cat">
    <option value="">Wszystkie kategorie</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= $cat['id'] ?>" <?= $catId === (int)$cat['id'] ? 'selected' : '' ?>>
        <?= h($cat['name_pl']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Filtruj</button>
  <a href="<?= BASE_PATH ?>/admin/products.php" class="btn btn-outline">Wyczyść</a>
</form>

<div class="card">
  <?php if (empty($products)): ?>
    <p style="color:var(--stone);text-align:center;padding:2rem">Brak produktów. <a href="<?= BASE_PATH ?>/admin/product-edit.php">Dodaj pierwszy produkt →</a></p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Zdjęcie</th>
            <th>Nazwa</th>
            <th>Kategoria</th>
            <th>Cena</th>
            <th>Stok</th>
            <th>Wyróżniony</th>
            <th>Aktywny</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p):
            $imgs = get_product_images($p);
            $img  = !empty($imgs[0]) ? upload_url($imgs[0]) : null;
          ?>
            <tr>
              <td>
                <div class="table-img">
                  <?php if ($img): ?>
                    <img src="<?= h($img) ?>" alt="<?= h($p['name_pl']) ?>">
                  <?php else: ?>
                    <div class="placeholder-img" style="width:44px;height:44px;font-size:1.2rem">🏺</div>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div style="font-weight:600"><?= h($p['name_pl']) ?></div>
                <?php if ($p['name_en'] && $p['name_en'] !== $p['name_pl']): ?>
                  <div style="font-size:.75rem;color:var(--stone)"><?= h($p['name_en']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= h($p['cat'] ?? '—') ?></td>
              <td><strong><?= format_price($p['price']) ?></strong></td>
              <td>
                <span style="<?= (int)$p['stock'] < 5 ? 'color:var(--error);font-weight:700' : '' ?>">
                  <?= (int)$p['stock'] ?>
                </span>
              </td>
              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_featured">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $p['featured'] ? 'btn-warning' : 'btn-outline' ?>" title="Wyróżniony">
                    <i class="fas fa-star"></i>
                  </button>
                </form>
              </td>
              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_active">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-sm <?= $p['active'] ? 'btn-success' : 'btn-outline' ?>" title="Aktywny">
                    <i class="fas fa-<?= $p['active'] ? 'eye' : 'eye-slash' ?>"></i>
                  </button>
                </form>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= BASE_PATH ?>/admin/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= BASE_PATH ?>/product.php?slug=<?= $p['slug'] ?>" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i>
                  </a>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm"
                            data-confirm="Usunąć produkt &quot;<?= h($p['name_pl']) ?>&quot;? Tej akcji nie można cofnąć.">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
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
