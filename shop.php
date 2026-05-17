<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Sklep' : 'Shop';

// Filters from GET
$catSlug  = $_GET['cat']      ?? '';
$search   = trim($_GET['s']   ?? '');
$sort     = $_GET['sort']     ?? 'newest';
$minPrice = $_GET['min']      ?? '';
$maxPrice = $_GET['max']      ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

$category = $catSlug ? get_category_by_slug($catSlug) : null;

$filters = [
    'sort'     => $sort,
    'search'   => $search,
    'min_price'=> $minPrice !== '' ? (float)$minPrice : null,
    'max_price'=> $maxPrice !== '' ? (float)$maxPrice : null,
];
if ($category) $filters['category_id'] = $category['id'];

$total   = count_products($filters);
$paging  = paginate($total, $perPage, $page);
$filters['limit']  = $paging['limit'];
$filters['offset'] = $paging['offset'];

$products   = get_products($filters);
$categories = get_categories();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-sm">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= url('index.php') ?>"><?= t('nav.home') ?></a>
    <span class="breadcrumb-sep">/</span>
    <span><?= t('nav.shop') ?></span>
    <?php if ($category): ?>
      <span class="breadcrumb-sep">/</span>
      <span><?= category_name($category) ?></span>
    <?php endif; ?>
  </div>

  <h1 class="mb-3"><?= $category ? category_name($category) : t('shop.title') ?></h1>

  <!-- Search bar -->
  <form class="shop-search-form" method="get" action="">
    <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>
    <input type="search" name="s" placeholder="<?= h(t('shop.search_ph')) ?>"
           value="<?= h($search) ?>" autocomplete="off">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-search"></i>
    </button>
  </form>

  <div class="shop-layout">
    <!-- Sidebar -->
    <aside class="shop-sidebar">
      <!-- Categories -->
      <div class="sidebar-section">
        <div class="sidebar-title"><i class="fas fa-tag"></i> <?= current_lang() === 'pl' ? 'Kategorie' : 'Categories' ?></div>
        <div class="cat-filter-list">
          <a href="<?= url('shop.php') ?>" class="<?= !$catSlug ? 'active' : '' ?>">
            <?= t('shop.all_cats') ?>
            <span class="cat-filter-count"><?= count_products() ?></span>
          </a>
          <?php foreach ($categories as $cat):
            $catCount = count_products(['category_id' => $cat['id']]);
          ?>
            <a href="<?= url('shop.php?cat=' . $cat['slug']) ?>"
               class="<?= $catSlug === $cat['slug'] ? 'active' : '' ?>">
              <?= category_name($cat) ?>
              <span class="cat-filter-count"><?= $catCount ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Price filter -->
      <div class="sidebar-section">
        <div class="sidebar-title"><i class="fas fa-coins"></i> <?= t('shop.price_range') ?></div>
        <form method="get" action="">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>
          <?php if ($search): ?><input type="hidden" name="s" value="<?= h($search) ?>"><?php endif; ?>
          <input type="hidden" name="sort" value="<?= h($sort) ?>">
          <div class="price-range-inputs">
            <input type="number" name="min" placeholder="<?= t('shop.price_from') ?>" value="<?= h($minPrice) ?>" min="0" step="1">
            <input type="number" name="max" placeholder="<?= t('shop.price_to') ?>"   value="<?= h($maxPrice) ?>" min="0" step="1">
          </div>
          <button type="submit" class="btn btn-outline btn-sm price-submit mt-1">
            <?= t('shop.filter') ?>
          </button>
        </form>
      </div>

      <!-- Custom order CTA -->
      <div style="background:var(--sand);border-radius:8px;padding:1rem;text-align:center">
        <div style="font-size:1.8rem;margin-bottom:.4rem">🎨</div>
        <div style="font-weight:700;font-size:.9rem;margin-bottom:.4rem">
          <?= current_lang() === 'pl' ? 'Nie znalazłeś?' : "Can't find it?" ?>
        </div>
        <p style="font-size:.8rem;color:var(--stone);margin-bottom:.8rem">
          <?= current_lang() === 'pl' ? 'Zamów ceramikę szytą na miarę!' : 'Order custom-made ceramics!' ?>
        </p>
        <a href="<?= url('custom-order.php') ?>" class="btn btn-primary btn-sm btn-block">
          <?= t('nav.custom') ?>
        </a>
      </div>
    </aside>

    <!-- Products area -->
    <div>
      <!-- Sort + results header -->
      <div class="shop-header">
        <span class="shop-results">
          <?= $total ?> <?= t('shop.results') ?>
          <?php if ($search): ?> — "<strong><?= h($search) ?></strong>"<?php endif; ?>
        </span>
        <form method="get" action="">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= h($catSlug) ?>"><?php endif; ?>
          <?php if ($search): ?><input type="hidden" name="s"   value="<?= h($search) ?>"><?php endif; ?>
          <?php if ($minPrice): ?><input type="hidden" name="min" value="<?= h($minPrice) ?>"><?php endif; ?>
          <?php if ($maxPrice): ?><input type="hidden" name="max" value="<?= h($maxPrice) ?>"><?php endif; ?>
          <select name="sort" class="shop-sort" onchange="this.form.submit()">
            <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>><?= t('shop.sort_newest') ?></option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>><?= t('shop.sort_price_a') ?></option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>><?= t('shop.sort_price_d') ?></option>
            <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>><?= t('shop.sort_name') ?></option>
          </select>
        </form>
      </div>

      <?php if (empty($products)): ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> <?= t('shop.no_products') ?>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($products as $product):
            $img = get_product_main_image($product);
          ?>
            <article class="product-card">
              <a href="<?= url('product.php?slug=' . $product['slug']) ?>" class="product-card-img">
                <?php if ($img !== 'placeholder.jpg'): ?>
                  <img src="<?= upload_url($img) ?>" alt="<?= product_name($product) ?>" loading="lazy">
                <?php else: ?>
                  <div class="placeholder-img">🏺</div>
                <?php endif; ?>
                <?php if ($product['stock'] < 1): ?>
                  <span class="product-badge badge-out"><?= t('product.out_of_stock') ?></span>
                <?php elseif ($product['price_before']): ?>
                  <span class="product-badge badge-sale"><?= t('common.sale') ?></span>
                <?php endif; ?>
              </a>
              <div class="product-card-body">
                <?php if ($product['cat_name_pl']): ?>
                  <div class="product-card-cat"><?= h($product['cat_name_' . current_lang()] ?: $product['cat_name_pl']) ?></div>
                <?php endif; ?>
                <div class="product-card-name">
                  <a href="<?= url('product.php?slug=' . $product['slug']) ?>"><?= product_name($product) ?></a>
                </div>
                <div class="product-card-footer">
                  <div>
                    <div class="product-price"><?= format_price($product['price']) ?></div>
                    <?php if ($product['price_before']): ?>
                      <div class="product-price-before"><?= format_price($product['price_before']) ?></div>
                    <?php endif; ?>
                  </div>
                  <?php if ($product['stock'] > 0): ?>
                    <button class="btn-cart btn-cart-ajax" data-id="<?= $product['id'] ?>">
                      <i class="fas fa-shopping-bag"></i>
                    </button>
                  <?php else: ?>
                    <button class="btn-cart" disabled><i class="fas fa-times"></i></button>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($paging['pages'] > 1): ?>
          <div class="pagination">
            <?php if ($paging['current'] > 1): ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['page' => $paging['current'] - 1])) ?>">&#8249;</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $paging['pages']; $p++): ?>
              <?php if ($p === $paging['current']): ?>
                <span class="active"><?= $p ?></span>
              <?php else: ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($paging['current'] < $paging['pages']): ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['page' => $paging['current'] + 1])) ?>">&#8250;</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
