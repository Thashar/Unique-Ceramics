<?php
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Admin | <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css?v=<?= filemtime(ROOT_DIR . '/assets/css/admin.css') ?>">
  <script>const BASE_PATH = '<?= BASE_PATH ?>', CSRF_TOKEN = '<?= csrf_token() ?>';</script>
</head>
<body>
<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-logo">
    <img src="<?= BASE_PATH ?>/assets/images/logo.png" alt="UC" onerror="this.style.display='none'">
    <div class="sidebar-logo-text">
      <?= SITE_NAME ?>
      <small>Panel administracyjny</small>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Zarządzanie</div>
    <a href="<?= BASE_PATH ?>/admin/dashboard.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-chart-line"></i></span> Dashboard
    </a>
    <a href="<?= BASE_PATH ?>/admin/products.php" class="<?= in_array($adminPage, ['products','product-edit']) ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-box"></i></span> Produkty
    </a>
    <a href="<?= BASE_PATH ?>/admin/categories.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-tags"></i></span> Kategorie
    </a>

    <div class="sidebar-nav-label">Zamówienia</div>
    <a href="<?= BASE_PATH ?>/admin/orders.php" class="<?= in_array($adminPage, ['orders','order-view']) ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-shopping-bag"></i></span> Zamówienia
      <?php
      $newOrders = db_query("SELECT COUNT(*) FROM orders WHERE order_status = 'new'")->fetchColumn();
      if ($newOrders > 0): ?>
        <span class="badge"><?= $newOrders ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= BASE_PATH ?>/admin/custom-orders.php" class="<?= $adminPage === 'custom-orders' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-paint-brush"></i></span> Zam. indywidualne
      <?php
      $newCustom = db_query("SELECT COUNT(*) FROM custom_orders WHERE status = 'new'")->fetchColumn();
      if ($newCustom > 0): ?>
        <span class="badge"><?= $newCustom ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar-nav-label">System</div>
    <a href="<?= BASE_PATH ?>/admin/settings.php" class="<?= $adminPage === 'settings' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-cog"></i></span> Ustawienia
    </a>
    <a href="<?= BASE_PATH ?>/index.php" target="_blank">
      <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span> Podgląd strony
    </a>
    <a href="<?= BASE_PATH ?>/admin/logout.php">
      <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span> Wyloguj się
    </a>
  </nav>

  <div class="sidebar-footer">
    Zalogowany jako <strong><?= h($adminName) ?></strong>
  </div>
</aside>

<!-- MAIN -->
<div class="admin-main">
  <!-- Top bar -->
  <div class="admin-topbar">
    <div style="display:flex;align-items:center;gap:.8rem">
      <button onclick="document.getElementById('adminSidebar').classList.toggle('open')"
              style="display:none;font-size:1.2rem;color:var(--charcoal)" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>
      <div class="topbar-title"><?= isset($pageTitle) ? h($pageTitle) : 'Panel administracyjny' ?></div>
    </div>
    <div class="topbar-user">
      <i class="fas fa-user-circle"></i> <?= h($adminName) ?>
      <a href="<?= BASE_PATH ?>/admin/logout.php">Wyloguj</a>
    </div>
  </div>

  <!-- Page content -->
  <div class="admin-content">
