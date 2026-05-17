<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$id      = (int)($_GET['id'] ?? 0);
$product = $id ? get_product_by_id($id) : null;
$isNew   = !$product;
$pageTitle = $isNew ? 'Nowy produkt' : 'Edytuj produkt';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Błąd CSRF.';
    } else {
        $data = [
            'name_pl'        => trim($_POST['name_pl']        ?? ''),
            'name_en'        => trim($_POST['name_en']        ?? ''),
            'slug'           => trim($_POST['slug']           ?? ''),
            'description_pl' => trim($_POST['description_pl'] ?? ''),
            'description_en' => trim($_POST['description_en'] ?? ''),
            'price'          => (float)($_POST['price']       ?? 0),
            'price_before'   => ($_POST['price_before'] ?? '') !== '' ? (float)$_POST['price_before'] : null,
            'category_id'    => ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null,
            'stock'          => max(0, (int)($_POST['stock']  ?? 0)),
            'sku'            => trim($_POST['sku']            ?? ''),
            'featured'       => isset($_POST['featured']) ? 1 : 0,
            'active'         => isset($_POST['active'])   ? 1 : 0,
        ];

        if (!$data['name_pl']) $errors[] = 'Nazwa (PL) jest wymagana.';
        if ($data['price'] <= 0) $errors[] = 'Cena musi być większa od 0.';

        if (!$data['slug']) $data['slug'] = slugify($data['name_pl']);

        // Check slug uniqueness
        $existSlug = db_fetch('SELECT id FROM products WHERE slug = ? AND id != ?', [$data['slug'], $id ?: 0]);
        if ($existSlug) { $data['slug'] .= '-' . time(); }

        // Handle image uploads
        $existingImages = $product ? get_product_images($product) : [];
        $keepImages = $_POST['keep_images'] ?? [];
        $remainImages = array_values(array_filter($existingImages, fn($img) => in_array($img, $keepImages)));

        $newImages = handle_multiple_image_upload('images', 'products/');
        $allImages = array_merge($remainImages, $newImages);
        $data['images'] = json_encode($allImages);

        if (empty($errors)) {
            if ($isNew) {
                $id = db_insert('products', $data + ['created_at' => date('Y-m-d H:i:s')]);
            } else {
                db_update('products', $data, 'id = ?', [$id]);
            }
            redirect(url('admin/products.php?saved=1'));
        }
    }
}

$categories = get_categories(false);

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header">
  <h1><?= $pageTitle ?></h1>
  <a href="<?= BASE_PATH ?>/admin/products.php" class="btn btn-outline">← Wróć do listy</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-error">
    <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.2rem;align-items:start">
    <!-- Main form -->
    <div>
      <!-- Names & Slug -->
      <div class="card">
        <div class="card-header"><h3>Podstawowe informacje</h3></div>
        <div class="form-grid">
          <div class="form-group">
            <label>Nazwa (PL) *</label>
            <input type="text" name="name_pl" value="<?= h($product['name_pl'] ?? $_POST['name_pl'] ?? '') ?>"
                   required oninput="autoSlug(this)">
          </div>
          <div class="form-group">
            <label>Nazwa (EN)</label>
            <input type="text" name="name_en" value="<?= h($product['name_en'] ?? $_POST['name_en'] ?? '') ?>">
          </div>
          <div class="form-group full">
            <label>Slug (URL) — wypełnia się automatycznie</label>
            <input type="text" name="slug" id="slugField"
                   value="<?= h($product['slug'] ?? $_POST['slug'] ?? '') ?>"
                   pattern="[a-z0-9\-]+" placeholder="np. kubek-espresso-rozowy">
            <div class="form-hint">Tylko małe litery, cyfry i myślniki. Np.: kubek-z-serduszkiem</div>
          </div>
        </div>
      </div>

      <!-- Descriptions -->
      <div class="card">
        <div class="card-header"><h3>Opisy</h3></div>
        <div class="form-group">
          <label>Opis (PL)</label>
          <textarea name="description_pl" class="rich" rows="6"><?= h($product['description_pl'] ?? $_POST['description_pl'] ?? '') ?></textarea>
        </div>
        <div class="form-group" style="margin-top:.8rem">
          <label>Opis (EN)</label>
          <textarea name="description_en" class="rich" rows="6"><?= h($product['description_en'] ?? $_POST['description_en'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Images -->
      <div class="card">
        <div class="card-header"><h3>Zdjęcia produktu</h3></div>
        <?php if ($product && !empty(get_product_images($product))): ?>
          <div style="margin-bottom:1rem">
            <div class="form-section-title">Aktualne zdjęcia (odznacz aby usunąć)</div>
            <div class="img-preview-grid">
              <?php foreach (get_product_images($product) as $img): ?>
                <label style="position:relative;cursor:pointer">
                  <input type="checkbox" name="keep_images[]" value="<?= h($img) ?>" checked
                         style="position:absolute;top:4px;left:4px;width:auto;z-index:1;accent-color:var(--terracotta)">
                  <div class="img-preview-item">
                    <img src="<?= upload_url($img) ?>" alt="">
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="img-upload-area" onclick="document.getElementById('imgInput').click()">
          <input type="file" id="imgInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                 onchange="previewImages(this,'imgPreview')">
          <div class="img-upload-icon">📷</div>
          <div class="img-upload-text">
            Kliknij aby wybrać zdjęcia<br>
            <small>JPG, PNG, WEBP — maks. 8 MB każde</small>
          </div>
        </div>
        <div class="img-preview-grid" id="imgPreview"></div>
      </div>
    </div>

    <!-- Sidebar -->
    <div>
      <!-- Price & Stock -->
      <div class="card">
        <div class="card-header"><h3>Cena i dostępność</h3></div>
        <div class="form-group">
          <label>Cena (<?= CURRENCY_SYMBOL ?>) *</label>
          <input type="number" name="price" value="<?= h($product['price'] ?? $_POST['price'] ?? '') ?>"
                 min="0.01" step="0.01" required>
        </div>
        <div class="form-group">
          <label>Cena przed przeceną (opcjonalnie)</label>
          <input type="number" name="price_before" value="<?= h($product['price_before'] ?? $_POST['price_before'] ?? '') ?>"
                 min="0" step="0.01">
        </div>
        <div class="form-group">
          <label>Stan magazynowy (sztuki)</label>
          <input type="number" name="stock" value="<?= h($product['stock'] ?? $_POST['stock'] ?? '0') ?>" min="0" step="1">
        </div>
        <div class="form-group">
          <label>SKU / kod produktu</label>
          <input type="text" name="sku" value="<?= h($product['sku'] ?? $_POST['sku'] ?? '') ?>" placeholder="np. UC-KUB-001">
        </div>
      </div>

      <!-- Category -->
      <div class="card">
        <div class="card-header"><h3>Kategoria</h3></div>
        <div class="form-group">
          <select name="category_id">
            <option value="">— Bez kategorii —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                <?= (int)($product['category_id'] ?? $_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= h($cat['name_pl']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Options -->
      <div class="card">
        <div class="card-header"><h3>Opcje</h3></div>
        <div style="display:flex;flex-direction:column;gap:.8rem">
          <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
            <input type="checkbox" name="active" value="1" style="width:auto;accent-color:var(--sage)"
                   <?= ($product['active'] ?? $_POST['active'] ?? 1) ? 'checked' : '' ?>>
            <span><strong>Aktywny</strong> — widoczny w sklepie</span>
          </label>
          <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
            <input type="checkbox" name="featured" value="1" style="width:auto;accent-color:var(--terracotta)"
                   <?= ($product['featured'] ?? $_POST['featured'] ?? 0) ? 'checked' : '' ?>>
            <span><strong>Wyróżniony</strong> — pokazywany na stronie głównej</span>
          </label>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-primary btn-lg" style="width:100%">
        <i class="fas fa-save"></i> <?= $isNew ? 'Dodaj produkt' : 'Zapisz zmiany' ?>
      </button>
      <?php if (!$isNew): ?>
        <a href="<?= BASE_PATH ?>/product.php?slug=<?= $product['slug'] ?>" target="_blank"
           class="btn btn-outline" style="width:100%;margin-top:.5rem;justify-content:center">
          <i class="fas fa-external-link-alt"></i> Podgląd na stronie
        </a>
      <?php endif; ?>
    </div>
  </div>
</form>

<script>
function autoSlug(input) {
  const slug = document.getElementById('slugField');
  if (!slug.dataset.manual) {
    const map = {'ą':'a','ć':'c','ę':'e','ł':'l','ń':'n','ó':'o','ś':'s','ź':'z','ż':'z'};
    let s = input.value.toLowerCase().split('').map(c => map[c] || c).join('');
    slug.value = s.replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  }
}
document.getElementById('slugField')?.addEventListener('input', () => {
  document.getElementById('slugField').dataset.manual = '1';
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
