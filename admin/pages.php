<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_admin();

$pageTitle = 'Edytor stron';
$success   = false;
$errors    = [];

// ---- Default JSON data ----
$default_values = [
    ['icon'=>'🤍','title_pl'=>'Ręcznie wykonane','title_en'=>'Handmade','text_pl'=>'Każdy element formowany i zdobiony z dbałością o każdy detal','text_en'=>'Every element formed and decorated with attention to every detail'],
    ['icon'=>'✨','title_pl'=>'Unikatowe','title_en'=>'Unique','text_pl'=>'Żadne dwa egzemplarze nie są identyczne — każdy jest wyjątkowy','text_en'=>'No two pieces are identical — each one is unique'],
    ['icon'=>'🎁','title_pl'=>'Personalizacja','title_en'=>'Personalisation','text_pl'=>'Serduszka, dedykacje, własne wzory — ceramika szyta na miarę','text_en'=>'Hearts, dedications, custom patterns — ceramics tailored to you'],
    ['icon'=>'🚚','title_pl'=>'Szybka wysyłka','title_en'=>'Fast shipping','text_pl'=>'Staranne pakowanie i wysyłka w całej Polsce i za granicę','text_en'=>'Careful packaging and shipping across Poland and abroad'],
];
$default_process = [
    ['icon'=>'🏺','title_pl'=>'Formowanie','title_en'=>'Forming','text_pl'=>'Glina jest ręcznie formowana na kole lub w formie','text_en'=>'Clay is hand-formed on the wheel or in a mould'],
    ['icon'=>'🔥','title_pl'=>'Suszenie','title_en'=>'Drying','text_pl'=>'Produkt suszy się powoli, zachowując swój kształt','text_en'=>'The piece dries slowly, retaining its shape'],
    ['icon'=>'🎨','title_pl'=>'Szkliwienie','title_en'=>'Glazing','text_pl'=>'Nakładam szkliwo — każda sztuka inaczej','text_en'=>'I apply glaze — each piece differently'],
    ['icon'=>'♨️','title_pl'=>'Wypalanie','title_en'=>'Firing','text_pl'=>'Piec w temperaturze ~1200°C nadaje ceramice trwałość','text_en'=>'The kiln at ~1200°C gives the ceramics durability'],
    ['icon'=>'✨','title_pl'=>'Kontrola','title_en'=>'Quality check','text_pl'=>'Każdy produkt sprawdzam przed wysyłką','text_en'=>'I inspect every piece before shipping'],
];
$default_workshops = [
    ['icon'=>'🎂','title_pl'=>'Warsztaty urodzinowe','title_en'=>'Birthday workshops','desc_pl'=>'Wyjątkowe urodziny w towarzystwie gliny! Idealne dla grup od 4 osób. Tworzysz, śmiejesz się i wychodzisz z własnoręcznym prezentem.','desc_en'=>'Unique birthday with clay! Perfect for groups from 4 people. You create, laugh, and leave with a handmade gift.','price_pl'=>'od 80 zł / os.','price_en'=>'from 80 PLN / person'],
    ['icon'=>'💍','title_pl'=>'Wieczory panieńskie','title_en'=>'Hen parties','desc_pl'=>'Niezapomniane wieczory panieńskie z ceramiką. Oryginalna alternatywa dla standardowych imprez. Możliwość degustacji wina.','desc_en'=>'Unforgettable hen parties with ceramics. An original alternative to standard parties. Wine tasting available.','price_pl'=>'od 100 zł / os.','price_en'=>'from 100 PLN / person'],
    ['icon'=>'🏢','title_pl'=>'Team Building','title_en'=>'Team Building','desc_pl'=>'Integracja przez ceramikę dla firm i grup zawodowych. Budujecie coś razem — w przenośni i dosłownie.','desc_en'=>'Integration through ceramics for companies and professional groups. Build something together — figuratively and literally.','price_pl'=>'wycena indywidualna','price_en'=>'custom pricing'],
    ['icon'=>'🌿','title_pl'=>'Warsztaty otwarte','title_en'=>'Open workshops','desc_pl'=>'Regularne warsztaty dla osób indywidualnych. Nauka podstaw toczenia i ręcznego formowania gliny.','desc_en'=>'Regular workshops for individuals. Learn the basics of wheel throwing and hand-forming.','price_pl'=>'od 90 zł / os.','price_en'=>'from 90 PLN / person'],
    ['icon'=>'🎁','title_pl'=>'Vouchery prezentowe','title_en'=>'Gift vouchers','desc_pl'=>'Podaruj komuś wyjątkowe doświadczenie! Vouchery na warsztaty dostępne w różnych nominałach.','desc_en'=>'Give someone a unique experience! Workshop vouchers available in various amounts.','price_pl'=>'od 80 zł','price_en'=>'from 80 PLN'],
    ['icon'=>'👨‍👩‍👧','title_pl'=>'Dla dzieci i rodzin','title_en'=>'For children & families','desc_pl'=>'Warsztaty ceramiczne dla dzieci od 8 lat i całych rodzin. Bezpieczna glina, mnóstwo zabawy i kreatywności.','desc_en'=>'Ceramic workshops for children from age 8 and whole families. Safe clay, lots of fun and creativity.','price_pl'=>'od 60 zł / os.','price_en'=>'from 60 PLN / person'],
];
$default_includes_pl = "🏺 Materiały (glina, narzędzia)\n👩‍🏫 Prowadzenie przez ceramiczkę\n🔥 Wypalanie Twoich prac\n📦 Gotowe wyroby do odbioru\n📸 Pamiątkowe zdjęcia\n☕ Napoje podczas warsztatów";
$default_includes_en = "🏺 Materials (clay, tools)\n👩‍🏫 Guidance by a ceramicist\n🔥 Firing of your pieces\n📦 Finished pieces to collect\n📸 Souvenir photos\n☕ Drinks during the workshop";

// ---- POST handler ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'about') {
        // Photo upload
        $photo = handle_image_upload('about_photo', 'pages/');
        if ($photo) set_setting('page_about_photo', $photo);

        set_setting('page_about_quote',      trim($_POST['about_quote']      ?? ''));
        set_setting('page_about_story_pl',   trim($_POST['about_story_pl']   ?? ''));
        set_setting('page_about_story_en',   trim($_POST['about_story_en']   ?? ''));
        set_setting('page_about_mission_pl', trim($_POST['about_mission_pl'] ?? ''));
        set_setting('page_about_mission_en', trim($_POST['about_mission_en'] ?? ''));

        // Values JSON
        $vals = [];
        foreach (($_POST['val_icon'] ?? []) as $i => $icon) {
            $vals[] = ['icon'=>$icon,'title_pl'=>$_POST['val_title_pl'][$i]??'','title_en'=>$_POST['val_title_en'][$i]??'','text_pl'=>$_POST['val_text_pl'][$i]??'','text_en'=>$_POST['val_text_en'][$i]??''];
        }
        set_setting('page_about_values', json_encode($vals, JSON_UNESCAPED_UNICODE));

        // Process JSON
        $procs = [];
        foreach (($_POST['proc_icon'] ?? []) as $i => $icon) {
            $procs[] = ['icon'=>$icon,'title_pl'=>$_POST['proc_title_pl'][$i]??'','title_en'=>$_POST['proc_title_en'][$i]??'','text_pl'=>$_POST['proc_text_pl'][$i]??'','text_en'=>$_POST['proc_text_en'][$i]??''];
        }
        set_setting('page_about_process', json_encode($procs, JSON_UNESCAPED_UNICODE));

        set_setting('page_about_show_values',  isset($_POST['show_values'])  ? '1' : '0');
        set_setting('page_about_show_process', isset($_POST['show_process']) ? '1' : '0');
        set_setting('page_about_show_gallery', isset($_POST['show_gallery']) ? '1' : '0');

        $success = 'Strona "O mnie" zapisana.';

    } elseif ($action === 'workshops') {
        $photo = handle_image_upload('workshops_photo', 'pages/');
        if ($photo) set_setting('page_workshops_photo', $photo);

        set_setting('page_workshops_intro1_pl', trim($_POST['intro1_pl'] ?? ''));
        set_setting('page_workshops_intro1_en', trim($_POST['intro1_en'] ?? ''));
        set_setting('page_workshops_intro2_pl', trim($_POST['intro2_pl'] ?? ''));
        set_setting('page_workshops_intro2_en', trim($_POST['intro2_en'] ?? ''));

        // Workshop types JSON
        $types = [];
        foreach (($_POST['ws_icon'] ?? []) as $i => $icon) {
            $types[] = ['icon'=>$icon,'title_pl'=>$_POST['ws_title_pl'][$i]??'','title_en'=>$_POST['ws_title_en'][$i]??'','desc_pl'=>$_POST['ws_desc_pl'][$i]??'','desc_en'=>$_POST['ws_desc_en'][$i]??'','price_pl'=>$_POST['ws_price_pl'][$i]??'','price_en'=>$_POST['ws_price_en'][$i]??''];
        }
        set_setting('page_workshops_types', json_encode($types, JSON_UNESCAPED_UNICODE));

        set_setting('page_workshops_includes_pl', trim($_POST['includes_pl'] ?? ''));
        set_setting('page_workshops_includes_en', trim($_POST['includes_en'] ?? ''));
        set_setting('page_workshops_show_types',    isset($_POST['show_ws_types'])    ? '1' : '0');
        set_setting('page_workshops_show_includes', isset($_POST['show_ws_includes']) ? '1' : '0');

        $success = 'Strona "Warsztaty" zapisana.';

    } elseif ($action === 'regulamin') {
        set_setting('page_regulamin_pl', trim($_POST['regulamin_pl'] ?? ''));
        set_setting('page_regulamin_en', trim($_POST['regulamin_en'] ?? ''));
        $success = 'Regulamin zapisany.';
    }
}

// ---- Load current values ----
$tab = $_GET['tab'] ?? 'about';

// About
$about_photo      = get_setting('page_about_photo');
$about_quote      = get_setting('page_about_quote', '"Ręcznie tworzone z sercem"');
$about_story_pl   = get_setting('page_about_story_pl',   'Od 20 lat zajmuję się ceramiką w obszarze przemysłu, dlatego moje doświadczenie przeniosłam na ceramikę artystyczną, którą zajmuję się od około roku. Tworzenie unikatowych prac stało się dla mnie prawdziwą pasją i sposobem na wyrażanie kreatywności. W tym czasie stworzyłam własną, kameralną pracownię, w której powstają ręcznie wykonywane przedmioty użytkowe i dekoracyjne. Swoją inspirację czerpię przede wszystkim z prostych form oraz rzemiosła artystycznego.');
$about_story_en   = get_setting('page_about_story_en',   'For 20 years I have been working with ceramics in industry, and I have now brought that experience to artistic ceramics, which I have been pursuing for about a year. Creating unique pieces has become my true passion and a way to express creativity. I have set up my own small studio where I make handmade functional and decorative items, drawing inspiration from simple forms and artisan craftsmanship.');
$about_mission_pl = get_setting('page_about_mission_pl', 'Każdą pracę wykonuję samodzielnie, dbając o detale, estetykę i niepowtarzalny charakter wyrobów. Ceramika daje mi ogromną satysfakcję oraz pozwala odnaleźć wewnętrzny spokój i chwilę wyciszenia w tym jakże zabieganym świecie.');
$about_mission_en = get_setting('page_about_mission_en', 'I make every piece myself, paying attention to detail, aesthetics, and the unique character of each work. Ceramics gives me great satisfaction and allows me to find inner peace and a moment of quiet in this busy world.');
$values_raw  = get_setting('page_about_values');
$values      = $values_raw  ? json_decode($values_raw,  true) : $default_values;
$process_raw = get_setting('page_about_process');
$process     = $process_raw ? json_decode($process_raw, true) : $default_process;
$show_values  = get_setting('page_about_show_values',  '1');
$show_process = get_setting('page_about_show_process', '1');
$show_gallery = get_setting('page_about_show_gallery', '1');

// Workshops
$ws_photo      = get_setting('page_workshops_photo');
$intro1_pl     = get_setting('page_workshops_intro1_pl', 'Organizujemy warsztaty ceramiczne dla grup i indywidualnych uczestników. Idealne na urodziny, wieczory panieńskie, imprezy firmowe czy po prostu wyjątkowy wieczór z przyjaciółmi. Nie potrzebujesz żadnego doświadczenia — wszystkiego nauczymy Cię od podstaw.');
$intro1_en     = get_setting('page_workshops_intro1_en', 'We organise ceramic workshops for groups and individuals. Perfect for birthdays, hen parties, corporate events, or simply a special evening with friends. No experience needed — we\'ll teach you everything from scratch.');
$intro2_pl     = get_setting('page_workshops_intro2_pl', 'W trakcie warsztatu uformujecie własne wyroby z gliny, które po wypaleniu możecie odebrać lub wysłać pocztą. Każdy uczestnik wychodzi z wyjątkowym, własnoręcznie wykonanym dziełem.');
$intro2_en     = get_setting('page_workshops_intro2_en', 'During the workshop you\'ll shape your own clay pieces, which after firing you can pick up or have shipped. Every participant leaves with a unique, hand-made piece.');
$ws_types_raw  = get_setting('page_workshops_types');
$ws_types      = $ws_types_raw ? json_decode($ws_types_raw, true) : $default_workshops;
$includes_pl   = get_setting('page_workshops_includes_pl', $default_includes_pl);
$includes_en   = get_setting('page_workshops_includes_en', $default_includes_en);
$show_ws_types    = get_setting('page_workshops_show_types',    '1');
$show_ws_includes = get_setting('page_workshops_show_includes', '1');

require __DIR__ . '/includes/admin-header.php';
?>

<div class="page-header"><h1><?= $pageTitle ?></h1></div>

<?php if ($success): ?>
  <div class="alert alert-success"><i class="fas fa-check"></i> <?= h($success) ?></div>
<?php endif; ?>

<div class="admin-tabs">
  <a href="?tab=about"      class="admin-tab <?= $tab === 'about'      ? 'active' : '' ?>"><i class="fas fa-user"></i> O mnie</a>
  <a href="?tab=workshops"  class="admin-tab <?= $tab === 'workshops'  ? 'active' : '' ?>"><i class="fas fa-hands"></i> Warsztaty</a>
  <a href="?tab=regulamin"  class="admin-tab <?= $tab === 'regulamin'  ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Regulamin</a>
</div>

<?php if ($tab === 'about'): ?>
<!-- ===== O MNIE ===== -->
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="about">

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Zdjęcie</div>
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
      <?php
      $photoSrc = $about_photo ? upload_url($about_photo) : BASE_PATH . '/assets/images/about-photo.jpg';
      ?>
      <img src="<?= h($photoSrc) ?>" alt="" style="width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
      <div>
        <input type="file" name="about_photo" accept="image/jpeg,image/png,image/webp">
        <div class="form-hint">Obecne zdjęcie: <code><?= $about_photo ?: 'assets/images/about-photo.jpg' ?></code></div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Treść strony</div>
    <div class="form-group">
      <label>Cytat hero (PL)</label>
      <input type="text" name="about_quote" value="<?= h($about_quote) ?>">
    </div>
    <div class="form-grid">
      <div class="form-group">
        <label>Historia (PL)</label>
        <textarea name="about_story_pl" rows="5"><?= h($about_story_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Historia (EN)</label>
        <textarea name="about_story_en" rows="5"><?= h($about_story_en) ?></textarea>
      </div>
      <div class="form-group">
        <label>Misja / drugi akapit (PL)</label>
        <textarea name="about_mission_pl" rows="4"><?= h($about_mission_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Misja / drugi akapit (EN)</label>
        <textarea name="about_mission_en" rows="4"><?= h($about_mission_en) ?></textarea>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Wartości (4 kafelki) <small style="font-weight:400;color:var(--stone)">— widoczne też na stronie głównej</small></div>
    <?php foreach ($values as $i => $v): ?>
    <div style="background:var(--sand);border-radius:8px;padding:1rem;margin-bottom:.8rem">
      <div style="font-weight:600;margin-bottom:.6rem;color:var(--stone);font-size:.85rem">Wartość <?= $i+1 ?></div>
      <div style="display:grid;grid-template-columns:60px 1fr 1fr;gap:.6rem;margin-bottom:.5rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Ikona</label>
          <input type="text" name="val_icon[]" value="<?= h($v['icon']) ?>" style="text-align:center;font-size:1.2rem">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł PL</label>
          <input type="text" name="val_title_pl[]" value="<?= h($v['title_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł EN</label>
          <input type="text" name="val_title_en[]" value="<?= h($v['title_en']) ?>">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis PL</label>
          <input type="text" name="val_text_pl[]" value="<?= h($v['text_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis EN</label>
          <input type="text" name="val_text_en[]" value="<?= h($v['text_en']) ?>">
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Jak powstaje ceramika? (5 kroków)</div>
    <?php foreach ($process as $i => $p): ?>
    <div style="background:var(--sand);border-radius:8px;padding:1rem;margin-bottom:.8rem">
      <div style="font-weight:600;margin-bottom:.6rem;color:var(--stone);font-size:.85rem">Krok <?= $i+1 ?></div>
      <div style="display:grid;grid-template-columns:60px 1fr 1fr;gap:.6rem;margin-bottom:.5rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Ikona</label>
          <input type="text" name="proc_icon[]" value="<?= h($p['icon']) ?>" style="text-align:center;font-size:1.2rem">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł PL</label>
          <input type="text" name="proc_title_pl[]" value="<?= h($p['title_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł EN</label>
          <input type="text" name="proc_title_en[]" value="<?= h($p['title_en']) ?>">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis PL</label>
          <input type="text" name="proc_text_pl[]" value="<?= h($p['text_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis EN</label>
          <input type="text" name="proc_text_en[]" value="<?= h($p['text_en']) ?>">
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Widoczność sekcji</div>
    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" name="show_values"  <?= $show_values  === '1' ? 'checked' : '' ?>> Sekcja "Wartości"
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" name="show_process" <?= $show_process === '1' ? 'checked' : '' ?>> Sekcja "Jak powstaje ceramika?"
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
      <input type="checkbox" name="show_gallery" <?= $show_gallery === '1' ? 'checked' : '' ?>> Sekcja "Moje prace" (galeria)
    </label>
  </div>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz stronę "O mnie"</button>
</form>

<?php elseif ($tab === 'workshops'): ?>
<!-- ===== WARSZTATY ===== -->
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="workshops">

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Zdjęcie</div>
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
      <?php $wsPhotoSrc = $ws_photo ? upload_url($ws_photo) : BASE_PATH . '/assets/images/warsztaty-photo.jpg'; ?>
      <img src="<?= h($wsPhotoSrc) ?>" alt="" style="width:150px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
      <div>
        <input type="file" name="workshops_photo" accept="image/jpeg,image/png,image/webp">
        <div class="form-hint">Obecne zdjęcie: <code><?= $ws_photo ?: 'assets/images/warsztaty-photo.jpg' ?></code></div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Treść intro</div>
    <div class="form-grid">
      <div class="form-group">
        <label>Akapit 1 (PL)</label>
        <textarea name="intro1_pl" rows="4"><?= h($intro1_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Akapit 1 (EN)</label>
        <textarea name="intro1_en" rows="4"><?= h($intro1_en) ?></textarea>
      </div>
      <div class="form-group">
        <label>Akapit 2 (PL)</label>
        <textarea name="intro2_pl" rows="3"><?= h($intro2_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Akapit 2 (EN)</label>
        <textarea name="intro2_en" rows="3"><?= h($intro2_en) ?></textarea>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Rodzaje warsztatów (6 kart)</div>
    <?php foreach ($ws_types as $i => $w): ?>
    <div style="background:var(--sand);border-radius:8px;padding:1rem;margin-bottom:.8rem">
      <div style="font-weight:600;margin-bottom:.6rem;color:var(--stone);font-size:.85rem">Warsztat <?= $i+1 ?></div>
      <div style="display:grid;grid-template-columns:60px 1fr 1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Ikona</label>
          <input type="text" name="ws_icon[]" value="<?= h($w['icon']) ?>" style="text-align:center;font-size:1.2rem">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł PL</label>
          <input type="text" name="ws_title_pl[]" value="<?= h($w['title_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Tytuł EN</label>
          <input type="text" name="ws_title_en[]" value="<?= h($w['title_en']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Cena PL</label>
          <input type="text" name="ws_price_pl[]" value="<?= h($w['price_pl']) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Cena EN</label>
          <input type="text" name="ws_price_en[]" value="<?= h($w['price_en']) ?>">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis PL</label>
          <textarea name="ws_desc_pl[]" rows="2" style="font-size:.82rem"><?= h($w['desc_pl']) ?></textarea>
        </div>
        <div class="form-group" style="margin:0">
          <label style="font-size:.75rem">Opis EN</label>
          <textarea name="ws_desc_en[]" rows="2" style="font-size:.82rem"><?= h($w['desc_en']) ?></textarea>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Co zawiera warsztat? <small style="font-weight:400;color:var(--stone)">(jedna pozycja na linię)</small></div>
    <div class="form-grid">
      <div class="form-group">
        <label>Lista PL</label>
        <textarea name="includes_pl" rows="7"><?= h($includes_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Lista EN</label>
        <textarea name="includes_en" rows="7"><?= h($includes_en) ?></textarea>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-section-title">Widoczność sekcji</div>
    <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" name="show_ws_types"    <?= $show_ws_types    === '1' ? 'checked' : '' ?>> Sekcja "Rodzaje warsztatów"
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
      <input type="checkbox" name="show_ws_includes" <?= $show_ws_includes === '1' ? 'checked' : '' ?>> Sekcja "Co zawiera warsztat?"
    </label>
  </div>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz stronę "Warsztaty"</button>
</form>

<?php elseif ($tab === 'regulamin'): ?>
<!-- ===== REGULAMIN ===== -->
<?php
$reg_pl = get_setting('page_regulamin_pl');
$reg_en = get_setting('page_regulamin_en');
?>
<div style="background:rgba(212,168,67,.1);border:1px solid rgba(212,168,67,.3);border-radius:8px;padding:.9rem 1rem;margin-bottom:1rem;font-size:.88rem">
  <i class="fas fa-info-circle" style="color:var(--warning)"></i>
  Treść regulaminu jest zapisywana jako HTML. Możesz używać tagów jak <code>&lt;strong&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;h2&gt;</code>.
  Jeśli pola są puste, wyświetlana jest domyślna treść z pliku <code>regulamin.php</code>.
</div>
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="regulamin">
  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-grid">
      <div class="form-group">
        <label>Treść regulaminu (PL) — HTML</label>
        <textarea name="regulamin_pl" rows="30" style="font-family:monospace;font-size:.82rem"><?= h($reg_pl) ?></textarea>
      </div>
      <div class="form-group">
        <label>Terms & Conditions (EN) — HTML</label>
        <textarea name="regulamin_en" rows="30" style="font-family:monospace;font-size:.82rem"><?= h($reg_en) ?></textarea>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz regulamin</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
