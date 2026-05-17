# Project Rules

## Git Workflow

After every modification to files in this project, always:
1. Stage the changed files with `git add`
2. Commit with a concise message describing the change
3. Push to `main` branch: `git push origin master:main`

---

## Project Overview

**Unique Ceramics** — dwujęzyczny (PL/EN) sklep e-commerce z ceramiką ręcznie robioną.
- PHP 8.x + SQLite (PDO), bez frameworka
- Hosting: InfinityFree (LiteSpeed), deploy przez GitHub Actions FTP
- Repo: https://github.com/Thashar/Unique-Ceramics
- Strona: http://uniqueceramics.ct8.pl (lub podobny subdomain InfinityFree)

### Struktura kluczowych plików
- `config.php` — stałe, sesja, helpery (format_price, url, t, csrf)
- `includes/header.php` / `includes/footer.php` — wspólny layout frontendu
- `includes/lang/pl.php` / `en.php` — tłumaczenia
- `includes/db.php` / `functions.php` — baza danych i funkcje produktów
- `admin/includes/admin-header.php` — layout panelu admina
- `assets/css/main.css` — style frontendu
- `assets/css/admin.css` — style panelu admina (OSOBNY plik!)
- `assets/js/main.js` — JS frontendu
- `assets/images/logo.jpg` — logo strony
- `assets/images/about-photo.jpg` — zdjęcie na stronie O mnie
- `uploads/products/` — zdjęcia produktów (nie wersjonowane w git)
- `.github/workflows/deploy.yml` — auto-deploy FTP na push do main

### Znane problemy i rozwiązania
- **Symbol zł na InfinityFree**: `format_price()` zwraca `<span class="curr"></span>`, CSS dodaje `zł` przez `.curr::after { content: " z\142"; }` — reguła musi być w OBIE: `main.css` i `admin.css`
- **Cache CSS**: link do CSS ma `?v=<?= filemtime(...) ?>` — wymagane w obu headerach
- **FTP deploy**: nie wersjonuje `uploads/`, `data/shop.db`, `.git*`

### Zasady pracy (nauczone z błędów)
- Przy każdej globalnej zmianie (CSS, stała PHP, funkcja) — grep całego projektu, napraw frontend I admin naraz w jednym commicie
- `main.css` i `admin.css` to osobne pliki — zmiany w jednym nie trafiają do drugiego automatycznie
- Przed commitem "kompleksowej" naprawy zawsze sprawdź `admin/*.php`

### Właściciel / Dane firmy
- Właściciel: jedna osoba (forma pierwszoosobowa: "O mnie", "moja ceramika", nie "my/nasze")
- Telefon: +48 668 443 706
- E-mail: kontakt@uniqueceramics.pl
- Instagram: @unique.ceramics
- Wysyłka: 18 zł, darmowa od 300 zł
