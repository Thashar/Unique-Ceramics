<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Kontakt' : 'Contact';
$success   = false;
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Security error.';
    } else {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name)                                        $errors[] = t('contact.name')    . ' — wymagane';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('contact.email') . ' — wymagane';
        if (!$message)                                     $errors[] = t('contact.message') . ' — wymagane';

        if (empty($errors)) {
            // In production, send an email here using mail() or a library like PHPMailer
            $to      = SITE_EMAIL;
            $subject = 'Wiadomość ze strony Unique Ceramics — ' . $name;
            $body    = "Imię i nazwisko: {$name}\nE-mail: {$email}\n\nWiadomość:\n{$message}";
            $headers = "From: noreply@uniqueceramics.pl\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";
            @mail($to, $subject, $body, $headers);
            $success = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
$isPl = current_lang() === 'pl';
?>

<!-- Hero -->
<div style="background:var(--sand);padding:4rem 0;text-align:center">
  <div class="container-sm">
    <h1><?= t('contact.title') ?></h1>
    <p style="color:var(--stone);margin-top:.6rem">
      <?= $isPl ? 'Masz pytania? Chętnie odpiszemy — zazwyczaj w ciągu 24 godzin.' : 'Have questions? We\'ll respond — usually within 24 hours.' ?>
    </p>
  </div>
</div>

<div class="container section-sm">
  <div class="contact-layout">
    <!-- Form -->
    <div class="contact-card">
      <h2 style="margin-bottom:1.2rem"><?= $isPl ? 'Wyślij wiadomość' : 'Send a message' ?></h2>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> <?= t('contact.success') ?>
        </div>
      <?php else: ?>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="form-group">
            <label><?= t('contact.name') ?> *</label>
            <input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required autocomplete="name">
          </div>
          <div class="form-group">
            <label><?= t('contact.email') ?> *</label>
            <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autocomplete="email">
          </div>
          <div class="form-group">
            <label><?= t('contact.message') ?> *</label>
            <textarea name="message" rows="6" required><?= h($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i class="fas fa-paper-plane"></i> <?= t('contact.send') ?>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Contact info -->
    <div>
      <div class="contact-card" style="margin-bottom:1rem">
        <h3 style="margin-bottom:1.2rem"><?= t('contact.find_us') ?></h3>

        <div class="contact-info-row">
          <div class="contact-info-icon">📞</div>
          <div>
            <div class="contact-info-label"><?= t('contact.phone') ?></div>
            <div class="contact-info-val"><a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a></div>
          </div>
        </div>
        <div class="contact-info-row">
          <div class="contact-info-icon">✉️</div>
          <div>
            <div class="contact-info-label">E-mail</div>
            <div class="contact-info-val"><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></div>
          </div>
        </div>
        <div class="contact-info-row">
          <div class="contact-info-icon">📸</div>
          <div>
            <div class="contact-info-label"><?= t('contact.instagram') ?></div>
            <div class="contact-info-val">
              <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener">@unique.ceramics</a>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ -->
      <div class="contact-card">
        <h3 style="margin-bottom:1rem"><?= $isPl ? 'Najczęstsze pytania' : 'FAQ' ?></h3>
        <?php
        $faqs = $isPl ? [
          ['Jak długo trwa realizacja zamówienia?', 'Standardowe zamówienia wysyłamy w 3–5 dni roboczych. Zamówienia indywidualne to zazwyczaj 2–4 tygodnie.'],
          ['Czy mogę odebrać zamówienie osobiście?', 'Odbiór osobisty jest możliwy po wcześniejszym umówieniu przez telefon lub Instagram.'],
          ['Czy ceramika nadaje się do zmywarki?', 'Tak, ceramika nadaje się do zmywarki.'],
          ['Czy robicie ceramikę z personalizacją?', 'Tak! Możemy wgryźć, namalować lub wytłoczyć dowolny wzór, tekst lub dedykację.'],
        ] : [
          ['How long does an order take?', 'Standard orders are shipped in 3–5 business days. Custom orders typically take 2–4 weeks.'],
          ['Can I collect my order in person?', 'In-person collection is possible after prior arrangement by phone or Instagram.'],
          ['Is the ceramics dishwasher-safe?', 'Yes, the ceramics are dishwasher safe.'],
          ['Do you make personalised ceramics?', 'Yes! We can carve, paint, or emboss any pattern, text, or dedication.'],
        ];
        foreach ($faqs as [$q, $a]):
        ?>
          <details style="margin-bottom:.8rem;border-bottom:1px solid var(--border);padding-bottom:.8rem">
            <summary style="font-weight:600;cursor:pointer;font-size:.9rem;padding:.3rem 0"><?= $q ?></summary>
            <p style="font-size:.88rem;color:var(--stone);margin-top:.5rem;padding-left:.5rem"><?= $a ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Instagram CTA -->
<section class="section-sm" style="background:var(--sand)">
  <div class="container text-center">
    <h2><?= $isPl ? 'Śledź nas na Instagramie' : 'Follow us on Instagram' ?></h2>
    <p style="color:var(--stone);margin:.8rem 0 1.5rem">
      <?= $isPl ? 'Bądź na bieżąco z nowościami i zakulisowymi zdjęciami z pracowni!' : 'Stay up to date with new pieces and behind-the-scenes photos from the studio!' ?>
    </p>
    <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" class="btn btn-primary btn-lg">
      <i class="fab fa-instagram"></i> @unique.ceramics
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
