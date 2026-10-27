<?php
declare(strict_types=1);

require_once __DIR__ . '/event.php';

/* ------------------------------------------------------------- language */

/** Current language; set once per request by serve(). */
function lang(?string $set = null): string
{
    static $lang = LANGS[0];
    if ($set !== null) $lang = $set;
    return $lang;
}

/** Pick the current language from a ['de' => .., 'en' => ..] value. */
function L(array|string $value): string
{
    return is_array($value) ? $value[lang()] : $value;
}

/** Inline translation, raw (may contain markup written in the template). */
function tr(string $de, string $en): string
{
    return lang() === 'en' ? $en : $de;
}

/** Inline translation, HTML-escaped. */
function t(string $de, string $en): string
{
    return e(tr($de, $en));
}

function page_url(string $id, ?string $lang = null): string
{
    return PAGES[$id][$lang ?? lang()][0];
}

/* --------------------------------------------------------------- routing */

/**
 * Pure routing: URI -> ['redirect' => path] or ['page', 'lang', 'status'].
 * Every page URL comes from PAGES; anything else is a 404 in the language of
 * its prefix.
 */
function route(string $uri): array
{
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    if ($path === '/index.php') return ['redirect' => '/'];
    if ($path !== '/' && str_ends_with($path, '/')) return ['redirect' => rtrim($path, '/') ?: '/'];
    if ($path === '/sitemap.xml') return ['page' => 'sitemap', 'lang' => LANGS[0], 'status' => 200];
    foreach (PAGES as $id => $page) {
        foreach (LANGS as $l) {
            if ($page[$l][0] === $path) return ['page' => $id, 'lang' => $l, 'status' => 200];
        }
    }
    $lang = ($path === '/en' || str_starts_with($path, '/en/')) ? 'en' : LANGS[0];
    return ['page' => '404', 'lang' => $lang, 'status' => 404];
}

/** Render a routed page (front controller and tests). */
function serve(array $route): void
{
    lang($route['lang']);
    if ($route['status'] !== 200) http_response_code($route['status']);
    if ($route['page'] === 'sitemap') {
        render_sitemap();
        return;
    }
    require __DIR__ . '/../pages/' . $route['page'] . '.php';
}

/* --------------------------------------------------------------- helpers */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Opens in a new tab without giving the target access to window.opener. */
function ext(string $url, string $label, string $class = '', string $icon = ''): string
{
    return sprintf(
        '<a%s href="%s" target="_blank" rel="noopener">%s%s<span class="visually-hidden"> %s</span></a>',
        $class ? ' class="' . e($class) . '"' : '', e($url), $icon, e($label),
        t('(öffnet in neuem Tab)', '(opens in a new tab)')
    );
}

function icon(string $name): string
{
    $paths = [
        'ticket'    => '<path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2v-2a2 2 0 0 0 0-4Z"/><path d="M14 6v12" stroke-dasharray="2 2"/>',
        'map'       => '<path d="M9 4 3 6.5v13.5L9 17.5l6 2.5 6-2.5V4l-6 2.5L9 4Z"/><path d="M9 4v13.5M15 6.5V20"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>',
        'pin'       => '<path fill="currentColor" stroke="none" d="M12 1.5a8 8 0 0 0-8 8c0 5.6 6.6 12.2 7.3 12.8a1 1 0 0 0 1.4 0C13.4 21.7 20 15.1 20 9.5a8 8 0 0 0-8-8Zm0 11.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4Z"/>',
    ];
    return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[$name] . '</svg>';
}

function asset(string $path): string
{
    // cache-busting by content hash; site.css is served with a 1-year max-age
    $file = __DIR__ . '/../public/' . $path;
    return '/' . $path . (is_file($file) ? '?v=' . substr(md5_file($file), 0, 8) : '');
}

/* ---------------------------------------------------------------- layout */

/** $id: key of PAGES, or '404'. */
function page_head(string $id, array $jsonld = [], string $bodyClass = ''): void
{
    $found = $id !== '404';
    [$path, , $title, $desc] = $found
        ? PAGES[$id][lang()]
        : [null, null, tr('Seite nicht gefunden', 'Page not found') . ' – Ruhr Acro Convention', ''];
    $img = SITE_URL . '/img/social-1200x630.jpg';
    $locale = ['de' => 'de_DE', 'en' => 'en_GB'];
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<?php if (!$found): ?>
<meta name="robots" content="noindex">
<?php else: ?>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e(SITE_URL . $path) ?>">
<?php foreach (LANGS as $l): ?>
<link rel="alternate" hreflang="<?= e($l) ?>" href="<?= e(SITE_URL . page_url($id, $l)) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= e(SITE_URL . page_url($id, LANGS[0])) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= e($locale[lang()]) ?>">
<?php foreach (LANGS as $l): if ($l !== lang()): ?>
<meta property="og:locale:alternate" content="<?= e($locale[$l]) ?>">
<?php endif; endforeach; ?>
<meta property="og:site_name" content="Ruhr Acro Convention">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e(SITE_URL . $path) ?>">
<meta property="og:image" content="<?= e($img) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= t('Poster der Ruhr Acro Convention 2027: 5.–7. März 2027 in Essen', 'Poster of the Ruhr Acro Convention 2027: 5–7 March 2027 in Essen') ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<meta name="theme-color" content="#001913">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= e(asset('img/touch-icon.png')) ?>">
<?php /* same URL as the @font-face in site.css, otherwise the font loads twice */ ?>
<link rel="preload" href="/fonts/kalam-700-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/site.css')) ?>">
<?php foreach ($jsonld as $block): ?>
<script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body<?= $bodyClass ? ' class="' . e($bodyClass) . '"' : '' ?>>
<a class="skip" href="#inhalt"><?= t('Zum Inhalt springen', 'Skip to content') ?></a>
<?php
    site_nav($id);
}

function site_nav(string $id): void
{
    $legal = in_array($id, LEGAL_PAGES, true);
    ?>
<nav class="nav" aria-label="<?= t('Hauptnavigation', 'Main navigation') ?>">
  <a class="nav__brand" href="<?= e(page_url('home')) ?>"<?= $id === 'home' ? ' aria-current="page"' : '' ?>>Ruhr Acro</a>
  <ul class="nav__links">
<?php foreach (PAGES as $p => $page):
        if ($p === 'home') continue; ?>
    <li<?= in_array($p, LEGAL_PAGES, true) ? ' class="nav__legal"' : '' ?>><a href="<?= e($page[lang()][0]) ?>"<?= $p === $id ? ' aria-current="page"' : '' ?>><?= e($page[lang()][1]) ?></a></li>
<?php endforeach; ?>
    <li class="nav__legal-compact"><a href="<?= e(page_url('impressum')) ?>"<?= $legal ? ' aria-current="page"' : '' ?> aria-label="<?= t('Impressum und Datenschutz', 'Legal notice and privacy') ?>"><?= t('Rechtliches', 'Legal') ?></a></li>
  </ul>
<?php lang_toggle($id); ?>
  <?= ext(EVENT['booking_url'], tr('Jetzt buchen', 'Book now'), 'btn btn--nav') ?>
</nav>
<?php
}

/** Flag toggle: links to the same page in each language (no JavaScript). */
function lang_toggle(string $id): void
{
    $names = ['de' => 'Deutsch', 'en' => 'English'];
    ?>
  <ul class="lang" aria-label="<?= t('Sprache', 'Language') ?>">
<?php foreach (LANGS as $l):
        $href = page_url($id === '404' ? 'home' : $id, $l); ?>
    <li<?= $l === lang() ? ' class="lang__current"' : '' ?>><a href="<?= e($href) ?>" hreflang="<?= e($l) ?>" lang="<?= e($l) ?>" aria-label="<?= e($names[$l]) ?>"<?= $l === lang() ? ' aria-current="true"' : '' ?>><img src="<?= e(asset("assets/flag-$l.svg")) ?>" width="24" height="16" alt=""><span class="lang__code" aria-hidden="true"><?= e(strtoupper($l)) ?></span></a></li>
<?php endforeach; ?>
  </ul>
<?php
}

function page_foot(): void
{
    ?>
<footer class="footer">
  <img src="<?= e(asset('img/lotus.webp')) ?>" width="160" height="92" alt="" loading="lazy" decoding="async">
  <p class="motto"><?= e(EVENT['claim']) ?></p>
  <p class="footer__links">
    <?= ext(EVENT['instagram'], '@ruhracroconvention', 'link', icon('instagram')) ?>
  </p>
  <div class="footer__org">
    <p><?= t('Veranstalter', 'Event host') ?>: <?= ext(EVENT['organizer_url'], L(EVENT['organizer'])) ?></p>
    <p><?= t('Organisation und Website', 'Organisation and website') ?>: <?= e(PROVIDER['name']) ?></p>
    <p><?= t('Diese Website informiert nur und verkauft nichts. Tickets verkauft ausschließlich der Hochschulsport der UDE über buchsys.de.',
             'This website provides information only and sells nothing. Tickets are sold exclusively by UDE university sports via buchsys.de.') ?></p>
  </div>
  <nav class="footer__legal" aria-label="<?= t('Rechtliches', 'Legal') ?>">
<?php foreach (LEGAL_PAGES as $p): ?>
    <a href="<?= e(page_url($p)) ?>"><?= e(PAGES[$p][lang()][1]) ?></a>
<?php endforeach; ?>
  </nav>
  <p class="fineprint"><?= e(EVENT['name']) ?></p>
</footer>
</body>
</html>
<?php
}

/* ---------------------------------------------------------------- JSON-LD */

function ld_place(): array
{
    return [
        '@type' => 'Place',
        'name' => VENUE['name'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => VENUE['street'],
            'postalCode' => VENUE['zip'],
            'addressLocality' => VENUE['city'],
            'addressRegion' => VENUE['region'],
            'addressCountry' => VENUE['country'],
        ],
        'geo' => ['@type' => 'GeoCoordinates', 'latitude' => VENUE['lat'], 'longitude' => VENUE['lon']],
    ];
}

/**
 * One Offer per ticket type (Google: "a nested Offer, one for each ticket
 * type"; price = lowest price of that type). Tickets are not a schema.org
 * Product for Google; event tickets belong in Event.offers. Seller is the
 * university: this website sells nothing.
 */
function ld_offers(): array
{
    return array_map(fn (array $t) => [
        '@type' => 'Offer',
        'name' => $t['name'] . ' Ticket',
        'price' => min($t['prices']),
        'priceCurrency' => 'EUR',
        'availability' => $t['available'] ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
        'url' => EVENT['booking_url'],
        'validThrough' => EVENT['start'],
        'seller' => ['@type' => 'Organization', 'name' => L(EVENT['organizer']), 'url' => EVENT['organizer_url']],
        'priceSpecification' => array_map(fn ($price, $group) => [
            '@type' => 'UnitPriceSpecification',
            'price' => $price,
            'priceCurrency' => 'EUR',
            'eligibleCustomerType' => L($group),
        ], $t['prices'], PRICE_GROUPS),
    ], TICKETS);
}

function ld_event(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        '@id' => SITE_URL . '/#event',
        'name' => EVENT['name'],
        'alternateName' => 'Ruhr Acro Convention – ' . L(EVENT['subtitle']),
        'description' => tr(
            'Ein Wochenende voller Acroyoga und Partnerakrobatik: 8 Workshop-Blöcke für alle Level (Standing Acro, Hand to Hand, Foot to Hand, Pops, Whips, Icarians, Flows), Jams, freies Training und Community.',
            'A weekend full of acroyoga and partner acrobatics: 8 workshop blocks for all levels (Standing Acro, Hand to Hand, Foot to Hand, Pops, Whips, Icarians, Flows), jams, open training and community.'
        ),
        'startDate' => EVENT['start'],
        'endDate' => EVENT['end'],
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'inLanguage' => lang(),
        'location' => ld_place(),
        // Google recommends several aspect ratios (1:1, 4:3, 16:9), >= 720px wide
        'image' => [
            SITE_URL . '/img/event-1x1.jpg',
            SITE_URL . '/img/event-4x3.jpg',
            SITE_URL . '/img/event-16x9.jpg',
        ],
        'url' => SITE_URL . page_url('home'),
        'organizer' => [
            '@type' => 'Organization',
            'name' => L(EVENT['organizer']),
            'url' => EVENT['organizer_url'],
        ],
        'offers' => ld_offers(),
        'sameAs' => [EVENT['instagram']],
    ];
}

function ld_website(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => SITE_URL . '/#website',
        'url' => SITE_URL . '/',
        'name' => 'Ruhr Acro Convention',
        'inLanguage' => LANGS,
        'about' => ['@id' => SITE_URL . '/#event'],
    ];
}

function ld_breadcrumb(string $id): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => PAGES['home'][lang()][1], 'item' => SITE_URL . page_url('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => PAGES[$id][lang()][1], 'item' => SITE_URL . page_url($id)],
        ],
    ];
}

/* --------------------------------------------------------------- content */

/** "16:00–17:20", "vor 16:00" or "ab 17:30" as HTML. */
function slot_time(?string $from, ?string $to): string
{
    if ($from === null) return t('vor', 'before') . ' <time>' . e($to) . '</time>';
    if ($to === null) return t('ab', 'from') . ' <time>' . e($from) . '</time>';
    return '<time>' . e($from) . '</time>–<time>' . e($to) . '</time>';
}

/** [title, note] of a timetable slot in the current language. */
function slot_text(string $kind, ?int $block): array
{
    return array_map(fn (?string $s) => $s === null ? null : sprintf($s, $block), SLOT_LABELS[$kind][lang()]);
}

function render_program(): void
{
    foreach (PROGRAM as $id => $day):
        $date = strtotime($day['date']); ?>
  <section class="day day--<?= e($id) ?>" aria-labelledby="tag-<?= e($id) ?>">
    <h2 id="tag-<?= e($id) ?>"><?= e(L($day['label'])) ?> <time datetime="<?= e($day['date']) ?>"><?= e(date(tr('j.n.', 'j M'), $date)) ?></time></h2>
    <ol class="slots">
<?php foreach ($day['items'] as [$from, $to, $kind, $block]):
            [$title, $note] = slot_text($kind, $block); ?>
      <li class="slot slot--<?= e($kind) ?>">
        <span class="slot__time"><?= slot_time($from, $to) ?></span>
        <span class="slot__what"><strong><?= e($title) ?></strong><?= $note ? ' <span>' . e($note) . '</span>' : '' ?></span>
      </li>
<?php endforeach; ?>
    </ol>
  </section>
<?php endforeach;
}

/** sitemap.xml with hreflang alternates; lastmod = newest source of a page. */
function render_sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $lib = max(array_map('filemtime', glob(__DIR__ . '/*.php')));
    $priority = ['home' => '1.0', 'programm' => '0.9', 'tickets' => '0.9', 'anreise' => '0.6'];
    echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">', "\n";
    foreach (PAGES as $id => $page) {
        $lastmod = date('Y-m-d', max($lib, filemtime(__DIR__ . "/../pages/$id.php")));
        foreach (LANGS as $l) {
            echo '  <url><loc>', e(SITE_URL . $page[$l][0]), '</loc><lastmod>', $lastmod, '</lastmod><priority>', $priority[$id] ?? '0.2', '</priority>';
            foreach (LANGS as $alt) {
                echo '<xhtml:link rel="alternate" hreflang="', $alt, '" href="', e(SITE_URL . $page[$alt][0]), '"/>';
            }
            echo '<xhtml:link rel="alternate" hreflang="x-default" href="', e(SITE_URL . $page[LANGS[0]][0]), '"/>';
            echo "</url>\n";
        }
    }
    echo "</urlset>\n";
}
