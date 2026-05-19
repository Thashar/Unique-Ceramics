<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Warsztaty ceramiczne' : 'Ceramic Workshops';
require_once __DIR__ . '/includes/header.php';

$isPl = current_lang() === 'pl';
?>

<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--sand) 0%,var(--cream) 100%);padding:5rem 0;text-align:center">
  <div class="container-sm">
    <div style="font-size:3rem;margin-bottom:1rem">🏺</div>
    <h1><?= t('workshops.title') ?></h1>
    <p style="color:var(--stone);font-size:1.15rem;margin-top:.8rem"><?= t('workshops.subtitle') ?></p>
  </div>
</div>

<!-- Intro -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center">
      <div>
        <h2><?= $isPl ? 'Odkryj radość lepienia!' : 'Discover the joy of pottery!' ?></h2>
        <p style="color:var(--stone);line-height:1.8;margin-top:1rem">
          <?= $isPl
            ? 'Organizujemy warsztaty ceramiczne dla grup i indywidualnych uczestników. Idealne na urodziny, wieczory panieńskie, imprezy firmowe czy po prostu wyjątkowy wieczór z przyjaciółmi. Nie potrzebujesz żadnego doświadczenia — wszystkiego nauczymy Cię od podstaw.'
            : 'We organise ceramic workshops for groups and individuals. Perfect for birthdays, hen parties, corporate events, or simply a special evening with friends. No experience needed — we\'ll teach you everything from scratch.' ?>
        </p>
        <p style="color:var(--stone);line-height:1.8">
          <?= $isPl
            ? 'W trakcie warsztatu uformujecie własne wyroby z gliny, które po wypaleniu możecie odebrać lub wysłać pocztą. Każdy uczestnik wychodzi z wyjątkowym, własnoręcznie wykonanym dziełem.'
            : 'During the workshop you\'ll shape your own clay pieces, which after firing you can pick up or have shipped. Every participant leaves with a unique, hand-made piece.' ?>
        </p>
        <a href="<?= url('contact.php') ?>" class="btn btn-primary" style="margin-top:1.2rem">
          <i class="fas fa-calendar-check"></i> <?= t('workshops.book_cta') ?>
        </a>
      </div>
      <div>
        <img src="<?= BASE_PATH ?>/assets/images/warsztaty-photo.jpg"
             alt="Pracownia ceramiczna Unique Ceramics"
             style="border-radius:12px;object-fit:cover;width:100%;height:360px">
      </div>
    </div>
  </div>
</section>

<!-- Workshop types -->
<section class="section" style="background:var(--sand)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Rodzaje warsztatów' : 'Workshop types' ?></h2>
    <div class="workshop-cards">
      <?php
      $workshops = $isPl ? [
        ['🎂', 'Warsztaty urodzinowe', 'Wyjątkowe urodziny w towarzystwie gliny! Idealne dla grup od 4 osób. Tworzysz, śmiejesz się i wychodzisz z własnoręcznym prezentem.', 'od 80 z&#322; / os.'],
        ['💍', 'Wieczory panieńskie', 'Niezapomniane wieczory panieńskie z ceramiką. Oryginalna alternatywa dla standardowych imprez. Możliwość degustacji wina.', 'od 100 z&#322; / os.'],
        ['🏢', 'Team Building', 'Integracja przez ceramikę dla firm i grup zawodowych. Budujecie coś razem — w przenośni i dosłownie. Oferta grupowa.', 'wycena indywidualna'],
        ['🌿', 'Warsztaty otwarte', 'Regularne warsztaty dla osób indywidualnych. Nauka podstaw toczenia i ręcznego formowania gliny.', 'od 90 z&#322; / os.'],
        ['🎁', 'Vouchery prezentowe', 'Podaruj komuś wyjątkowe doświadczenie! Vouchery na warsztaty dostępne w różnych nominałach.', 'od 80 z&#322;'],
        ['👨‍👩‍👧', 'Dla dzieci i rodzin', 'Warsztaty ceramiczne dla dzieci od 8 lat i całych rodzin. Bezpieczna glina, mnóstwo zabawy i kreatywności.', 'od 60 z&#322; / os.'],
      ] : [
        ['🎂', 'Birthday workshops', 'Unique birthday with clay! Perfect for groups from 4 people. You create, laugh, and leave with a handmade gift.', 'from 80 PLN / person'],
        ['💍', "Hen parties", 'Unforgettable hen parties with ceramics. An original alternative to standard parties. Wine tasting available.', 'from 100 PLN / person'],
        ['🏢', 'Team Building', 'Integration through ceramics for companies and professional groups. Build something together — figuratively and literally.', 'custom pricing'],
        ['🌿', 'Open workshops', 'Regular workshops for individuals. Learn the basics of wheel throwing and hand-forming.', 'from 90 PLN / person'],
        ['🎁', 'Gift vouchers', 'Give someone a unique experience! Workshop vouchers available in various amounts.', 'from 80 PLN'],
        ['👨‍👩‍👧', 'For children & families', 'Ceramic workshops for children from age 8 and whole families. Safe clay, lots of fun and creativity.', 'from 60 PLN / person'],
      ];
      foreach ($workshops as [$icon, $title, $desc, $price]): ?>
        <div class="workshop-card">
          <div class="workshop-card-icon"><?= $icon ?></div>
          <h3><?= $title ?></h3>
          <p><?= $desc ?></p>
          <div class="workshop-price"><?= $price ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- What's included -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Co zawiera warsztat?' : "What's included?" ?></h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-top:1.5rem">
      <?php
      $includes = $isPl ? [
        '🏺 Materiały (glina, narzędzia)',
        '👩‍🏫 Prowadzenie przez ceramiczkę',
        '🔥 Wypalanie Twoich prac',
        '📦 Gotowe wyroby do odbioru',
        '📸 Pamiątkowe zdjęcia',
        '☕ Napoje podczas warsztatów',
      ] : [
        '🏺 Materials (clay, tools)',
        '👩‍🏫 Guidance by a ceramicist',
        '🔥 Firing of your pieces',
        '📦 Finished pieces to collect',
        '📸 Souvenir photos',
        '☕ Drinks during the workshop',
      ];
      foreach ($includes as $item): ?>
        <div style="background:var(--sand);border-radius:8px;padding:.9rem 1rem;font-size:.9rem;font-weight:600">
          <?= $item ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Booking CTA -->
<section class="section-sm" style="background:var(--cream)">
  <div class="container">
    <div class="workshop-cta">
      <div>
        <h2><?= t('workshops.book_cta') ?></h2>
        <p><?= t('workshops.contact_for_booking') ?></p>
        <div style="margin-top:.8rem;display:flex;gap:1rem;flex-wrap:wrap">
          <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>" style="color:var(--white);font-weight:700;font-size:1.1rem">
            <i class="fas fa-phone-alt"></i> <?= SITE_PHONE ?>
          </a>
          <a href="<?= SITE_INSTAGRAM ?>" target="_blank" rel="noopener" style="color:rgba(255,255,255,.8)">
            <i class="fab fa-instagram"></i> Instagram DM
          </a>
        </div>
      </div>
      <a href="<?= url('contact.php') ?>" class="btn btn-outline">
        <i class="fas fa-envelope"></i> <?= $isPl ? 'Napisz do nas' : 'Write to us' ?>
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
