<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (is_admin_logged()) redirect(url('admin/dashboard.php'));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Błąd bezpieczeństwa.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (admin_login($username, $password)) {
            redirect(url('admin/dashboard.php'));
        } else {
            $error = 'Nieprawidłowy login lub hasło.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logowanie — <?= SITE_NAME ?> Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f4f1ee; }
    .login-box { background:#fff; border-radius:16px; padding:2.5rem 2rem; width:100%; max-width:380px; box-shadow:0 8px 40px rgba(44,44,44,.12); }
    .login-logo { text-align:center; margin-bottom:1.8rem; }
    .login-logo img { width:64px; margin:0 auto .6rem; }
    .login-logo h1 { font-family:'Playfair Display',serif; font-size:1.5rem; color:var(--charcoal); }
    .login-logo p  { font-size:.82rem; color:var(--stone); margin-top:.2rem; }
  </style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <img src="<?= BASE_PATH ?>/assets/images/logo.png?v=<?= filemtime(ROOT_DIR . '/assets/images/logo.png') ?>" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
    <h1><?= SITE_NAME ?></h1>
    <p>Panel administracyjny</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Login</label>
      <input type="text" name="username" value="<?= h($_POST['username'] ?? '') ?>" required autofocus autocomplete="username">
    </div>
    <div class="form-group" style="margin-top:.7rem">
      <label>Hasło</label>
      <input type="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:1.2rem">
      <i class="fas fa-sign-in-alt"></i> Zaloguj się
    </button>
  </form>
  <div style="text-align:center;margin-top:1.2rem">
    <a href="<?= url('index.php') ?>" style="font-size:.8rem;color:var(--stone)">← Wróć do strony</a>
  </div>
</div>
</body>
</html>
