# yameen.app (يمين)

Arabic text tools for designers, video editors and game developers:
type Arabic, preview it in free Arabic fonts, copy a version that works in
software without Arabic support, or export it as PNG/SVG.

All text and font processing runs in the browser. The Laravel server renders
pages and manages accounts, credits and payments.

## Stack

- Laravel 13, PHP 8.3, MySQL (SQLite in-memory for tests)
- Blade + Alpine.js, Vite, Tailwind CSS v4 (custom tokens only, default palette removed)
- Self-hosted fonts (`@fontsource/*`, bundled by Vite with hashed filenames)
- Pest (PHP tests), Vitest (JS tests)

## Local setup

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm run build   # or: npm run dev
php artisan serve
```

## Tests

```bash
./vendor/bin/pest
npm test
```

## Structure

- `routes/web.php` registers `routes/localized.php` once per locale:
  Arabic at `/`, English under `/en`. Route names are `{locale}.{name}`;
  use `lroute('fix')` in views.
- `config/yameen.php`: locales, software pages, font styles.
- `lang/{ar,en}/ui.php`: interface strings.
- `resources/css/app.css`: design tokens (`--paper`, `--lav-*`, `--ink`, ...).
  Colours are only defined as CSS variables so a dark theme can be added later.
- `resources/svg/wordmark.svg`: wordmark outlines generated from Reem Kufi 600.
