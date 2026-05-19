<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'O mnie' : 'About me';
require_once __DIR__ . '/includes/header.php';

// ---- Page content from settings (with fallbacks) ----
$isPl         = current_lang() === 'pl';
$about_photo  = get_setting('page_about_photo');
$about_quote  = get_setting('page_about_quote', '"Ręcznie tworzone z sercem"');
$story        = $isPl
    ? get_setting('page_about_story_pl',   t('about.story'))
    : get_setting('page_about_story_en',   'For 20 years I have been working with ceramics in industry, and I have now brought that experience to artistic ceramics.');
$mission      = $isPl
    ? get_setting('page_about_mission_pl', t('about.mission'))
    : get_setting('page_about_mission_en', 'I make every piece myself, paying attention to detail, aesthetics, and the unique character of each work.');
$values_raw   = get_setting('page_about_values');
$values_db    = $values_raw  ? json_decode($values_raw, true)  : null;
$process_raw  = get_setting('page_about_process');
$process_db   = $process_raw ? json_decode($process_raw, true) : null;
$show_values  = get_setting('page_about_show_values',  '1') === '1';
$show_process = get_setting('page_about_show_process', '1') === '1';
$show_gallery = get_setting('page_about_show_gallery', '1') === '1';
?>

<!-- Hero -->
<div style="background:var(--sand);padding:4rem 0;text-align:center">
  <div class="container-sm">
    <h1><?= t('about.title') ?></h1>
    <p style="color:var(--stone);font-size:1.15rem;margin-top:.8rem;font-style:italic"><?= h($about_quote) ?></p>
  </div>
</div>

<!-- Story -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Moja historia' : 'My story' ?></h2>
    <div class="about-section">
      <div>
        <p style="color:var(--stone);line-height:1.8;margin-bottom:1rem"><?= nl2br(h($story)) ?></p>
        <p style="color:var(--stone);line-height:1.8"><?= nl2br(h($mission)) ?></p>
        <a href="<?= url('custom-order.php') ?>" class="btn btn-primary" style="margin-top:1.5rem">
          <i class="fas fa-paint-brush"></i>
          <?= $isPl ? 'Zamów swoją ceramikę' : 'Order your ceramics' ?>
        </a>
      </div>
      <div style="display:flex;align-items:flex-start;justify-content:center">
        <?php $photoSrc = $about_photo ? upload_url($about_photo) : BASE_PATH . '/assets/images/about-photo.jpg'; ?>
        <img src="<?= h($photoSrc) ?>"
             alt="Unique Ceramics — ceramika na wystawie"
             style="width:100%;max-width:420px;border-radius:var(--radius-lg);object-fit:cover"
             onerror="this.style.display='none'">
      </div>
    </div>
  </div>
</section>

<?php if ($show_values): ?>
<!-- Values -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= t('about.values_title') ?></h2>
    <div class="values-grid">
      <?php
      $vals = $values_db ?? t('home.values');
      foreach ($vals as $val):
        $icon  = $val['icon'] ?? '';
        $title = $isPl ? ($val['title_pl'] ?? $val['title'] ?? '') : ($val['title_en'] ?? $val['title'] ?? '');
        $text  = $isPl ? ($val['text_pl']  ?? $val['text']  ?? '') : ($val['text_en']  ?? $val['text']  ?? '');
      ?>
        <div class="value-card">
          <div class="value-card-icon"><?= h($icon) ?></div>
          <h4><?= h($title) ?></h4>
          <p><?= h($text) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_process): ?>
<!-- Process -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= $isPl ? 'Jak powstaje ceramika?' : 'How is ceramics made?' ?></h2>
    <p class="section-sub"><?= $isPl ? 'Każdy produkt przechodzi przez moje ręce kilka razy' : 'Every product passes through my hands several times' ?></p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;margin-top:1rem">
      <?php
      $steps = $process_db ?? ($isPl
        ? [['icon'=>'🏺','title_pl'=>'Formowanie','text_pl'=>'Glina jest ręcznie formowana na kole lub w formie'],['icon'=>'🔥','title_pl'=>'Suszenie','text_pl'=>'Produkt suszy się powoli, zachowując swój kształt'],['icon'=>'🎨','title_pl'=>'Szkliwienie','text_pl'=>'Nakładam szkliwo — każda sztuka inaczej'],['icon'=>'♨️','title_pl'=>'Wypalanie','text_pl'=>'Piec w temperaturze ~1200°C nadaje ceramice trwałość'],['icon'=>'✨','title_pl'=>'Kontrola','text_pl'=>'Każdy produkt sprawdzam przed wysyłką']]
        : [['icon'=>'🏺','title_en'=>'Forming','text_en'=>'Clay is hand-formed on the wheel or in a mould'],['icon'=>'🔥','title_en'=>'Drying','text_en'=>'The piece dries slowly, retaining its shape'],['icon'=>'🎨','title_en'=>'Glazing','text_en'=>'I apply glaze — each piece differently'],['icon'=>'♨️','title_en'=>'Firing','text_en'=>'The kiln at ~1200°C gives the ceramics durability'],['icon'=>'✨','title_en'=>'Quality check','text_en'=>'I inspect every piece before shipping']]);
      foreach ($steps as $i => $step):
        $sIcon  = $step['icon'] ?? '';
        $sTitle = $isPl ? ($step['title_pl'] ?? '') : ($step['title_en'] ?? $step['title_pl'] ?? '');
        $sText  = $isPl ? ($step['text_pl']  ?? '') : ($step['text_en']  ?? $step['text_pl']  ?? '');
      ?>
        <div style="text-align:center;position:relative">
          <?php if ($i < count($steps) - 1): ?>
            <div style="position:absolute;top:24px;right:-1rem;font-size:1.2rem;color:var(--border)">→</div>
          <?php endif; ?>
          <div style="font-size:2rem;margin-bottom:.6rem"><?= h($sIcon) ?></div>
          <h4 style="margin-bottom:.3rem"><?= h($sTitle) ?></h4>
          <p style="font-size:.85rem;color:var(--stone)"><?= h($sText) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($show_gallery): ?>
<!-- Gallery -->
<section class="section-sm" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= current_lang() === 'pl' ? 'Moje prace' : 'My work' ?></h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.8rem;margin-top:1.5rem">
      <?php
      $galleryPhotos = [
        'IMG-20260515-WA0031.jpg','IMG-20260515-WA0032.jpg','IMG-20260515-WA0034.jpg',
        'IMG-20260515-WA0036.jpg','IMG-20260515-WA0037.jpg','IMG-20260515-WA0038.jpg',
        'IMG-20260515-WA0039.jpg','IMG-20260515-WA0041.jpg','IMG-20260515-WA0042.jpg',
      ];
      foreach ($galleryPhotos as $photo): ?>
        <div style="aspect-ratio:1;border-radius:12px;overflow:hidden;background:var(--border)">
          <img src="<?= BASE_PATH ?>/uploads/products/<?= $photo ?>"
               alt="Unique Ceramics"
               style="width:100%;height:100%;object-fit:cover;transition:transform .3s"
               onmouseover="this.style.transform='scale(1.06)'"
               onmouseout="this.style.transform=''"
               loading="lazy"
               onerror="this.parentElement.style.display='none'">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section-sm" style="background:var(--cream)">
  <div class="container">
    <div class="workshop-cta">
      <div>
        <h2><?= current_lang() === 'pl' ? 'Masz pytania?' : 'Have questions?' ?></h2>
        <p><?= current_lang() === 'pl' ? 'Chętnie opowiem Ci więcej o mojej ceramice i możliwościach personalizacji.' : 'I\'d love to tell you more about my ceramics and personalisation options.' ?></p>
      </div>
      <div style="display:flex;gap:.8rem;flex-wrap:wrap">
        <a href="<?= url('contact.php') ?>" class="btn btn-outline">
          <i class="fas fa-envelope"></i> <?= t('nav.contact') ?>
        </a>
        <a href="<?= url('shop.php') ?>" class="btn btn-primary">
          <i class="fas fa-shopping-bag"></i> <?= t('nav.shop') ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
