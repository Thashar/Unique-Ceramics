<?php
// ============================================================
// UNIQUE CERAMICS — INSTALACJA BAZY DANYCH
// Uruchom ten plik JEDEN raz: http://twoja-domena.pl/install.php
// Następnie USUŃ lub ZABEZPIECZ ten plik (np. przenieś poza wwwroot)
// ============================================================

define('INSTALL_MODE', true);

// Basic security — require a token or local access
if (!isset($_GET['token']) || $_GET['token'] !== 'UniqueInstall2024') {
    die('<h2>Dostęp zabroniony.</h2><p>Użyj: install.php?token=UniqueInstall2024</p>');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$log = [];

function run(string $sql): void {
    global $log;
    try {
        db()->exec($sql);
        $log[] = ['ok', substr(trim($sql), 0, 80)];
    } catch (PDOException $e) {
        $log[] = ['err', $e->getMessage() . ' | SQL: ' . substr(trim($sql), 0, 80)];
    }
}

// ---- CREATE TABLES ----
run("CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_pl VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL DEFAULT '',
    slug VARCHAR(100) NOT NULL UNIQUE,
    description_pl TEXT DEFAULT '',
    description_en TEXT DEFAULT '',
    image VARCHAR(255) DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    active INTEGER DEFAULT 1
)");

run("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER REFERENCES categories(id),
    name_pl VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) DEFAULT '',
    slug VARCHAR(255) NOT NULL UNIQUE,
    description_pl TEXT DEFAULT '',
    description_en TEXT DEFAULT '',
    price REAL NOT NULL DEFAULT 0,
    price_before REAL DEFAULT NULL,
    stock INTEGER DEFAULT 0,
    sku VARCHAR(100) DEFAULT '',
    images TEXT DEFAULT '[]',
    featured INTEGER DEFAULT 0,
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

run("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT '',
    shipping_address TEXT NOT NULL DEFAULT '{}',
    items TEXT NOT NULL DEFAULT '[]',
    subtotal REAL NOT NULL DEFAULT 0,
    shipping_cost REAL DEFAULT 0,
    total REAL NOT NULL DEFAULT 0,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'transfer',
    payment_status VARCHAR(50) DEFAULT 'pending',
    order_status VARCHAR(50) DEFAULT 'new',
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

run("CREATE TABLE IF NOT EXISTS custom_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT '',
    product_type VARCHAR(100) DEFAULT '',
    quantity INTEGER DEFAULT 1,
    color_preference TEXT DEFAULT '',
    pattern_preference TEXT DEFAULT '',
    dedication TEXT DEFAULT '',
    description TEXT DEFAULT '',
    budget_range VARCHAR(100) DEFAULT '',
    deadline DATE DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'new',
    admin_notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

run("CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT '',
    email VARCHAR(255) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

run("CREATE TABLE IF NOT EXISTS settings (
    key_name VARCHAR(100) PRIMARY KEY,
    value TEXT DEFAULT ''
)");

// ---- INSERT DEFAULT ADMIN ----
$adminPass  = 'ceramics2024';
$adminHash  = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
$existing   = db_fetch('SELECT id FROM admins WHERE username = ?', ['admin']);
if (!$existing) {
    run("INSERT INTO admins (username, password_hash, name, email) VALUES ('admin', '$adminHash', 'Administrator', '" . SITE_EMAIL . "')");
    $log[] = ['ok', "Admin utworzony: login=admin, hasło={$adminPass}"];
}

// ---- INSERT DEFAULT SETTINGS ----
$defaults = [
    'bank_account'        => '',
    'bank_name'           => '',
    'shipping_cost'       => SHIPPING_COST,
    'shipping_free_from'  => SHIPPING_FREE_FROM,
    'site_email'          => SITE_EMAIL,
    'site_phone'          => SITE_PHONE,
];
foreach ($defaults as $k => $v) {
    $exists = db_fetch('SELECT key_name FROM settings WHERE key_name = ?', [$k]);
    if (!$exists) {
        try {
            $stmt = db()->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
            $stmt->execute([$k, (string)$v]);
            $log[] = ['ok', "Ustawienie: $k = $v"];
        } catch (PDOException $e) {
            $log[] = ['err', $e->getMessage()];
        }
    }
}

// ---- INSERT DEFAULT CATEGORIES ----
$cats = [
    ['Kubki espresso',     'Espresso cups',    'kubki-espresso',     1, 'products/IMG-20260515-WA0033.jpg'],
    ['Zestawy kawowe',     'Coffee sets',      'zestawy-kawowe',     2, 'products/IMG-20260515-WA0032.jpg'],
    ['Talerze i miski',    'Plates & Bowls',   'talerze-i-miski',    3, 'products/IMG-20260515-WA0034.jpg'],
    ['Świeczniki',         'Candle holders',   'swieczniki',         4, 'products/IMG-20260515-WA0039.jpg'],
    ['Dzbanki',            'Pitchers',         'dzbanki',            5, 'products/IMG-20260515-WA0048.jpg'],
    ['Zestawy prezentowe', 'Gift sets',        'zestawy-prezentowe', 6, 'products/IMG-20260515-WA0048.jpg'],
];
foreach ($cats as [$pl, $en, $slug, $order, $img]) {
    $exists = db_fetch('SELECT id FROM categories WHERE slug = ?', [$slug]);
    if (!$exists) {
        db_insert('categories', ['name_pl' => $pl, 'name_en' => $en, 'slug' => $slug, 'sort_order' => $order, 'image' => $img, 'active' => 1]);
        $log[] = ['ok', "Kategoria dodana: $pl"];
    }
}

// ---- INSERT SAMPLE PRODUCTS ----
$catEspresso = db_fetch('SELECT id FROM categories WHERE slug = ?', ['kubki-espresso']);
$catSets     = db_fetch('SELECT id FROM categories WHERE slug = ?', ['zestawy-kawowe']);
$catPlates   = db_fetch('SELECT id FROM categories WHERE slug = ?', ['talerze-i-miski']);
$catCandles  = db_fetch('SELECT id FROM categories WHERE slug = ?', ['swieczniki']);
$catGifts    = db_fetch('SELECT id FROM categories WHERE slug = ?', ['zestawy-prezentowe']);

$sampleProducts = [
    // ---- Kubki espresso ----
    [
        'name_pl'        => 'Kubek espresso różowy z serduszkiem',
        'name_en'        => 'Pink espresso cup with heart',
        'slug'           => 'kubek-espresso-rozowy-serduszko',
        'description_pl' => "Ręcznie uformowany kubek espresso w delikatnym różowym kolorze z uroczym motywem serduszka. Idealny na kawę lub herbatę.\n\nKażdy kubek jest wyjątkowy — może się nieznacznie różnić od zdjęcia, co jest naturalną cechą ceramiki rzemieślniczej.",
        'description_en' => "Hand-formed espresso cup in a delicate pink colour with a charming heart motif. Perfect for coffee or tea.\n\nEach cup is unique — it may differ slightly from the photo, which is a natural feature of artisan ceramics.",
        'price'          => 89.00,
        'stock'          => 5,
        'category_id'    => $catEspresso['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0033.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Kubki espresso szaro-niebieskie (para)',
        'name_en'        => 'Grey-blue espresso cups (pair)',
        'slug'           => 'kubki-espresso-szaro-niebieskie-para',
        'description_pl' => "Para ręcznie robionych kubków espresso w szaro-niebieskich odcieniach. Szkliwo spływa w dół tworząc niepowtarzalny wzór na każdym kubku. Idealne dla dwojga.",
        'description_en' => "A pair of handmade espresso cups in grey-blue shades. The glaze flows downwards creating a unique pattern on each cup. Perfect for two.",
        'price'          => 159.00,
        'stock'          => 3,
        'category_id'    => $catEspresso['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0041.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Kubki espresso zielone z kwiatkiem (seria)',
        'name_en'        => 'Green flower espresso cups (series)',
        'slug'           => 'kubki-espresso-zielone-kwiatek-seria',
        'description_pl' => "Seria trzech ręcznie robionych kubków espresso w stonowanej zieleni z uroczym motywem kwiatka w reliefie. Każdy kubek lekko się różni — to magia ceramiki artystycznej.",
        'description_en' => "Series of three handmade espresso cups in muted green with a charming raised flower motif. Each cup differs slightly — the magic of artisan ceramics.",
        'price'          => 79.00,
        'stock'          => 3,
        'category_id'    => $catEspresso['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0042.jpg']),
        'featured'       => 0,
    ],
    [
        'name_pl'        => 'Kubek espresso Kopernik',
        'name_en'        => 'Copernicus espresso cup',
        'slug'           => 'kubek-espresso-kopernik',
        'description_pl' => "Wyjątkowy kubek espresso z ręcznie rytym portretem Mikołaja Kopernika. Niebieskie wnętrze i artystyczny rysunek czynią go idealnym prezentem dla miłośnika historii i kawy.",
        'description_en' => "Unique espresso cup with a hand-engraved portrait of Nicolaus Copernicus. Blue interior and artistic drawing make it a perfect gift for history and coffee lovers.",
        'price'          => 119.00,
        'stock'          => 4,
        'category_id'    => $catEspresso['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0046.jpg', 'products/IMG-20260515-WA0044.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Kubek espresso różowy z serduszkami (spodek)',
        'name_en'        => 'Pink espresso cup with hearts and saucer',
        'slug'           => 'kubek-espresso-rozowy-serduszka-spodek',
        'description_pl' => "Intensywnie różowy kubek espresso na pasującym spodku z motywem serc. Ciepły, romantyczny kolor i ręczne wykonanie sprawiają, że każdy poranek nabiera wyjątkowego charakteru.",
        'description_en' => "Vibrant pink espresso cup on a matching saucer with heart motifs. Warm, romantic colour and handmade craftsmanship make every morning special.",
        'price'          => 99.00,
        'stock'          => 4,
        'category_id'    => $catEspresso['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0047.jpg']),
        'featured'       => 0,
    ],
    // ---- Zestawy kawowe ----
    [
        'name_pl'        => 'Zestaw kawowy zielony z kwiatkiem',
        'name_en'        => 'Green coffee set with flower',
        'slug'           => 'zestaw-kawowy-zielony-kwiatek',
        'description_pl' => "Kompletny zestaw kawowy: kubek espresso + podstawek w uspokajającej zielonej barwie z uroczym motywem kwiatka. Doskonały prezent lub dekoracja stołu.",
        'description_en' => "Complete coffee set: espresso cup + saucer in a calming green colour with a charming flower motif. Perfect gift or table decoration.",
        'price'          => 149.00,
        'stock'          => 3,
        'category_id'    => $catSets['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0043.jpg', 'products/IMG-20260515-WA0032.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Zestaw kawowy miodowy',
        'name_en'        => 'Honey coffee set',
        'slug'           => 'zestaw-kawowy-miodowy',
        'description_pl' => "Elegancki zestaw: kubek espresso w ciepłym, miodowym kolorze z naturalnym surowym spodkiem. Minimalistyczny design i rzemieślnicze wykonanie.",
        'description_en' => "Elegant set: espresso cup in warm honey colour with a natural raw saucer. Minimalist design and artisan craftsmanship.",
        'price'          => 159.00,
        'stock'          => 4,
        'category_id'    => $catSets['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0035.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Zestaw kawowy szaro-niebieski',
        'name_en'        => 'Grey-blue coffee set',
        'slug'           => 'zestaw-kawowy-szaro-niebieski',
        'description_pl' => "Zestaw kawowy w dramatycznych szaro-niebieskich odcieniach. Szkliwo spływa w dół tworząc niepowtarzalny wzór. Każdy zestaw jest absolutnie wyjątkowy.",
        'description_en' => "Coffee set in dramatic grey-blue shades. The glaze flows downwards creating a unique pattern. Each set is absolutely one of a kind.",
        'price'          => 169.00,
        'stock'          => 2,
        'category_id'    => $catSets['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0040.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Zestaw kawowy Kopernik',
        'name_en'        => 'Copernicus coffee set',
        'slug'           => 'zestaw-kawowy-kopernik',
        'description_pl' => "Artystyczny zestaw kawowy z ręcznie rytym portretem Mikołaja Kopernika. Kremowe tło, niebieskie wnętrze i unikalny rysunek — wyjątkowy prezent dla miłośnika historii.",
        'description_en' => "Artistic coffee set with hand-engraved portrait of Nicolaus Copernicus. Cream background, blue interior and unique drawing — a one-of-a-kind gift for history lovers.",
        'price'          => 189.00,
        'stock'          => 3,
        'category_id'    => $catSets['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0044.jpg', 'products/IMG-20260515-WA0045.jpg']),
        'featured'       => 1,
    ],
    // ---- Talerze i miski ----
    [
        'name_pl'        => 'Miska ceramiczna niebieska',
        'name_en'        => 'Blue ceramic bowl',
        'slug'           => 'miska-ceramiczna-niebieska',
        'description_pl' => "Piękna miska ceramiczna z niebieskim wnętrzem i naturalnym zewnętrzem. Idealna do serwowania zup, sałatek czy jako ozdoba.",
        'description_en' => "Beautiful ceramic bowl with blue interior and natural exterior. Perfect for serving soups, salads, or as decoration.",
        'price'          => 129.00,
        'stock'          => 6,
        'category_id'    => $catPlates['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0031.jpg']),
        'featured'       => 0,
    ],
    [
        'name_pl'        => 'Zestaw talerzy z miseczkami',
        'name_en'        => 'Plate and bowl set',
        'slug'           => 'zestaw-talerzy-miseczkami',
        'description_pl' => "Kompletny zestaw dla dwóch osób: dwa owalne talerze i dwie miseczki w niebiesko-naturalnym połączeniu. Idealne na śniadanie, sushi lub tapas.",
        'description_en' => "Complete set for two: two oval plates and two small bowls in blue-natural combination. Perfect for breakfast, sushi or tapas.",
        'price'          => 279.00,
        'stock'          => 2,
        'category_id'    => $catPlates['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0034.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Miska ceramiczna granatowa',
        'name_en'        => 'Navy ceramic bowl',
        'slug'           => 'miska-ceramiczna-granatowa',
        'description_pl' => "Głęboka miska ceramiczna w intensywnym granatowym kolorze z naturalnym surowym spodkiem. Efektowna i funkcjonalna — idealna na zupę, makaron lub sałatkę.",
        'description_en' => "Deep ceramic bowl in intense navy colour with a natural raw base. Striking and functional — perfect for soup, pasta or salad.",
        'price'          => 139.00,
        'stock'          => 4,
        'category_id'    => $catPlates['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0037.jpg']),
        'featured'       => 0,
    ],
    [
        'name_pl'        => 'Podkładki ceramiczne komplet (4 szt.)',
        'name_en'        => 'Ceramic coasters set of 4',
        'slug'           => 'podkladki-ceramiczne-komplet-4',
        'description_pl' => "Komplet czterech ceramicznych podkładek z wytłaczanym wzorem liścia w czterech kolorach: niebieski, kremowy, pomarańczowy i szaro-niebieski. Oryginalna dekoracja i ochrona stołu.",
        'description_en' => "Set of four ceramic coasters with embossed leaf pattern in four colours: blue, cream, orange and grey-blue. Original decoration and table protection.",
        'price'          => 149.00,
        'stock'          => 5,
        'category_id'    => $catPlates['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0036.jpg']),
        'featured'       => 1,
    ],
    // ---- Świeczniki ----
    [
        'name_pl'        => 'Świeczniki ażurowe gwiazdkowe (3 szt.)',
        'name_en'        => 'Openwork star candle holders (set of 3)',
        'slug'           => 'swieczniki-azurowe-gwiazdkowe-komplet',
        'description_pl' => "Zestaw trzech ażurowych świeczników ceramicznych z wyciętymi gwiazdkami, księżycami i kwiatkami. W szarobrązowym, różowym i czarnym kolorze. Przepięknie oświetlają każde wnętrze.",
        'description_en' => "Set of three openwork ceramic candle holders with cut-out stars, moons and flowers. In grey-brown, pink and black. They beautifully illuminate any interior.",
        'price'          => 189.00,
        'stock'          => 3,
        'category_id'    => $catCandles['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0039.jpg']),
        'featured'       => 1,
    ],
    [
        'name_pl'        => 'Świeczniki słoneczne (komplet 4 szt.)',
        'name_en'        => 'Sun candle holders (set of 4)',
        'slug'           => 'swieczniki-sloneczne-komplet',
        'description_pl' => "Komplet czterech ceramicznych świeczników ze świecami sojowymi i tłoczonym motywem słońca. Ciemne szkliwo z naturalnym surowym dołem nadaje im rustykalny, elegancki charakter.",
        'description_en' => "Set of four ceramic candle holders with soy candles and embossed sun motif. Dark glaze with natural raw base gives them a rustic, elegant character.",
        'price'          => 229.00,
        'stock'          => 2,
        'category_id'    => $catCandles['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0038.jpg']),
        'featured'       => 1,
    ],
    // ---- Zestawy prezentowe ----
    [
        'name_pl'        => 'Zestaw prezentowy Unique Ceramics',
        'name_en'        => 'Unique Ceramics gift set',
        'slug'           => 'zestaw-prezentowy-unique-ceramics',
        'description_pl' => "Starannie dobrany zestaw prezentowy z kolekcji Unique Ceramics: miska, talerz, kubki espresso i świeczniki. Każdy element ręcznie wykonany — idealny prezent na każdą okazję.",
        'description_en' => "Carefully selected gift set from the Unique Ceramics collection: bowl, plate, espresso cups and candle holders. Each piece handmade — a perfect gift for any occasion.",
        'price'          => 349.00,
        'stock'          => 2,
        'category_id'    => $catGifts['id'] ?? null,
        'images'         => json_encode(['products/IMG-20260515-WA0048.jpg']),
        'featured'       => 1,
    ],
];

foreach ($sampleProducts as $prod) {
    $exists = db_fetch('SELECT id FROM products WHERE slug = ?', [$prod['slug']]);
    if (!$exists) {
        $prodData = array_merge($prod, ['active' => 1, 'created_at' => date('Y-m-d H:i:s')]);
        if ($prodData['price_before'] ?? null === null) unset($prodData['price_before']);
        db_insert('products', $prodData);
        $log[] = ['ok', 'Produkt dodany: ' . $prod['name_pl']];
    }
}

// Create uploads directories
$dirs = [UPLOAD_DIR, UPLOAD_DIR . 'products/', UPLOAD_DIR . 'categories/'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        $log[] = ['ok', "Katalog utworzony: $dir"];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Instalacja — <?= SITE_NAME ?></title>
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:system-ui,sans-serif; background:#f4f1ee; padding:2rem; }
    .box { max-width:700px; margin:0 auto; background:#fff; border-radius:12px; padding:2rem; box-shadow:0 4px 20px rgba(0,0,0,.1); }
    h1 { font-size:1.4rem; margin-bottom:1.2rem; color:#8B6F5E; }
    .log-item { display:flex; gap:.7rem; padding:.4rem .6rem; border-radius:6px; margin-bottom:.3rem; font-size:.85rem; }
    .ok  { background:rgba(90,138,90,.1); }
    .err { background:rgba(192,57,43,.1); color:#c0392b; }
    .ok  .icon::before  { content: '✓'; color:#5A8A5A; font-weight:700; }
    .err .icon::before  { content: '✗'; color:#c0392b; font-weight:700; }
    .next-steps { background:#FAF7F2; border-radius:8px; padding:1.2rem; margin-top:1.5rem; }
    .next-steps h2 { font-size:1rem; margin-bottom:.8rem; }
    .next-steps ol { margin-left:1.2rem; font-size:.9rem; line-height:1.8; }
    .btn { display:inline-block; padding:.6rem 1.4rem; background:#C4714B; color:#fff; border-radius:999px; font-weight:700; text-decoration:none; margin-top:1rem; }
    .btn-outline { background:transparent; border:2px solid #8B6F5E; color:#8B6F5E; margin-left:.5rem; }
  </style>
</head>
<body>
<div class="box">
  <h1>🏺 Instalacja <?= SITE_NAME ?> — zakończona</h1>

  <?php foreach ($log as [$type, $msg]): ?>
    <div class="log-item <?= $type ?>">
      <span class="icon"></span>
      <span><?= h($msg) ?></span>
    </div>
  <?php endforeach; ?>

  <div class="next-steps">
    <h2>📋 Następne kroki:</h2>
    <ol>
      <li><strong>Zaloguj się do panelu admina:</strong> login = <code>admin</code>, hasło = <code>ceramics2024</code></li>
      <li><strong>ZMIEŃ HASŁO</strong> w Ustawienia → Hasło</li>
      <li>Uzupełnij dane do przelewu w Ustawienia → Ogólne</li>
      <li>Skonfiguruj bramki płatnicze w Ustawienia → Płatności</li>
      <li>Skopiuj zdjęcia produktów do folderu <code>/uploads/products/</code></li>
      <li><strong>USUŃ lub zabezpiecz plik install.php!</strong></li>
    </ol>
  </div>

  <div>
    <a href="<?= url('admin/login.php') ?>" class="btn">→ Przejdź do panelu admina</a>
    <a href="<?= url('index.php') ?>" class="btn btn-outline">← Strona główna</a>
  </div>
</div>
</body>
</html>
