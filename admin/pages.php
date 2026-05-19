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

// ---- Regulamin defaults (PHP constants substituted) ----
$_re  = SITE_EMAIL;
$_rp  = SITE_PHONE;
$_rpc = str_replace(' ', '', SITE_PHONE);
$_sc  = number_format(SHIPPING_COST, 2, ',', ' ') . ' zł';
$_sf  = number_format(SHIPPING_FREE_FROM, 2, ',', ' ') . ' zł';
$default_reg_pl = "<h2>I. Postanowienia ogólne</h2>
<ol>
<li>Sklep internetowy dostępny pod adresem <strong>uniqueceramics.pl</strong> prowadzony jest przez osobę fizyczną działającą pod marką <strong>Unique Ceramics</strong>.</li>
<li>Kontakt ze sprzedawcą: e-mail <a href=\"mailto:{$_re}\">{$_re}</a>, telefon <a href=\"tel:{$_rpc}\">{$_rp}</a>.</li>
<li>Wszystkie produkty oferowane w sklepie są wykonywane ręcznie. Każdy egzemplarz jest niepowtarzalny i może nieznacznie różnić się od zdjęcia prezentowanego w sklepie — jest to naturalna cecha ceramiki rzemieślniczej, a nie wada towaru.</li>
<li>Pęknięcia szkliwa (crackle) są naturalnym efektem wypalania ceramiki i nie stanowią wady fizycznej produktu.</li>
<li>Klientem może być wyłącznie osoba pełnoletnia, posiadająca pełną zdolność do czynności prawnych.</li>
<li>Ceny podane w sklepie są cenami brutto (zawierają podatek VAT) i nie obejmują kosztów dostawy.</li>
<li>Do korzystania ze sklepu niezbędne jest urządzenie z dostępem do internetu oraz przeglądarka internetowa.</li>
</ol>
<h2>II. Zamówienia</h2>
<ol>
<li>Zamówienia można składać poprzez formularz na stronie lub drogą e-mailową pod adresem {$_re}.</li>
<li>Po złożeniu zamówienia Klient otrzymuje e-mail z potwierdzeniem przyjęcia zamówienia do realizacji.</li>
<li>Umowa sprzedaży zostaje zawarta z chwilą potwierdzenia zamówienia przez sprzedawcę.</li>
<li>Realizacja zamówienia na produkty dostępne od ręki rozpoczyna się po zaksięgowaniu płatności — zazwyczaj w ciągu 3–5 dni roboczych.</li>
<li>Zamówienia indywidualne (na specjalne zamówienie) mogą wymagać czasu realizacji do 4 tygodni — termin ustalany jest indywidualnie.</li>
<li>Sprzedawca zastrzega sobie prawo do odmowy realizacji zamówienia w uzasadnionych przypadkach, informując Klienta e-mailem.</li>
</ol>
<h2>III. Płatność i dostawa</h2>
<ol>
<li>Dostępna forma płatności: <strong>przelew bankowy</strong>. Dane do przelewu przesyłane są e-mailem po złożeniu zamówienia. Płatność powinna zostać zrealizowana w ciągu 7 dni.</li>
<li>Wysyłka realizowana jest za pośrednictwem wybranego przewoźnika (kurier lub Poczta Polska).</li>
<li>Koszt dostawy wynosi <strong>{$_sc}</strong>. Przy zamówieniach o wartości {$_sf} i powyżej dostawa jest <strong>bezpłatna</strong>.</li>
<li>Czas dostawy po wysłaniu przesyłki wynosi zazwyczaj 1–3 dni robocze na terenie Polski.</li>
<li>Sprzedawca nie ponosi odpowiedzialności za opóźnienia wynikające z działania przewoźnika.</li>
<li>Do każdego zamówienia dołączany jest paragon lub faktura (na życzenie).</li>
</ol>
<h2>IV. Reklamacje</h2>
<ol>
<li>Sprzedawca odpowiada wobec Klienta będącego konsumentem z tytułu rękojmi za wady fizyczne i prawne zakupionego towaru.</li>
<li>Reklamację należy zgłosić e-mailem na adres <a href=\"mailto:{$_re}\">{$_re}</a>, opisując wadę i dołączając zdjęcia.</li>
<li>Sprzedawca rozpatruje reklamację w terminie 14 dni kalendarzowych od jej otrzymania.</li>
<li>W przypadku uwzględnienia reklamacji Klient może żądać naprawy towaru, wymiany na nowy, obniżenia ceny albo odstąpienia od umowy.</li>
<li>Różnice w wyglądzie wynikające z ręcznego wykonania, naturalnych właściwości szkliwa (np. crackle) oraz nierówności faktury nie stanowią wady towaru.</li>
<li>Klient będący konsumentem ma prawo skorzystać z pozasądowych sposobów rozpatrywania reklamacji, w tym platformy ODR: <a href=\"https://ec.europa.eu/consumers/odr\" target=\"_blank\" rel=\"noopener\">ec.europa.eu/consumers/odr</a>.</li>
</ol>
<h2>V. Prawo odstąpienia od umowy</h2>
<ol>
<li>Konsument ma prawo odstąpić od umowy zawartej na odległość bez podania przyczyny w terminie <strong>14 dni</strong> od dnia otrzymania towaru.</li>
<li>Aby skorzystać z prawa odstąpienia, należy poinformować sprzedawcę e-mailem na adres <a href=\"mailto:{$_re}\">{$_re}</a> przed upływem terminu.</li>
<li>Po otrzymaniu oświadczenia o odstąpieniu sprzedawca niezwłocznie potwierdzi jego przyjęcie.</li>
<li>Konsument zobowiązany jest odesłać towar w terminie 14 dni od dnia odstąpienia. Bezpośrednie koszty zwrotu towaru ponosi Konsument.</li>
<li>Zwrot płatności nastąpi niezwłocznie, nie później niż w terminie 14 dni od dnia otrzymania zwrotu towaru, przelewem na wskazany rachunek bankowy.</li>
<li>Prawo odstąpienia nie przysługuje w odniesieniu do produktów wykonanych na specjalne zamówienie Klienta (zamówienia indywidualne).</li>
<li>Prawo odstąpienia od umowy nie przysługuje podmiotom nabywającym towar w związku z prowadzoną działalnością gospodarczą.</li>
</ol>
<h2>VI. Ochrona danych osobowych i pliki cookie</h2>
<ol>
<li>Administratorem danych osobowych Klientów jest właściciel sklepu Unique Ceramics. Dane przetwarzane są wyłącznie w celu realizacji zamówień i kontaktu z Klientem.</li>
<li>Podanie danych osobowych jest dobrowolne, lecz niezbędne do realizacji zamówienia.</li>
<li>Klientowi przysługuje prawo dostępu do swoich danych, ich poprawiania oraz usunięcia. W tym celu należy napisać na adres <a href=\"mailto:{$_re}\">{$_re}</a>.</li>
<li>Dane nie są udostępniane osobom trzecim, z wyjątkiem przewoźnika w zakresie niezbędnym do realizacji dostawy.</li>
<li>Strona wykorzystuje pliki cookie niezbędne do prawidłowego działania koszyka oraz pliki analityczne. Korzystanie ze strony bez zmiany ustawień przeglądarki oznacza zgodę na stosowanie plików cookie.</li>
</ol>
<h2>VII. Postanowienia końcowe</h2>
<ol>
<li>Sprzedawca zastrzega sobie prawo do zmiany Regulaminu. Zmiany nie dotyczą zamówień złożonych przed datą zmiany.</li>
<li>W sprawach nieuregulowanych niniejszym Regulaminem zastosowanie mają przepisy prawa polskiego, w szczególności Kodeksu Cywilnego oraz Ustawy o prawach konsumenta.</li>
<li>Ewentualne spory rozstrzygane są przez właściwy sąd powszechny. Konsument może skorzystać z pozasądowych sposobów rozstrzygania sporów.</li>
</ol>";
$default_reg_en = "<h2>I. General Provisions</h2>
<ol>
<li>The online store available at <strong>uniqueceramics.pl</strong> is operated by an individual trading under the brand name <strong>Unique Ceramics</strong>.</li>
<li>Contact: e-mail <a href=\"mailto:{$_re}\">{$_re}</a>, phone <a href=\"tel:{$_rpc}\">{$_rp}</a>.</li>
<li>All products are handmade. Each piece is unique and may differ slightly from the product photo — this is a natural characteristic of artisan ceramics, not a defect.</li>
<li>Glaze cracking (crackle) is a natural result of the firing process and does not constitute a physical defect.</li>
<li>Customers must be adults with full legal capacity.</li>
<li>Prices shown are gross prices (VAT-inclusive) and do not include shipping costs.</li>
</ol>
<h2>II. Orders</h2>
<ol>
<li>Orders can be placed via the website form or by e-mail at {$_re}.</li>
<li>After placing an order, the Customer receives a confirmation e-mail.</li>
<li>The sales contract is formed upon order confirmation by the seller.</li>
<li>In-stock orders are dispatched within 3–5 business days after payment is confirmed.</li>
<li>Custom orders may require up to 4 weeks — the timeline is agreed individually.</li>
<li>The seller reserves the right to refuse an order in justified cases, notifying the Customer by e-mail.</li>
</ol>
<h2>III. Payment &amp; Delivery</h2>
<ol>
<li>Payment method: <strong>bank transfer</strong>. Bank details are sent by e-mail after the order is placed. Payment must be made within 7 days.</li>
<li>Shipping is handled by a selected carrier (courier or Polish Post).</li>
<li>Shipping costs <strong>{$_sc}</strong>. Orders of {$_sf} or more qualify for <strong>free shipping</strong>.</li>
<li>Delivery within Poland typically takes 1–3 business days after dispatch.</li>
<li>The seller is not liable for delays caused by the carrier.</li>
<li>A receipt or VAT invoice (on request) is included with every order.</li>
</ol>
<h2>IV. Complaints</h2>
<ol>
<li>The seller is liable to consumers under the statutory warranty for physical and legal defects.</li>
<li>Complaints should be submitted by e-mail to <a href=\"mailto:{$_re}\">{$_re}</a>, describing the defect and attaching photos.</li>
<li>The seller will respond within 14 calendar days of receiving the complaint.</li>
<li>If a complaint is upheld, the Customer may request repair, replacement, a price reduction, or cancellation of the contract.</li>
<li>Differences in appearance resulting from handcrafting, natural glaze properties (e.g. crackle), and surface texture variations do not constitute defects.</li>
<li>Consumers may use out-of-court dispute resolution, including the EU ODR platform: <a href=\"https://ec.europa.eu/consumers/odr\" target=\"_blank\" rel=\"noopener\">ec.europa.eu/consumers/odr</a>.</li>
</ol>
<h2>V. Right of Withdrawal</h2>
<ol>
<li>Consumers have the right to withdraw from a distance contract without giving reasons within <strong>14 days</strong> of receiving the goods.</li>
<li>To exercise this right, notify the seller by e-mail at <a href=\"mailto:{$_re}\">{$_re}</a> before the deadline.</li>
<li>The seller will confirm receipt of the withdrawal notice promptly.</li>
<li>The Consumer must return the goods within 14 days of withdrawal. The direct cost of return shipping is borne by the Consumer.</li>
<li>Refunds will be issued no later than 14 days after receipt of the returned goods, by bank transfer.</li>
<li>The right of withdrawal does not apply to products made to the Customer's individual specifications.</li>
<li>The right of withdrawal does not apply to purchases made in connection with business activity.</li>
</ol>
<h2>VI. Data Protection &amp; Cookies</h2>
<ol>
<li>The data controller is the owner of Unique Ceramics. Personal data is processed solely for order fulfilment and customer communication.</li>
<li>Providing personal data is voluntary but necessary to process an order.</li>
<li>Customers have the right to access, correct, and delete their data by contacting <a href=\"mailto:{$_re}\">{$_re}</a>.</li>
<li>Data is not shared with third parties, except the carrier to the extent necessary for delivery.</li>
<li>The site uses cookies required for the shopping cart and analytics. Continued use of the site without changing browser settings implies consent to cookies.</li>
</ol>
<h2>VII. Final Provisions</h2>
<ol>
<li>The seller reserves the right to amend these Terms. Amendments do not affect orders placed before the amendment date.</li>
<li>Matters not covered by these Terms are governed by Polish law, in particular the Civil Code and the Consumer Rights Act.</li>
<li>Disputes are resolved by the competent Polish court. Consumers may also use out-of-court dispute resolution.</li>
</ol>";

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
    <div style="margin-bottom:.5rem"><input type="checkbox" id="sw_values" name="show_values" <?= $show_values === '1' ? 'checked' : '' ?>><label for="sw_values" style="cursor:pointer;margin-left:.45rem">Sekcja "Wartości"</label></div>
    <div style="margin-bottom:.5rem"><input type="checkbox" id="sw_process" name="show_process" <?= $show_process === '1' ? 'checked' : '' ?>><label for="sw_process" style="cursor:pointer;margin-left:.45rem">Sekcja "Jak powstaje ceramika?"</label></div>
    <div><input type="checkbox" id="sw_gallery" name="show_gallery" <?= $show_gallery === '1' ? 'checked' : '' ?>><label for="sw_gallery" style="cursor:pointer;margin-left:.45rem">Sekcja "Moje prace" (galeria)</label></div>
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
    <div style="margin-bottom:.5rem"><input type="checkbox" id="sw_ws_types" name="show_ws_types" <?= $show_ws_types === '1' ? 'checked' : '' ?>><label for="sw_ws_types" style="cursor:pointer;margin-left:.45rem">Sekcja "Rodzaje warsztatów"</label></div>
    <div><input type="checkbox" id="sw_ws_includes" name="show_ws_includes" <?= $show_ws_includes === '1' ? 'checked' : '' ?>><label for="sw_ws_includes" style="cursor:pointer;margin-left:.45rem">Sekcja "Co zawiera warsztat?"</label></div>
  </div>

  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz stronę "Warsztaty"</button>
</form>

<?php elseif ($tab === 'regulamin'): ?>
<!-- ===== REGULAMIN ===== -->
<?php
$reg_pl = get_setting('page_regulamin_pl') ?: $default_reg_pl;
$reg_en = get_setting('page_regulamin_en') ?: $default_reg_en;
?>
<form method="post" id="regulamin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="regulamin">
  <div class="card" style="margin-bottom:1.2rem">
    <div class="form-group" style="margin-bottom:1.4rem">
      <label style="font-size:.9rem;font-weight:700;margin-bottom:.5rem;display:block">Treść regulaminu (PL)</label>
      <textarea name="regulamin_pl" id="regulamin_pl" class="jodit-target"><?= h($reg_pl) ?></textarea>
    </div>
    <div class="form-group">
      <label style="font-size:.9rem;font-weight:700;margin-bottom:.5rem;display:block">Terms &amp; Conditions (EN)</label>
      <textarea name="regulamin_en" id="regulamin_en" class="jodit-target"><?= h($reg_en) ?></textarea>
    </div>
  </div>
  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Zapisz regulamin</button>
</form>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@3/build/jodit.min.css">
<script src="https://cdn.jsdelivr.net/npm/jodit@3/build/jodit.min.js"></script>
<script>
document.querySelectorAll('.jodit-target').forEach(function(el) {
  var editor = Jodit.make(el, {
    height: 560,
    language: 'pl',
    toolbarAdaptive: false,
    buttons: [
      'bold','italic','underline','strikethrough','|',
      'ul','ol','|',
      'fontsize','font','|',
      'paragraph','|',
      'left','center','right','justify','|',
      'link','|',
      'undo','redo','|',
      'fullsize','source'
    ],
    style: { font: '14px/1.6 Inter, Arial, sans-serif' }
  });
  el.closest('form').addEventListener('submit', function() {
    editor.synchronizeValues();
  });
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
