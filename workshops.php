<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Warsztaty ceramiczne' : 'Ceramic Workshops';
require_once __DIR__ . '/includes/header.php';

$isPl = current_lang() === 'pl';

// ---- Content from settings ----
$ws_photo   = get_setting('page_workshops_photo');
$intro1     = $isPl ? get_setting('page_workshops_intro1_pl', '') : get_setting('page_workshops_intro1_en', '');
$intro2     = $isPl ? get_setting('page_workshops_intro2_pl', '') : get_setting('page_workshops_intro2_en', '');
$ws_types_raw = get_setting('page_workshops_types');
$ws_types_db  = $ws_types_raw ? json_decode($ws_types_raw, true) : null;
$inc_raw      = $isPl ? get_setting('page_workshops_includes_pl', '') : get_setting('page_workshops_includes_en', '');
$show_types    = get_setting('page_workshops_show_types',    '1') === '1';
$show_includes = get_setting('page_workshops_show_includes', '1') === '1';
?>

<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--sand) 0%,var(--cream) 100%);padding:5rem 0;text-align:center">
  <div class="container-sm">
    <h1><?= t('workshops.title') ?></h1>
    <p style="color:var(--stone);font-size:1.15rem;margin-top:.8rem"><?= t('workshops.subtitle') ?></p>
  </div>
</div>

<!-- Intro -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Odkryj radość lepienia!' : 'Discover the joy of pottery!' ?></h2>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center">
      <div>
        <?php
        $default_intro1 = $isPl
            ? 'Organizujemy warsztaty ceramiczne dla grup i indywidualnych uczestników. Idealne na urodziny, wieczory panieńskie, imprezy firmowe czy po prostu wyjątkowy wieczór z przyjaciółmi. Nie potrzebujesz żadnego doświadczenia — wszystkiego nauczymy Cię od podstaw.'
            : 'We organise ceramic workshops for groups and individuals. Perfect for birthdays, hen parties, corporate events, or simply a special evening with friends. No experience needed — we\'ll teach you everything from scratch.';
        $default_intro2 = $isPl
            ? 'W trakcie warsztatu uformujecie własne wyroby z gliny, które po wypaleniu możecie odebrać lub wysłać pocztą. Każdy uczestnik wychodzi z wyjątkowym, własnoręcznie wykonanym dziełem.'
            : 'During the workshop you\'ll shape your own clay pieces, which after firing you can pick up or have shipped. Every participant leaves with a unique, hand-made piece.';
        ?>
        <p style="color:var(--stone);line-height:1.8;margin-top:1rem"><?= nl2br(h($intro1 ?: $default_intro1)) ?></p>
        <p style="color:var(--stone);line-height:1.8"><?= nl2br(h($intro2 ?: $default_intro2)) ?></p>
        <a href="<?= url('contact.php') ?>" class="btn btn-primary" style="margin-top:1.2rem">
          <i class="fas fa-calendar-check"></i> <?= t('workshops.book_cta') ?>
        </a>
      </div>
      <div>
        <?php $wsPhotoSrc = $ws_photo ? upload_url($ws_photo) : BASE_PATH . '/assets/images/warsztaty-photo.jpg'; ?>
        <img src="<?= h($wsPhotoSrc) ?>"
             alt="Pracownia ceramiczna Unique Ceramics"
             style="border-radius:12px;object-fit:cover;width:100%;height:360px">
      </div>
    </div>
  </div>
</section>

<?php if ($show_types): ?>
<!-- Workshop types -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Rodzaje warsztatów' : 'Workshop types' ?></h2>
    <div class="workshop-cards">
      <?php
      $ws_default = $isPl ? [
        ['icon'=>'🎂','title_pl'=>'Warsztaty urodzinowe','desc_pl'=>'Wyjątkowe urodziny w towarzystwie gliny! Idealne dla grup od 4 osób.','price_pl'=>'od 80 zł / os.'],
        ['icon'=>'💍','title_pl'=>'Wieczory panieńskie','desc_pl'=>'Niezapomniane wieczory panieńskie z ceramiką. Możliwość degustacji wina.','price_pl'=>'od 100 zł / os.'],
        ['icon'=>'🏢','title_pl'=>'Team Building','desc_pl'=>'Integracja przez ceramikę dla firm i grup zawodowych.','price_pl'=>'wycena indywidualna'],
        ['icon'=>'🌿','title_pl'=>'Warsztaty otwarte','desc_pl'=>'Regularne warsztaty dla osób indywidualnych.','price_pl'=>'od 90 zł / os.'],
        ['icon'=>'🎁','title_pl'=>'Vouchery prezentowe','desc_pl'=>'Podaruj komuś wyjątkowe doświadczenie!','price_pl'=>'od 80 zł'],
        ['icon'=>'👨‍👩‍👧','title_pl'=>'Dla dzieci i rodzin','desc_pl'=>'Warsztaty dla dzieci od 8 lat i całych rodzin.','price_pl'=>'od 60 zł / os.'],
      ] : [
        ['icon'=>'🎂','title_en'=>'Birthday workshops','desc_en'=>'Unique birthday with clay! Perfect for groups from 4 people.','price_en'=>'from 80 PLN / person'],
        ['icon'=>'💍','title_en'=>'Hen parties','desc_en'=>'Unforgettable hen parties with ceramics. Wine tasting available.','price_en'=>'from 100 PLN / person'],
        ['icon'=>'🏢','title_en'=>'Team Building','desc_en'=>'Integration through ceramics for companies and professional groups.','price_en'=>'custom pricing'],
        ['icon'=>'🌿','title_en'=>'Open workshops','desc_en'=>'Regular workshops for individuals. Learn the basics.','price_en'=>'from 90 PLN / person'],
        ['icon'=>'🎁','title_en'=>'Gift vouchers','desc_en'=>'Give someone a unique experience!','price_en'=>'from 80 PLN'],
        ['icon'=>'👨‍👩‍👧','title_en'=>'For children & families','desc_en'=>'Workshops for children from age 8 and whole families.','price_en'=>'from 60 PLN / person'],
      ];
      $workshops = $ws_types_db ?? $ws_default;
      foreach ($workshops as $w):
        $wIcon  = $w['icon'] ?? '';
        $wTitle = $isPl ? ($w['title_pl'] ?? '') : ($w['title_en'] ?? $w['title_pl'] ?? '');
        $wDesc  = $isPl ? ($w['desc_pl']  ?? '') : ($w['desc_en']  ?? $w['desc_pl']  ?? '');
        $wPrice = $isPl ? ($w['price_pl'] ?? '') : ($w['price_en'] ?? $w['price_pl'] ?? '');
      ?>
        <div class="workshop-card">
          <div class="workshop-card-icon"><?= h($wIcon) ?></div>
          <h3><?= h($wTitle) ?></h3>
          <p><?= h($wDesc) ?></p>
          <div class="workshop-price"><?= h($wPrice) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_includes): ?>
<!-- What's included -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Co zawiera warsztat?' : "What's included?" ?></h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-top:1.5rem">
      <?php
      $default_inc = $isPl
        ? ["🏺 Materiały (glina, narzędzia)","👩‍🏫 Prowadzenie przez ceramiczkę","🔥 Wypalanie Twoich prac","📦 Gotowe wyroby do odbioru","📸 Pamiątkowe zdjęcia","☕ Napoje podczas warsztatów"]
        : ["🏺 Materials (clay, tools)","👩‍🏫 Guidance by a ceramicist","🔥 Firing of your pieces","📦 Finished pieces to collect","📸 Souvenir photos","☕ Drinks during the workshop"];
      $inc_items = $inc_raw ? array_filter(array_map('trim', explode("\n", $inc_raw))) : $default_inc;
      foreach ($inc_items as $item): ?>
        <div style="background:var(--sand);border-radius:8px;padding:.9rem 1rem;font-size:.9rem;font-weight:600">
          <?= h($item) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

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
