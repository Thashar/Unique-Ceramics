<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Ustawienia';
$success   = false;
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? 'general';

    if ($action === 'general') {
        $fields = ['bank_account','bank_name','shipping_cost','shipping_free_from','site_email','site_phone'];
        foreach ($fields as $f) {
            set_setting($f, trim($_POST[$f] ?? ''));
        }
        $success = 'Ustawienia ogólne zapisane.';
    } elseif ($action === 'payment') {
        $fields = ['payu_pos_id','payu_md5_key','przelewy24_merchant_id','przelewy24_crc','stripe_public_key','stripe_secret_key'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) set_setting($f, trim($_POST[$f]));
        }
        $success = 'Ustawienia płatności zapisane.';
    } elseif ($action === 'password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cfm = $_POST['confirm_password'] ?? '';
        $admin = db_fetch('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
        if (!$admin || !password_verify($old, $admin['password_hash'])) {
            $errors[] = 'Nieprawidłowe obecne hasło.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'Nowe hasło musi mieć co najmniej 8 znaków.';
        } elseif ($new !== $cfm) {
            $errors[] = 'Hasła nie pasują do siebie.';
        } else {
            admin_change_password($_SESSION['admin_id'], $new);
            $success = 'Hasło zostało zmienione.';
        }
    }
}

$tab = $_GET['tab'] ?? 'general';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header"><h1><?= $pageTitle ?></h1></div>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?= h($success) ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="alert alert-error"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>

<div class="admin-tabs">
  <a href="?tab=general"  class="admin-tab <?= $tab === 'general'  ? 'active' : '' ?>">Ogólne</a>
  <a href="?tab=payment"  class="admin-tab <?= $tab === 'payment'  ? 'active' : '' ?>">Płatności</a>
  <a href="?tab=password" class="admin-tab <?= $tab === 'password' ? 'active' : '' ?>">Hasło</a>
</div>

<?php if ($tab === 'general'): ?>
<div class="card">
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="general">

    <div class="form-section">
      <div class="form-section-title">Dane kontaktowe</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Telefon kontaktowy</label>
          <input type="text" name="site_phone" value="<?= h(get_setting('site_phone', SITE_PHONE)) ?>">
        </div>
        <div class="form-group">
          <label>E-mail kontaktowy</label>
          <input type="email" name="site_email" value="<?= h(get_setting('site_email', SITE_EMAIL)) ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">Dane do przelewu bankowego</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Nazwa banku</label>
          <input type="text" name="bank_name" value="<?= h(get_setting('bank_name')) ?>" placeholder="np. PKO BP">
        </div>
        <div class="form-group">
          <label>Numer konta IBAN</label>
          <input type="text" name="bank_account" value="<?= h(get_setting('bank_account')) ?>" placeholder="PL61 1090 1014 0000 0712 1981 2874">
          <div class="form-hint">Numer konta wyświetlany po złożeniu zamówienia z przelewem</div>
        </div>
      </div>
    </div>

    <div class="form-section" style="border:none;padding-bottom:0">
      <div class="form-section-title">Wysyłka</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Koszt wysyłki (PLN)</label>
          <input type="number" name="shipping_cost" value="<?= h(get_setting('shipping_cost', SHIPPING_COST)) ?>" min="0" step="0.01">
        </div>
        <div class="form-group">
          <label>Darmowa wysyłka od (PLN)</label>
          <input type="number" name="shipping_free_from" value="<?= h(get_setting('shipping_free_from', SHIPPING_FREE_FROM)) ?>" min="0" step="1">
          <div class="form-hint">Wpisz 0 aby wyłączyć darmową wysyłkę</div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz ustawienia</button>
  </form>
</div>

<?php elseif ($tab === 'payment'): ?>
<div class="card">
  <div style="background:rgba(212,168,67,.1);border:1px solid rgba(212,168,67,.3);border-radius:8px;padding:1rem;margin-bottom:1.2rem;font-size:.88rem">
    <i class="fas fa-exclamation-triangle" style="color:var(--warning)"></i>
    <strong>Uwaga:</strong> Klucze API są wrażliwymi danymi. Nie udostępniaj ich osobom trzecim.
    Integracja bramek płatności wymaga aktywnego konta u dostawcy.
  </div>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="payment">

    <div class="form-section">
      <div class="form-section-title">PayU</div>
      <div class="form-grid">
        <div class="form-group">
          <label>POS ID</label>
          <input type="text" name="payu_pos_id" value="<?= h(get_setting('payu_pos_id')) ?>" placeholder="Numer POS z panelu PayU">
        </div>
        <div class="form-group">
          <label>MD5 Key</label>
          <input type="password" name="payu_md5_key" value="<?= get_setting('payu_md5_key') ? '••••••••' : '' ?>" placeholder="Klucz MD5 z panelu PayU">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">Przelewy24</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Merchant ID</label>
          <input type="text" name="przelewy24_merchant_id" value="<?= h(get_setting('przelewy24_merchant_id')) ?>">
        </div>
        <div class="form-group">
          <label>CRC Key</label>
          <input type="password" name="przelewy24_crc" value="<?= get_setting('przelewy24_crc') ? '••••••••' : '' ?>">
        </div>
      </div>
    </div>

    <div class="form-section" style="border:none;padding-bottom:0">
      <div class="form-section-title">Stripe</div>
      <div class="form-grid">
        <div class="form-group">
          <label>Publishable Key (pk_...)</label>
          <input type="text" name="stripe_public_key" value="<?= h(get_setting('stripe_public_key')) ?>" placeholder="pk_live_...">
        </div>
        <div class="form-group">
          <label>Secret Key (sk_...)</label>
          <input type="password" name="stripe_secret_key" value="<?= get_setting('stripe_secret_key') ? '••••••••' : '' ?>" placeholder="sk_live_...">
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz klucze API</button>
  </form>
</div>

<?php elseif ($tab === 'password'): ?>
<div class="card" style="max-width:400px">
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <div class="form-group">
      <label>Obecne hasło</label>
      <input type="password" name="old_password" required autocomplete="current-password">
    </div>
    <div class="form-group">
      <label>Nowe hasło (min. 8 znaków)</label>
      <input type="password" name="new_password" required autocomplete="new-password" minlength="8">
    </div>
    <div class="form-group">
      <label>Potwierdź nowe hasło</label>
      <input type="password" name="confirm_password" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:.5rem">
      <i class="fas fa-key"></i> Zmień hasło
    </button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
