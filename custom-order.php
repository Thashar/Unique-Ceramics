<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Zamówienie indywidualne' : 'Custom Order';
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = current_lang() === 'pl' ? 'Błąd bezpieczeństwa.' : 'Security error.';
    } else {
        $name        = trim($_POST['name']        ?? '');
        $email       = trim($_POST['email']       ?? '');
        $phone       = trim($_POST['phone']       ?? '');
        $type        = trim($_POST['type']        ?? '');
        $qty         = max(1, (int)($_POST['quantity'] ?? 1));
        $color       = trim($_POST['color']       ?? '');
        $pattern     = trim($_POST['pattern']     ?? '');
        $dedication  = trim($_POST['dedication']  ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget      = trim($_POST['budget']      ?? '');
        $deadline    = trim($_POST['deadline']    ?? '');

        if (!$name)                                         $errors[] = t('custom.name')  . ' — wymagane';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('custom.email') . ' — wymagane';
        if (!$type)                                         $errors[] = t('custom.type')  . ' — wymagane';

        if (empty($errors)) {
            db_insert('custom_orders', [
                'customer_name'    => $name,
                'customer_email'   => $email,
                'customer_phone'   => $phone,
                'product_type'     => $type,
                'quantity'         => $qty,
                'color_preference' => $color,
                'pattern_preference'=> $pattern,
                'dedication'       => $dedication,
                'description'      => $description,
                'budget_range'     => $budget,
                'deadline'         => $deadline ?: null,
                'status'           => 'new',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
            $success = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div class="custom-order-hero">
  <div class="container-sm">
    <h1><?= t('custom.title') ?></h1>
    <p style="color:var(--stone);font-size:1.1rem;margin-top:.6rem"><?= t('custom.subtitle') ?></p>
    <p style="margin-top:.8rem;color:var(--clay);font-size:.9rem">
      <i class="fas fa-clock"></i> <?= t('custom.note') ?>
    </p>
  </div>
</div>

<div class="container-sm section-sm">
  <?php if ($success): ?>
    <div class="alert alert-success" style="font-size:1rem;padding:1.2rem">
      <i class="fas fa-check-circle"></i> <strong><?= t('custom.success') ?></strong>
    </div>
  <?php else: ?>

    <!-- Steps -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center;margin-bottom:2.5rem">
      <?php
      $steps = current_lang() === 'pl'
        ? [['1', 'Wypełnij formularz'], ['2', 'Kontaktujemy się'], ['3', 'Tworzymy dla Ciebie']]
        : [['1', 'Fill the form'], ['2', "We'll contact you"], ['3', 'We create for you']];
      foreach ($steps as [$num, $label]): ?>
        <div>
          <div style="width:48px;height:48px;border-radius:50%;background:var(--terracotta);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;margin:0 auto .6rem"><?= $num ?></div>
          <div style="font-size:.88rem;font-weight:600"><?= $label ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="custom-form-card">
      <form method="post">
        <?= csrf_field() ?>

        <!-- Contact -->
        <div class="form-section">
          <div class="form-section-title"><?= current_lang() === 'pl' ? 'Dane kontaktowe' : 'Contact details' ?></div>
          <div class="form-grid">
            <div class="form-group">
              <label><?= t('custom.name') ?> *</label>
              <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required autocomplete="name">
            </div>
            <div class="form-group">
              <label><?= t('custom.phone') ?></label>
              <input type="tel" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" autocomplete="tel">
            </div>
            <div class="form-group full">
              <label><?= t('custom.email') ?> *</label>
              <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autocomplete="email">
            </div>
          </div>
        </div>

        <!-- Product details -->
        <div class="form-section">
          <div class="form-section-title"><?= current_lang() === 'pl' ? 'Szczegóły zamówienia' : 'Order details' ?></div>
          <div class="form-grid">
            <div class="form-group">
              <label><?= t('custom.type') ?> *</label>
              <input type="text" name="type" value="<?= h($_POST['type'] ?? '') ?>" required
                     placeholder="<?= h(t('custom.type_ph')) ?>">
            </div>
            <div class="form-group">
              <label><?= t('custom.quantity') ?></label>
              <input type="number" name="quantity" value="<?= h($_POST['quantity'] ?? '1') ?>" min="1" max="100">
            </div>
            <div class="form-group">
              <label><?= t('custom.color') ?></label>
              <input type="text" name="color" value="<?= h($_POST['color'] ?? '') ?>"
                     placeholder="<?= h(t('custom.color_ph')) ?>">
            </div>
            <div class="form-group">
              <label><?= t('custom.pattern') ?></label>
              <input type="text" name="pattern" value="<?= h($_POST['pattern'] ?? '') ?>"
                     placeholder="<?= h(t('custom.pattern_ph')) ?>">
            </div>
            <div class="form-group full">
              <label><?= t('custom.dedication') ?></label>
              <input type="text" name="dedication" value="<?= h($_POST['dedication'] ?? '') ?>"
                     placeholder="<?= h(t('custom.dedication_ph')) ?>">
            </div>
            <div class="form-group full">
              <label><?= t('custom.description') ?></label>
              <textarea name="description" rows="5" placeholder="<?= h(t('custom.description_ph')) ?>"><?= h($_POST['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Budget & Timeline -->
        <div class="form-section" style="border:none;padding-bottom:0;margin-bottom:1.2rem">
          <div class="form-section-title"><?= current_lang() === 'pl' ? 'Budżet i termin' : 'Budget & timeline' ?></div>
          <div class="form-grid">
            <div class="form-group">
              <label><?= t('custom.budget') ?></label>
              <select name="budget">
                <option value="">— <?= current_lang() === 'pl' ? 'wybierz' : 'select' ?> —</option>
                <?php foreach (t('custom.budget_opt') as $opt): ?>
                  <option value="<?= h($opt) ?>" <?= ($_POST['budget'] ?? '') === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label><?= t('custom.deadline') ?></label>
              <input type="date" name="deadline" value="<?= h($_POST['deadline'] ?? '') ?>"
                     min="<?= date('Y-m-d', strtotime('+14 days')) ?>">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
          <i class="fas fa-paper-plane"></i> <?= t('custom.submit') ?>
        </button>
        <p style="text-align:center;font-size:.8rem;color:var(--stone);margin-top:.8rem">
          <?= t('common.required_fields') ?>
        </p>
      </form>
    </div>

    <!-- Contact alternatives -->
    <div style="text-align:center;margin-top:2rem;padding:1.5rem;background:var(--sand);border-radius:12px">
      <p style="font-weight:700;margin-bottom:.8rem"><?= current_lang() === 'pl' ? 'Wolisz porozmawiać?' : 'Prefer to talk?' ?></p>
      <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap">
        <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>" class="btn btn-outline">
          <i class="fas fa-phone-alt"></i> <?= SITE_PHONE ?>
        </a>
        <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" class="btn btn-outline">
          <i class="fab fa-instagram"></i> Instagram DM
        </a>
      </div>
    </div>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
