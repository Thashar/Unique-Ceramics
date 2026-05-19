<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = current_lang() === 'pl' ? 'Regulamin sklepu' : 'Terms & Conditions';
require_once __DIR__ . '/includes/header.php';
$isPl = current_lang() === 'pl';
$custom_content = $isPl ? get_setting('page_regulamin_pl') : get_setting('page_regulamin_en');
?>

<div style="background:var(--sand);padding:3rem 0;text-align:center">
  <div class="container-sm">
    <h1><?= $isPl ? 'Regulamin sklepu' : 'Terms &amp; Conditions' ?></h1>
    <p style="color:var(--stone);margin-top:.6rem">
      <?= $isPl ? 'Obowiązuje od 1 stycznia 2025 r.' : 'Effective from 1 January 2025' ?>
    </p>
  </div>
</div>

<div class="container section" style="max-width:860px">

<?php if ($custom_content): ?>
  <?= $custom_content ?>
<?php elseif ($isPl): ?>

  <h2>I. Postanowienia ogólne</h2>
  <ol>
    <li>Sklep internetowy dostępny pod adresem <strong>uniqueceramics.pl</strong> prowadzony jest przez osobę fizyczną działającą pod marką <strong>Unique Ceramics</strong>.</li>
    <li>Kontakt ze sprzedawcą: e-mail <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>, telefon <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a>.</li>
    <li>Wszystkie produkty oferowane w sklepie są wykonywane ręcznie. Każdy egzemplarz jest niepowtarzalny i może nieznacznie różnić się od zdjęcia prezentowanego w sklepie — jest to naturalna cecha ceramiki rzemieślniczej, a nie wada towaru.</li>
    <li>Pęknięcia szkliwa (crackle) są naturalnym efektem wypalania ceramiki i nie stanowią wady fizycznej produktu.</li>
    <li>Klientem może być wyłącznie osoba pełnoletnia, posiadająca pełną zdolność do czynności prawnych.</li>
    <li>Ceny podane w sklepie są cenami brutto (zawierają podatek VAT) i nie obejmują kosztów dostawy.</li>
    <li>Do korzystania ze sklepu niezbędne jest urządzenie z dostępem do internetu oraz przeglądarka internetowa.</li>
  </ol>

  <h2 style="margin-top:2rem">II. Zamówienia</h2>
  <ol>
    <li>Zamówienia można składać poprzez formularz na stronie lub drogą e-mailową pod adresem <?= SITE_EMAIL ?>.</li>
    <li>Po złożeniu zamówienia Klient otrzymuje e-mail z potwierdzeniem przyjęcia zamówienia do realizacji.</li>
    <li>Umowa sprzedaży zostaje zawarta z chwilą potwierdzenia zamówienia przez sprzedawcę.</li>
    <li>Realizacja zamówienia na produkty dostępne od ręki rozpoczyna się po zaksięgowaniu płatności — zazwyczaj w ciągu 3–5 dni roboczych.</li>
    <li>Zamówienia indywidualne (na specjalne zamówienie) mogą wymagać czasu realizacji do 4 tygodni — termin ustalany jest indywidualnie.</li>
    <li>Sprzedawca zastrzega sobie prawo do odmowy realizacji zamówienia w uzasadnionych przypadkach, informując Klienta e-mailem.</li>
  </ol>

  <h2 style="margin-top:2rem">III. Płatność i dostawa</h2>
  <ol>
    <li>Dostępna forma płatności: <strong>przelew bankowy</strong>. Dane do przelewu przesyłane są e-mailem po złożeniu zamówienia. Płatność powinna zostać zrealizowana w ciągu 7 dni od daty złożenia zamówienia.</li>
    <li>Wysyłka realizowana jest za pośrednictwem wybranego przewoźnika (kurier lub Poczta Polska).</li>
    <li>Koszt dostawy wynosi <strong><?= format_price(SHIPPING_COST) ?></strong>. Przy zamówieniach o wartości <?= format_price(SHIPPING_FREE_FROM) ?> i powyżej dostawa jest <strong>bezpłatna</strong>.</li>
    <li>Czas dostawy po wysłaniu przesyłki wynosi zazwyczaj 1–3 dni robocze na terenie Polski.</li>
    <li>Sprzedawca nie ponosi odpowiedzialności za opóźnienia wynikające z działania przewoźnika.</li>
    <li>Do każdego zamówienia dołączany jest paragon lub faktura (na życzenie).</li>
  </ol>

  <h2 style="margin-top:2rem">IV. Reklamacje</h2>
  <ol>
    <li>Sprzedawca odpowiada wobec Klienta będącego konsumentem z tytułu rękojmi za wady fizyczne i prawne zakupionego towaru.</li>
    <li>Reklamację należy zgłosić e-mailem na adres <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>, opisując wadę i dołączając zdjęcia.</li>
    <li>Sprzedawca rozpatruje reklamację w terminie 14 dni kalendarzowych od jej otrzymania.</li>
    <li>W przypadku uwzględnienia reklamacji Klient może żądać naprawy towaru, wymiany na nowy, obniżenia ceny albo odstąpienia od umowy.</li>
    <li>Różnice w wyglądzie wynikające z ręcznego wykonania, naturalnych właściwości szkliwa (np. crackle) oraz nierówności faktury nie stanowią wady towaru.</li>
    <li>Klient będący konsumentem ma prawo skorzystać z pozasądowych sposobów rozpatrywania reklamacji, w tym zwrócić się do Powiatowego Rzecznika Konsumentów lub skorzystać z platformy ODR: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</li>
  </ol>

  <h2 style="margin-top:2rem">V. Prawo odstąpienia od umowy</h2>
  <ol>
    <li>Konsument ma prawo odstąpić od umowy zawartej na odległość bez podania przyczyny w terminie <strong>14 dni</strong> od dnia otrzymania towaru.</li>
    <li>Aby skorzystać z prawa odstąpienia, należy poinformować sprzedawcę e-mailem na adres <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a> przed upływem terminu.</li>
    <li>Po otrzymaniu oświadczenia o odstąpieniu sprzedawca niezwłocznie potwierdzi jego przyjęcie.</li>
    <li>Konsument zobowiązany jest odesłać towar w terminie 14 dni od dnia odstąpienia. Bezpośrednie koszty zwrotu towaru ponosi Konsument.</li>
    <li>Zwrot płatności nastąpi niezwłocznie, nie później niż w terminie 14 dni od dnia otrzymania zwrotu towaru, przelewem na wskazany rachunek bankowy.</li>
    <li>Prawo odstąpienia nie przysługuje w odniesieniu do produktów wykonanych na specjalne zamówienie Klienta (zamówienia indywidualne).</li>
    <li>Prawo odstąpienia od umowy nie przysługuje podmiotom nabywającym towar w związku z prowadzoną działalnością gospodarczą.</li>
  </ol>

  <h2 style="margin-top:2rem">VI. Ochrona danych osobowych i pliki cookie</h2>
  <ol>
    <li>Administratorem danych osobowych Klientów jest właściciel sklepu Unique Ceramics. Dane przetwarzane są wyłącznie w celu realizacji zamówień i kontaktu z Klientem.</li>
    <li>Podanie danych osobowych jest dobrowolne, lecz niezbędne do realizacji zamówienia.</li>
    <li>Klientowi przysługuje prawo dostępu do swoich danych, ich poprawiania oraz usunięcia. W tym celu należy napisać na adres <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</li>
    <li>Dane nie są udostępniane osobom trzecim, z wyjątkiem przewoźnika w zakresie niezbędnym do realizacji dostawy.</li>
    <li>Strona wykorzystuje pliki cookie niezbędne do prawidłowego działania koszyka oraz pliki analityczne. Korzystanie ze strony bez zmiany ustawień przeglądarki oznacza zgodę na stosowanie plików cookie.</li>
  </ol>

  <h2 style="margin-top:2rem">VII. Postanowienia końcowe</h2>
  <ol>
    <li>Sprzedawca zastrzega sobie prawo do zmiany Regulaminu. Zmiany nie dotyczą zamówień złożonych przed datą zmiany.</li>
    <li>W sprawach nieuregulowanych niniejszym Regulaminem zastosowanie mają przepisy prawa polskiego, w szczególności Kodeksu Cywilnego oraz Ustawy o prawach konsumenta.</li>
    <li>Ewentualne spory rozstrzygane są przez właściwy sąd powszechny. Konsument może skorzystać z pozasądowych sposobów rozstrzygania sporów.</li>
  </ol>

<?php else: ?>

  <h2>I. General Provisions</h2>
  <ol>
    <li>The online store available at <strong>uniqueceramics.pl</strong> is operated by an individual trading under the brand name <strong>Unique Ceramics</strong>.</li>
    <li>Contact: e-mail <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>, phone <a href="tel:<?= str_replace(' ', '', SITE_PHONE) ?>"><?= SITE_PHONE ?></a>.</li>
    <li>All products are handmade. Each piece is unique and may differ slightly from the product photo — this is a natural characteristic of artisan ceramics, not a defect.</li>
    <li>Glaze cracking (crackle) is a natural result of the firing process and does not constitute a physical defect.</li>
    <li>Customers must be adults with full legal capacity.</li>
    <li>Prices shown are gross prices (VAT-inclusive) and do not include shipping costs.</li>
  </ol>

  <h2 style="margin-top:2rem">II. Orders</h2>
  <ol>
    <li>Orders can be placed via the website form or by e-mail at <?= SITE_EMAIL ?>.</li>
    <li>After placing an order, the Customer receives a confirmation e-mail.</li>
    <li>The sales contract is formed upon order confirmation by the seller.</li>
    <li>In-stock orders are dispatched within 3–5 business days after payment is confirmed.</li>
    <li>Custom orders may require up to 4 weeks — the timeline is agreed individually.</li>
    <li>The seller reserves the right to refuse an order in justified cases, notifying the Customer by e-mail.</li>
  </ol>

  <h2 style="margin-top:2rem">III. Payment &amp; Delivery</h2>
  <ol>
    <li>Payment method: <strong>bank transfer</strong>. Bank details are sent by e-mail after the order is placed. Payment must be made within 7 days.</li>
    <li>Shipping is handled by a selected carrier (courier or Polish Post).</li>
    <li>Shipping costs <strong><?= format_price(SHIPPING_COST) ?></strong>. Orders of <?= format_price(SHIPPING_FREE_FROM) ?> or more qualify for <strong>free shipping</strong>.</li>
    <li>Delivery within Poland typically takes 1–3 business days after dispatch.</li>
    <li>The seller is not liable for delays caused by the carrier.</li>
    <li>A receipt or VAT invoice (on request) is included with every order.</li>
  </ol>

  <h2 style="margin-top:2rem">IV. Complaints</h2>
  <ol>
    <li>The seller is liable to consumers under the statutory warranty for physical and legal defects.</li>
    <li>Complaints should be submitted by e-mail to <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>, describing the defect and attaching photos.</li>
    <li>The seller will respond within 14 calendar days of receiving the complaint.</li>
    <li>If a complaint is upheld, the Customer may request repair, replacement, a price reduction, or cancellation of the contract.</li>
    <li>Differences in appearance resulting from handcrafting, natural glaze properties (e.g. crackle), and surface texture variations do not constitute defects.</li>
    <li>Consumers may use out-of-court dispute resolution methods, including the EU ODR platform: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.</li>
  </ol>

  <h2 style="margin-top:2rem">V. Right of Withdrawal</h2>
  <ol>
    <li>Consumers have the right to withdraw from a distance contract without giving reasons within <strong>14 days</strong> of receiving the goods.</li>
    <li>To exercise this right, notify the seller by e-mail at <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a> before the deadline.</li>
    <li>The seller will confirm receipt of the withdrawal notice promptly.</li>
    <li>The Consumer must return the goods within 14 days of withdrawal. The direct cost of return shipping is borne by the Consumer.</li>
    <li>Refunds will be issued no later than 14 days after receipt of the returned goods, by bank transfer.</li>
    <li>The right of withdrawal does not apply to products made to the Customer's individual specifications.</li>
    <li>The right of withdrawal does not apply to purchases made in connection with business activity.</li>
  </ol>

  <h2 style="margin-top:2rem">VI. Data Protection &amp; Cookies</h2>
  <ol>
    <li>The data controller is the owner of Unique Ceramics. Personal data is processed solely for order fulfilment and customer communication.</li>
    <li>Providing personal data is voluntary but necessary to process an order.</li>
    <li>Customers have the right to access, correct, and delete their data by contacting <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</li>
    <li>Data is not shared with third parties, except the carrier to the extent necessary for delivery.</li>
    <li>The site uses cookies required for the shopping cart and analytics. Continued use of the site without changing browser settings implies consent to cookies.</li>
  </ol>

  <h2 style="margin-top:2rem">VII. Final Provisions</h2>
  <ol>
    <li>The seller reserves the right to amend these Terms. Amendments do not affect orders placed before the amendment date.</li>
    <li>Matters not covered by these Terms are governed by Polish law, in particular the Civil Code and the Consumer Rights Act.</li>
    <li>Disputes are resolved by the competent Polish court. Consumers may also use out-of-court dispute resolution.</li>
  </ol>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
