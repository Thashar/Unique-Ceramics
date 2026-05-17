<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Kategorie';
$errors    = [];

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $cid   = (int)($_POST['id'] ?? 0);
        $namePl = trim($_POST['name_pl'] ?? '');
        $nameEn = trim($_POST['name_en'] ?? '');
        $slug   = trim($_POST['slug']   ?? '') ?: slugify($namePl);
        $active = isset($_POST['active']) ? 1 : 0;
        $sort   = (int)($_POST['sort_order'] ?? 0);

        if (!$namePl) { $errors[] = 'Nazwa (PL) jest wymagana.'; }

        if (empty($errors)) {
            $img = null;
            if (!empty($_FILES['image']['name'])) {
                $img = handle_image_upload('image', 'categories/');
            }

            $data = ['name_pl' => $namePl, 'name_en' => $nameEn, 'slug' => $slug, 'active' => $active, 'sort_order' => $sort];
            if ($img) $data['image'] = $img;

            if ($cid) {
                db_update('categories', $data, 'id = ?', [$cid]);
            } else {
                db_insert('categories', $data);
            }
            redirect(url('admin/categories.php?saved=1'));
        }
    } elseif ($action === 'delete') {
        $cid = (int)($_POST['id'] ?? 0);
        db_query('DELETE FROM categories WHERE id = ?', [$cid]);
        redirect(url('admin/categories.php?deleted=1'));
    }
}

$categories  = db_fetch_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY sort_order ASC, name_pl ASC');
$editId      = (int)($_GET['edit'] ?? 0);
$editCat     = $editId ? db_fetch('SELECT * FROM categories WHERE id = ?', [$editId]) : null;

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= $pageTitle ?></h1>
</div>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Kategoria zapisana!</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Kategoria usunięta.</div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="alert alert-error"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:1.2rem;align-items:start">
  <!-- List -->
  <div class="card">
    <div class="card-header">
      <h3>Wszystkie kategorie (<?= count($categories) ?>)</h3>
    </div>
    <?php if (empty($categories)): ?>
      <p style="color:var(--stone);text-align:center;padding:2rem">Brak kategorii. Dodaj pierwszą kategorię →</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Obraz</th><th>Nazwa</th><th>Slug</th><th>Produkty</th><th>Aktywna</th><th>Kolejność</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td>
                  <div class="table-img">
                    <?php if ($cat['image']): ?>
                      <img src="<?= upload_url($cat['image']) ?>" alt="">
                    <?php else: ?>
                      <div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:var(--sand);border-radius:6px;font-size:1.2rem">🏺</div>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div style="font-weight:600"><?= h($cat['name_pl']) ?></div>
                  <?php if ($cat['name_en']): ?><div style="font-size:.75rem;color:var(--stone)"><?= h($cat['name_en']) ?></div><?php endif; ?>
                </td>
                <td style="font-size:.8rem;color:var(--stone)"><?= h($cat['slug']) ?></td>
                <td><?= $cat['product_count'] ?></td>
                <td>
                  <span class="status <?= $cat['active'] ? 'status-paid' : 'status-cancelled' ?>">
                    <?= $cat['active'] ? 'Tak' : 'Nie' ?>
                  </span>
                </td>
                <td><?= $cat['sort_order'] ?></td>
                <td>
                  <div class="td-actions">
                    <a href="?edit=<?= $cat['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i></a>
                    <?php if ($cat['product_count'] == 0): ?>
                      <form method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"
                                data-confirm="Usunąć kategorię &quot;<?= h($cat['name_pl']) ?>&quot;?">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Form -->
  <div class="card">
    <div class="card-header">
      <h3><?= $editCat ? 'Edytuj: ' . h($editCat['name_pl']) : 'Nowa kategoria' ?></h3>
      <?php if ($editCat): ?><a href="?" class="btn btn-outline btn-sm">Wyczyść</a><?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">

      <div class="form-group">
        <label>Nazwa (PL) *</label>
        <input type="text" name="name_pl" value="<?= h($editCat['name_pl'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Nazwa (EN)</label>
        <input type="text" name="name_en" value="<?= h($editCat['name_en'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="<?= h($editCat['slug'] ?? '') ?>" placeholder="auto">
      </div>
      <div class="form-group">
        <label>Kolejność wyświetlania</label>
        <input type="number" name="sort_order" value="<?= h($editCat['sort_order'] ?? 0) ?>" min="0">
      </div>
      <div class="form-group">
        <label>Zdjęcie kategorii</label>
        <?php if ($editCat && $editCat['image']): ?>
          <img src="<?= upload_url($editCat['image']) ?>" alt="" style="height:80px;border-radius:6px;margin-bottom:.5rem">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
      </div>
      <label style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;cursor:pointer">
        <input type="checkbox" name="active" value="1" style="width:auto" <?= ($editCat['active'] ?? 1) ? 'checked' : '' ?>>
        <span>Aktywna (widoczna w sklepie)</span>
      </label>
      <button type="submit" class="btn btn-primary" style="width:100%">
        <i class="fas fa-save"></i> <?= $editCat ? 'Zapisz zmiany' : 'Dodaj kategorię' ?>
      </button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
