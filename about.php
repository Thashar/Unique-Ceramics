<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'O mnie' : 'About me';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div style="background:var(--sand);padding:4rem 0;text-align:center">
  <div class="container-sm">
    <h1><?= t('about.title') ?></h1>
    <p style="color:var(--stone);font-size:1.15rem;margin-top:.8rem;font-style:italic">"Ręcznie tworzone z sercem"</p>
  </div>
</div>

<!-- About photo full-width -->
<div style="background:var(--white);padding-top:2.5rem">
  <div class="container">
    <img src="<?= BASE_PATH ?>/assets/images/about-photo.jpg"
         alt="Unique Ceramics — ceramika na wystawie"
         style="width:100%;max-height:480px;object-fit:cover;border-radius:var(--radius-lg)"
         onerror="this.style.display='none'">
  </div>
</div>

<!-- Story -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <div class="about-section">
      <div>
        <h2><?= current_lang() === 'pl' ? 'Moja historia' : 'My story' ?></h2>
        <p style="color:var(--stone);line-height:1.8;margin-top:1rem;margin-bottom:1rem"><?= t('about.story') ?></p>
        <p style="color:var(--stone);line-height:1.8"><?= t('about.mission') ?></p>
        <a href="<?= url('custom-order.php') ?>" class="btn btn-primary" style="margin-top:1.5rem">
          <i class="fas fa-paint-brush"></i>
          <?= current_lang() === 'pl' ? 'Zamów swoją ceramikę' : 'Order your ceramics' ?>
        </a>
      </div>
      <div class="about-images">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260515-WA0033.jpg" alt="Ceramiczny kubek"
             onerror="this.style.display='none'">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260515-WA0040.jpg" alt="Zestaw ceramiczny"
             onerror="this.style.display='none'">
        <img src="<?= BASE_PATH ?>/uploads/products/IMG-20260515-WA0043.jpg" alt="Warsztaty ceramiczne"
             onerror="this.style.display='none'">
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <h2 class="section-title"><?= t('about.values_title') ?></h2>
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

<!-- Process -->
<section class="section" style="background:var(--white)">
  <div class="container">
    <h2 class="section-title"><?= current_lang() === 'pl' ? 'Jak powstaje ceramika?' : 'How is ceramics made?' ?></h2>
    <p class="section-sub"><?= current_lang() === 'pl' ? 'Każdy produkt przechodzi przez moje ręce kilka razy' : 'Every product passes through my hands several times' ?></p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;margin-top:1rem">
      <?php
      $steps = current_lang() === 'pl'
        ? [
            ['🏺', 'Formowanie', 'Glina jest ręcznie formowana na kole lub w formie'],
            ['🔥', 'Suszenie', 'Produkt suszy się powoli, zachowując swój kształt'],
            ['🎨', 'Szkliwienie', 'Nakładam szkliwo — każda sztuka inaczej'],
            ['♨️', 'Wypalanie', 'Piec w temperaturze ~1200°C nadaje ceramice trwałość'],
            ['✨', 'Kontrola', 'Każdy produkt sprawdzam przed wysyłką'],
          ]
        : [
            ['🏺', 'Forming', 'Clay is hand-formed on the wheel or in a mould'],
            ['🔥', 'Drying', 'The piece dries slowly, retaining its shape'],
            ['🎨', 'Glazing', 'I apply glaze — each piece differently'],
            ['♨️', 'Firing', 'The kiln at ~1200°C gives the ceramics durability'],
            ['✨', 'Quality check', 'I inspect every piece before shipping'],
          ];
      foreach ($steps as $i => [$icon, $title, $text]):
      ?>
        <div style="text-align:center;position:relative">
          <?php if ($i < count($steps) - 1): ?>
            <div style="position:absolute;top:24px;right:-1rem;font-size:1.2rem;color:var(--border)">→</div>
          <?php endif; ?>
          <div style="font-size:2rem;margin-bottom:.6rem"><?= $icon ?></div>
          <h4 style="margin-bottom:.3rem"><?= $title ?></h4>
          <p style="font-size:.85rem;color:var(--stone)"><?= $text ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Gallery -->
<section class="section-sm" style="background:var(--sand)">
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
