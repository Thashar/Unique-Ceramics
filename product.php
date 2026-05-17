<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug    = $_GET['slug'] ?? '';
$product = $slug ? get_product_by_slug($slug) : null;

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    redirect(url('shop.php'));
}

$images   = get_product_images($product);
$mainImg  = get_product_main_image($product);
$related  = get_products(['category_id' => $product['category_id'] ?? 0, 'limit' => 4]);
$related  = array_filter($related, fn($p) => $p['id'] !== $product['id']);

$pageTitle = product_name($product);
$pageDescription = strip_tags(product_description($product));

require_once __DIR__ . '/includes/header.php';
?>

<div class="container section-sm">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= url('index.php') ?>"><?= t('nav.home') ?></a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= url('shop.php') ?>"><?= t('nav.shop') ?></a>
    <?php if ($product['cat_name_pl']): ?>
      <span class="breadcrumb-sep">/</span>
      <a href="<?= url('shop.php?cat=' . slugify($product['cat_name_pl'])) ?>">
        <?= h($product['cat_name_' . current_lang()] ?: $product['cat_name_pl']) ?>
      </a>
    <?php endif; ?>
    <span class="breadcrumb-sep">/</span>
    <span><?= product_name($product) ?></span>
  </div>

  <!-- Product Detail -->
  <div class="product-detail">
    <!-- Gallery -->
    <div class="product-gallery">
      <div class="product-main-img">
        <?php if ($mainImg !== 'placeholder.jpg'): ?>
          <img id="mainProductImg" src="<?= upload_url($mainImg) ?>" alt="<?= product_name($product) ?>">
        <?php else: ?>
          <div class="placeholder-img" style="aspect-ratio:1">🏺</div>
        <?php endif; ?>
      </div>
      <?php if (count($images) > 1): ?>
        <div class="product-thumbs">
          <?php foreach ($images as $i => $img): ?>
            <div class="product-thumb <?= $i === 0 ? 'active' : '' ?>">
              <img src="<?= upload_url($img) ?>" alt="<?= product_name($product) ?> <?= $i + 1 ?>">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="product-info">
      <?php if ($product['cat_name_pl']): ?>
        <div class="product-info-cat">
          <a href="<?= url('shop.php?cat=' . slugify($product['cat_name_pl'])) ?>" style="color:var(--stone)">
            <?= h($product['cat_name_' . current_lang()] ?: $product['cat_name_pl']) ?>
          </a>
        </div>
      <?php endif; ?>

      <h1><?= product_name($product) ?></h1>

      <div class="product-info-price">
        <?= format_price($product['price']) ?>
        <?php if ($product['price_before']): ?>
          <span class="product-price-before" style="font-size:1rem;margin-left:.5rem">
            <?= format_price($product['price_before']) ?>
          </span>
        <?php endif; ?>
      </div>

      <p class="product-info-desc"><?= nl2br(h(product_description($product))) ?></p>

      <?php if ($product['stock'] > 0): ?>
        <form class="add-to-cart-form" method="post" action="<?= url('cart-action.php') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="id" value="<?= $product['id'] ?>">
          <input type="hidden" name="redirect" value="<?= h($_SERVER['REQUEST_URI']) ?>">

          <div class="qty-selector">
            <label><?= t('product.quantity') ?></label>
            <div class="qty-controls">
              <button type="button" data-action="minus">−</button>
              <input type="number" id="qty-input" name="qty" value="1" min="1" max="<?= $product['stock'] ?>">
              <button type="button" data-action="plus">+</button>
            </div>
            <span class="stock-info">
              <?= t('product.available') ?>: <?= $product['stock'] ?> <?= t('common.pieces') ?>
            </span>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block btn-cart-ajax" data-id="<?= $product['id'] ?>">
            <i class="fas fa-shopping-bag"></i> <?= t('product.add_to_cart') ?>
          </button>
        </form>

        <a href="<?= url('custom-order.php') ?>" class="btn btn-outline btn-block" style="margin-top:.6rem">
          <i class="fas fa-paint-brush"></i>
          <?= current_lang() === 'pl' ? 'Zamów wariant na miarę' : 'Order custom variant' ?>
        </a>

      <?php else: ?>
        <button class="btn btn-primary btn-lg btn-block" disabled>
          <i class="fas fa-times"></i> <?= t('product.out_of_stock') ?>
        </button>
        <a href="<?= url('custom-order.php') ?>" class="btn btn-outline btn-block" style="margin-top:.6rem">
          <i class="fas fa-paint-brush"></i>
          <?= current_lang() === 'pl' ? 'Zamów podobny produkt na zamówienie' : 'Order a similar piece custom' ?>
        </a>
      <?php endif; ?>

      <!-- Meta -->
      <div class="product-meta">
        <?php if ($product['sku']): ?>
          <div class="product-meta-row">
            <span class="product-meta-label">SKU:</span>
            <span><?= h($product['sku']) ?></span>
          </div>
        <?php endif; ?>
        <?php if ($product['cat_name_pl']): ?>
          <div class="product-meta-row">
            <span class="product-meta-label"><?= t('product.category') ?>:</span>
            <span><?= h($product['cat_name_' . current_lang()] ?: $product['cat_name_pl']) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="product-note">
        <i class="fas fa-hands"></i> <?= t('product.handmade_note') ?>
      </div>
      <div class="product-note" style="margin-top:.5rem">
        <i class="fas fa-truck"></i> <?= t('product.shipping_info') ?>
        <?php if (SHIPPING_FREE_ENABLED): ?>
          — <?= current_lang() === 'pl'
            ? 'Darmowa wysyłka przy zamówieniu powyżej ' . format_price(SHIPPING_FREE_FROM)
            : 'Free shipping on orders over ' . format_price(SHIPPING_FREE_FROM) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="product-tabs">
    <div class="tab-buttons">
      <button class="tab-btn active" data-tab="desc"><?= t('product.description') ?></button>
      <button class="tab-btn" data-tab="details"><?= t('product.details') ?></button>
    </div>
    <div class="tab-content active" data-tab="desc">
      <?= nl2br(h(product_description($product))) ?>
    </div>
    <div class="tab-content" data-tab="details">
      <p>🤍 <?= current_lang() === 'pl' ? 'Ręcznie formowana i wypalana ceramika' : 'Hand-formed and kiln-fired ceramics' ?></p>
      <p>🎨 <?= current_lang() === 'pl' ? 'Unikalne szkliwienie — każdy egzemplarz jest wyjątkowy' : 'Unique glazing — every piece is one of a kind' ?></p>
      <p>🍽️ <?= current_lang() === 'pl' ? 'Bezpieczna do kontaktu z żywnością' : 'Food-safe' ?></p>
      <p>🫧 <?= current_lang() === 'pl' ? 'Mycie ręczne zalecane' : 'Hand washing recommended' ?></p>
      <p>🚫 <?= current_lang() === 'pl' ? 'Nie nadaje się do mikrofali i zmywarki' : 'Not suitable for microwave or dishwasher' ?></p>
    </div>
  </div>

  <!-- Related products -->
  <?php if (!empty($related)): ?>
    <div style="margin-top:4rem">
      <h2 class="section-title" style="text-align:left;margin-bottom:1.5rem"><?= t('product.related') ?></h2>
      <div class="products-grid">
        <?php foreach (array_slice($related, 0, 4) as $rel):
          $relImg = get_product_main_image($rel);
        ?>
          <article class="product-card">
            <a href="<?= url('product.php?slug=' . $rel['slug']) ?>" class="product-card-img">
              <?php if ($relImg !== 'placeholder.jpg'): ?>
                <img src="<?= upload_url($relImg) ?>" alt="<?= product_name($rel) ?>" loading="lazy">
              <?php else: ?>
                <div class="placeholder-img">🏺</div>
              <?php endif; ?>
            </a>
            <div class="product-card-body">
              <div class="product-card-name">
                <a href="<?= url('product.php?slug=' . $rel['slug']) ?>"><?= product_name($rel) ?></a>
              </div>
              <div class="product-card-footer">
                <div class="product-price"><?= format_price($rel['price']) ?></div>
                <a href="<?= url('product.php?slug=' . $rel['slug']) ?>" class="btn-cart">
                  <?= t('common.view') ?> →
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
