<?php
// Load language
$langCode = current_lang();
$lang = require ROOT_DIR . '/includes/lang/' . $langCode . '.php';

// Cart count
require_once ROOT_DIR . '/includes/cart-functions.php';
$cartCount = cart_count();

// Current page for active nav
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="<?= $pageDescription ?? 'Unique Ceramics — ręcznie tworzona ceramika użytkowa. Kubki, talerze, świeczniki, dzbanki i zestawy. Personalizacja i zamówienia indywidualne.' ?>">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/main.css?v=<?= filemtime(ROOT_DIR . '/assets/css/main.css') ?>">
  <?php if (isset($extraHead)) echo $extraHead; ?>
  <script>
    const BASE_PATH   = '<?= BASE_PATH ?>';
    const CSRF_TOKEN  = '<?= csrf_token() ?>';
    const CURRENT_LANG= '<?= $langCode ?>';
  </script>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <!-- Top bar -->
  <div class="topbar">
    <div class="container">
      <div class="topbar-left">
        <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><i class="fas fa-phone-alt"></i> <?= SITE_PHONE ?></a>
        <a href="mailto:<?= SITE_EMAIL ?>"><i class="fas fa-envelope"></i> <?= SITE_EMAIL ?></a>
      </div>
      <div class="topbar-right">
        <?php if (SITE_INSTAGRAM): ?>
          <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i> Instagram</a>
        <?php endif; ?>
        <a href="<?= BASE_PATH ?>/lang-switch.php"><?= other_lang() === 'en' ? '🇬🇧 EN' : '🇵🇱 PL' ?></a>
      </div>
    </div>
  </div>

  <!-- Main nav -->
  <div class="container">
    <div class="nav-wrap">
      <!-- Logo -->
      <a href="<?= url('index.php') ?>" class="logo">
        <img src="<?= BASE_PATH ?>/assets/images/logo.png?v=<?= filemtime(ROOT_DIR . '/assets/images/logo.png') ?>" alt="<?= SITE_NAME ?>" class="logo-img" onerror="this.style.display='none'">
        <div class="logo-text">
          Unique Ceramics
          <span><?= t('footer.tagline') ?></span>
        </div>
      </a>

      <!-- Desktop nav -->
      <nav class="main-nav" aria-label="Main navigation">
        <a href="<?= url('index.php') ?>"          class="<?= $currentPage === 'index'          ? 'active' : '' ?>"><?= t('nav.home') ?></a>
        <a href="<?= url('shop.php') ?>"           class="<?= $currentPage === 'shop'           ? 'active' : '' ?>"><?= t('nav.shop') ?></a>
        <a href="<?= url('about.php') ?>"          class="<?= $currentPage === 'about'          ? 'active' : '' ?>"><?= t('nav.about') ?></a>
        <a href="<?= url('workshops.php') ?>"      class="<?= $currentPage === 'workshops'      ? 'active' : '' ?>"><?= t('nav.workshops') ?></a>
        <a href="<?= url('custom-order.php') ?>"   class="<?= $currentPage === 'custom-order'   ? 'active' : '' ?>"><?= t('nav.custom') ?></a>
        <a href="<?= url('contact.php') ?>"        class="<?= $currentPage === 'contact'        ? 'active' : '' ?>"><?= t('nav.contact') ?></a>
      </nav>

      <!-- Actions -->
      <div class="nav-actions">
        <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>" class="nav-phone">
          <i class="fas fa-phone-alt"></i> <?= SITE_PHONE ?>
        </a>
        <a href="<?= url('cart.php') ?>" class="cart-btn" aria-label="<?= t('nav.cart') ?>">
          <i class="fas fa-shopping-bag"></i>
          <span class="d-none d-sm-inline"><?= t('nav.cart') ?></span>
          <span class="cart-count" <?= $cartCount === 0 ? 'style="display:none"' : '' ?>><?= $cartCount ?></span>
        </a>
        <!-- Hamburger -->
        <button class="hamburger" aria-label="Menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- MOBILE NAV -->
<nav class="mobile-nav" aria-label="Mobile navigation">
  <button class="mobile-nav-close" aria-label="Close">✕</button>
  <a href="<?= url('index.php') ?>"><?= t('nav.home') ?></a>
  <a href="<?= url('shop.php') ?>"><?= t('nav.shop') ?></a>
  <a href="<?= url('about.php') ?>"><?= t('nav.about') ?></a>
  <a href="<?= url('workshops.php') ?>"><?= t('nav.workshops') ?></a>
  <a href="<?= url('custom-order.php') ?>"><?= t('nav.custom') ?></a>
  <a href="<?= url('contact.php') ?>"><?= t('nav.contact') ?></a>
  <a href="<?= url('cart.php') ?>" style="color:var(--terracotta);font-weight:700"><?= t('nav.cart') ?> <?= $cartCount > 0 ? "($cartCount)" : '' ?></a>
  <a href="<?= BASE_PATH ?>/lang-switch.php"><?= other_lang() === 'en' ? '🇬🇧 English' : '🇵🇱 Polski' ?></a>
</nav>

<main>
