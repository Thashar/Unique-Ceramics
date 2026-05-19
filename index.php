<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = SITE_NAME . ' — ' . (current_lang() === 'pl' ? 'Ręcznie Tworzona Ceramika' : 'Handcrafted Ceramics');

require_once __DIR__ . '/includes/header.php';

$featured    = get_products(['featured' => true, 'limit' => 8]);
$categories  = get_categories();
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-content">
    <span class="hero-tag">✨ <?= current_lang() === 'pl' ? 'Ręcznie wykonane' : 'Handmade' ?></span>
    <h1><?= t('hero.title') ?></h1>
    <p class="hero-sub"><?= t('hero.subtitle') ?></p>
    <div class="hero-btns">
      <a href="<?= url('shop.php') ?>" class="btn btn-primary btn-lg">
        <i class="fas fa-shopping-bag"></i> <?= t('hero.cta1') ?>
      </a>
      <a href="<?= url('custom-order.php') ?>" class="btn btn-outline btn-lg">
        <i class="fas fa-paint-brush"></i> <?= t('hero.cta2') ?>
      </a>
    </div>
  </div>
  <div class="hero-image">
    <img src="<?= BASE_PATH ?>/assets/images/hero.jpg"
         alt="Unique Ceramics — handmade mugs"
         onerror="this.parentElement.style.background='var(--sand)'">
  </div>
</section>

<!-- VALUES -->
<section class="section-sm" style="background:var(--white)">
  <div class="container">
    <div class="values-grid">
      <?php foreach (t('home.values') as $val): ?>
        <div class="value-card">
          <div class="value-card-icon"><?= $val['icon'] ?></div>
          <h4><?= h($val['title']) ?></h4>
          <p><?= h($val['text']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<?php if (!empty($categories)): ?>
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= t('home.categories_title') ?></h2>
    <p class="section-sub"><?= t('home.categories_sub') ?></p>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= url('shop.php?cat=' . $cat['slug']) ?>" class="cat-card">
          <div class="cat-card-img">
            <?php if ($cat['image']): ?>
              <img src="<?= upload_url($cat['image']) ?>" alt="<?= category_name($cat) ?>">
            <?php else: ?>
              <div class="placeholder-img">🏺</div>
            <?php endif; ?>
          </div>
          <div class="cat-card-name"><?= category_name($cat) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED PRODUCTS -->
<?php if (!empty($featured)): ?>
<section class="section" style="background:var(--sand)">
  <div class="container">
    <h2 class="section-title"><?= t('home.featured_title') ?></h2>
    <p class="section-sub"><?= t('home.featured_sub') ?></p>
    <div class="products-grid">
      <?php foreach ($featured as $product):
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
                <button class="btn-cart btn-cart-ajax"
                        data-id="<?= $product['id'] ?>"
                        title="<?= h(t('common.add_to_cart')) ?>">
                  <i class="fas fa-shopping-bag"></i> <?= t('common.add_to_cart') ?>
                </button>
              <?php else: ?>
                <button class="btn-cart" disabled><?= t('product.out_of_stock') ?></button>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= url('shop.php') ?>" class="btn btn-outline btn-lg">
        <?= current_lang() === 'pl' ? 'Zobacz wszystkie produkty' : 'View all products' ?>
        <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ABOUT SECTION -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="about-section">
      <div class="about-images">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260508-WA0011.jpg"
             alt="Unique Ceramics workshop"
             onerror="this.parentElement.innerHTML='<div class=\'placeholder-img\'>🏺</div>'">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260515-WA0033.jpg" alt="Ceramic mug"
             onerror="this.style.display='none'">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260515-WA0035.jpg" alt="Ceramic set"
             onerror="this.style.display='none'">
      </div>
      <div>
        <h2><?= t('home.about_title') ?></h2>
        <p style="color:var(--stone);line-height:1.75;margin:1rem 0 1.5rem"><?= t('home.about_text') ?></p>
        <a href="<?= url('about.php') ?>" class="btn btn-primary">
          <?= t('home.about_cta') ?> <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- WORKSHOP CTA -->
<section class="section-sm" style="background:var(--cream)">
  <div class="container">
    <div class="workshop-cta">
      <div>
        <h2><?= t('home.workshop_title') ?></h2>
        <p><?= t('home.workshop_text') ?></p>
      </div>
      <a href="<?= url('workshops.php') ?>" class="btn btn-outline btn-lg">
        <i class="fas fa-hands"></i> <?= t('home.workshop_cta') ?>
      </a>
    </div>
  </div>
</section>

<!-- INSTAGRAM -->
<section class="section-sm" style="background:var(--sand)">
  <div class="container text-center">
    <h2 class="section-title"><?= t('home.insta_title') ?></h2>
    <p style="margin-bottom:1.5rem;color:var(--stone)">
      <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" style="color:var(--terracotta);font-weight:700">
        <i class="fab fa-instagram"></i> @unique.ceramics
      </a>
    </p>
    <!-- Instagram gallery grid with real photos -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.6rem;max-width:900px;margin:0 auto">
      <?php
      $instaPhotos = [
        'IMG-20260515-WA0040.jpg','IMG-20260515-WA0041.jpg','IMG-20260515-WA0042.jpg',
        'IMG-20260515-WA0043.jpg','IMG-20260515-WA0044.jpg','IMG-20260515-WA0045.jpg',
      ];
      foreach ($instaPhotos as $photo): ?>
        <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener"
           style="aspect-ratio:1;overflow:hidden;border-radius:8px;display:block">
          <img src="<?= BASE_PATH ?>/uploads/products/<?= $photo ?>"
               alt="Unique Ceramics Instagram"
               style="width:100%;height:100%;object-fit:cover;transition:transform .3s"
               onmouseover="this.style.transform='scale(1.05)'"
               onmouseout="this.style.transform=''"
               onerror="this.parentElement.style.display='none'">
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
