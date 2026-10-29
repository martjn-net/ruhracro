<?php
// Site checks without a framework: php web/tests/run_tests.php [--prelaunch]
// Renders every page in both languages through the router and checks the
// contracts that a visitor or a search engine relies on. --prelaunch
// additionally fails on owner TODOs.
declare(strict_types=1);

require __DIR__ . '/../lib/render.php';

$prelaunch = in_array('--prelaunch', $argv, true);
$fail = 0;
$pass = 0;
function check(bool $ok, string $msg): void
{
    global $fail, $pass;
    $ok ? $pass++ : $fail++;
    if (!$ok) fwrite(STDERR, "FAIL  $msg\n");
}

function render(string $uri): string
{
    ob_start();
    serve(route($uri));
    return (string) ob_get_clean();
}

/** All tags of a name as attribute maps (good enough for our own markup). */
function tags(string $html, string $name): array
{
    preg_match_all('~<' . $name . '\b([^>]*)>~i', $html, $m);
    return array_map(function (string $attrs): array {
        preg_match_all('~([a-z:-]+)(?:="([^"]*)")?~i', $attrs, $a, PREG_SET_ORDER);
        $out = [];
        foreach ($a as $x) $out[strtolower($x[1])] = html_entity_decode($x[2] ?? '', ENT_QUOTES);
        return $out;
    }, $m[1]);
}

function between(string $html, string $open, string $close): string
{
    $s = strpos($html, $open);
    return $s === false ? '' : substr($html, $s, strpos($html, $close, $s) - $s);
}

$public = public_dir();
$css = file_get_contents($public . '/assets/site.css');
$B = BASE_PATH; // '' in production, e.g. '/acro' for a sub-folder preview

// routing: one URL per page and language, everything else redirects or 404s
check(route("$B/programm/") === ['redirect' => "$B/programm"], 'route: trailing slash redirects');
check(route("$B/en/") === ['redirect' => "$B/en"], 'route: /en/ redirects to /en');
check(route("$B/index.php") === ['redirect' => "$B/"], 'route: /index.php redirects');
check(route("$B/programm.php")['status'] === 404, 'route: old .php URLs are gone');
check(route("$B/gibtsnicht") === ['page' => '404', 'lang' => 'de', 'status' => 404], 'route: unknown -> German 404');
check(route("$B/en/nope") === ['page' => '404', 'lang' => 'en', 'status' => 404], 'route: unknown /en/ -> English 404');
check(route("$B/en/programme?x=1") === ['page' => 'programm', 'lang' => 'en', 'status' => 200], 'route: query string ignored');
check(PAGES['home']['de'][0] === '/', 'German is the default language at /');
if ($B !== '') {
    check(route($B) === ['redirect' => "$B/"], 'route: base path without slash redirects');
    check(route('/programm')['status'] === 404, 'route: paths outside the base path are 404');
}

// German UI text must not leak into English pages and vice versa
const LEAKS = [
    'en' => ['Jetzt buchen', 'Zum Inhalt springen', 'öffnet in neuem Tab', 'Programm ansehen', 'Veranstalter:', 'Rechtliches', 'Gut zu wissen', 'Preisgruppe', 'Mittagspause', 'ausgebucht'],
    'de' => ['Book now', 'Skip to content', 'opens in a new tab', 'See the programme', 'Event host', 'Good to know', 'price group', 'Lunch break', 'sold out'],
];

$pages = [];
foreach (PAGES as $id => $page) {
    foreach (LANGS as $l) {
        [$path, $label, $title, $desc] = $page[$l];
        $where = "$l $path";
        $html = render(page_url($id, $l));
        $pages[$l][$id] = $html;
        $nav = between($html, '<nav class="nav"', '</nav>');
        $toggle = between($html, '<ul class="lang"', '</ul>');
        $footer = between($html, '<footer', '</footer>');
        preg_match('~<title>(.*?)</title>~s', $html, $t);
        $links = tags($html, 'link');
        $canon = array_values(array_filter($links, fn ($x) => ($x['rel'] ?? '') === 'canonical'));
        $alts = [];
        foreach ($links as $x) if (($x['rel'] ?? '') === 'alternate') $alts[$x['hreflang']] = $x['href'];

        check(str_contains($html, '<html lang="' . $l . '">'), "$where: html lang");
        check(preg_match_all('~<h1\b~', $html) === 1, "$where: exactly one h1");
        check(html_entity_decode($t[1] ?? '') === $title, "$where: title");
        check(iconv_strlen($title, 'UTF-8') <= 70, "$where: title <= 70 chars");
        check(iconv_strlen($desc, 'UTF-8') >= 50 && iconv_strlen($desc, 'UTF-8') <= 160, "$where: description 50..160 chars");
        check(($canon[0]['href'] ?? '') === SITE_URL . $path, "$where: canonical");
        check(substr_count($html, 'property="og:image"') === 1, "$where: og:image");

        // hreflang: every language version plus x-default (= German)
        $expect = ['de' => SITE_URL . $page['de'][0], 'en' => SITE_URL . $page['en'][0], 'x-default' => SITE_URL . $page['de'][0]];
        check($alts == $expect, "$where: hreflang alternates");

        // flag toggle: links to this page in both languages, current one marked
        foreach (LANGS as $other) {
            check(str_contains($toggle, 'href="' . page_url($id, $other) . '"'), "$where: toggle links $other version");
            check(str_contains($toggle, 'src="' . $B . '/assets/flag-' . $other . '.svg'), "$where: toggle shows $other flag");
        }
        check(substr_count($toggle, 'aria-current="true"') === 1 && preg_match('~hreflang="' . $l . '"[^>]*aria-current="true"~', $toggle) === 1, "$where: toggle marks current language");

        // internal links stay in the page language (except the toggle)
        $rest = str_replace($toggle, '', $html);
        foreach (tags($rest, 'a') as $a) {
            $href = $a['href'] ?? '';
            if (!str_starts_with($href, '/') || isset($a['lang'])) continue;
            // every internal link must carry the base path (sub-folder deployments)
            check($href === "$B/" || str_starts_with($href, "$B/"), "$where: internal link $href inside base path");
            $rel = substr($href, strlen($B));
            $isEn = $rel === '/en' || str_starts_with($rel, '/en/');
            check($l === 'en' ? $isEn : !$isEn, "$where: internal link $href in page language");
        }

        // navigation: always present, legal pages always linked
        foreach (LEGAL_PAGES as $legal) {
            check(str_contains($nav, 'href="' . page_url($legal, $l) . '"'), "$where: nav links $legal");
            check(str_contains($footer, 'href="' . page_url($legal, $l) . '"'), "$where: footer links $legal");
        }
        // preview deployments: banner and noindex on every page; production: neither
        check(str_contains($html, '<p class="preview">') === PREVIEW, "$where: preview banner only in preview mode");
        check(str_contains($html, 'name="robots" content="noindex"') === PREVIEW, "$where: noindex only in preview mode");

        // who is who: university hosts and sells, the website sells nothing
        check(str_contains($footer, e(L(EVENT['organizer']))), "$where: footer names the university as host");
        check(str_contains($footer, e(PROVIDER['name'])), "$where: footer names the website operator");
        check(str_contains($footer, $l === 'en' ? 'sells nothing' : 'verkauft nichts'), "$where: footer says the website sells nothing");

        foreach (LEAKS[$l] as $leak) check(!str_contains($html, $leak), "$where: no foreign UI text '$leak'");

        // every external link opens a new tab without window.opener
        foreach (tags($html, 'a') as $a) {
            if (!preg_match('~^https?://~', $a['href'] ?? '')) continue;
            check(($a['target'] ?? '') === '_blank', "$where: {$a['href']} target=_blank");
            check(str_contains($a['rel'] ?? '', 'noopener'), "$where: {$a['href']} rel=noopener");
        }
        check(str_contains($html, 'href="' . e(EVENT['booking_url']) . '"'), "$where: booking link");
        check(substr_count($html, 'href="' . e(EVENT['instagram']) . '"') === 1, "$where: instagram link");

        // no third-party requests: everything the browser loads is local
        foreach (['img' => ['src', 'srcset'], 'source' => ['srcset'], 'script' => ['src'], 'link' => ['href'], 'iframe' => ['src']] as $tag => $attrs) {
            foreach (tags($html, $tag) as $n) {
                if ($tag === 'link' && in_array($n['rel'] ?? '', ['canonical', 'alternate'], true)) continue;
                foreach ($attrs as $attr) {
                    $v = $n[$attr] ?? '';
                    check(!preg_match('~(^|[\s,])(https?:)?//~', $v), "$where: local $tag $attr $v");
                    foreach (preg_split('~\s*,\s*~', trim($v)) as $src) {
                        if ($src !== '') check(str_starts_with($src, "$B/"), "$where: $tag $attr $src inside base path");
                    }
                }
            }
        }
        foreach (tags($html, 'img') as $img) {
            check(isset($img['alt']), "$where: img alt");
            check(isset($img['width'], $img['height']), "$where: img dimensions");
        }

        // JSON-LD parses
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $ld);
        foreach ($ld[1] as $json) check(json_decode($json) !== null, "$where: JSON-LD valid");
        // Google: one event per page, schedule pages must not carry multiple events
        check(substr_count($html, '"@type":"Event"') === ($id === 'home' ? 1 : 0), "$where: event markup only on the event page");
        // CSP style-src 'self' blocks style="" attributes
        check(!preg_match('~\sstyle="~', $html), "$where: no inline style attributes");
        // headings never skip a level
        preg_match_all('~<h([1-6])\b~', $html, $h);
        $prev = 0;
        foreach ($h[1] as $lvl) { check((int) $lvl <= $prev + 1, "$where: heading order h$prev -> h$lvl"); $prev = (int) $lvl; }
        // preloaded fonts must use the exact URL the stylesheet requests
        foreach ($links as $x) {
            if (($x['rel'] ?? '') !== 'preload' || ($x['as'] ?? '') !== 'font') continue;
            check(str_contains($css, 'url("..' . substr($x['href'], strlen($B)) . '")'), "$where: preload {$x['href']} matches @font-face");
        }

        // removed by request
        check(!str_contains($html, 'Programm und weitere Infos folgen'), "$where: no 'Infos folgen'");
        check(!str_contains($html, 'Termin speichern') && !str_contains($html, '.ics'), "$where: no calendar download");
        check(!preg_match('~openstreetmap\.org/search~', $html), "$where: no broken OSM search link");
    }
}

// page frame: header band between navigation and content, footer band inside the footer
foreach ($pages as $l => $byId) {
    foreach ($byId as $id => $html) {
        $head = strpos($html, '<div class="band band--head">');
        check($head !== false && $head > strpos($html, '<nav class="nav"') && $head < strpos($html, '<main'), "$l $id: header band between navigation and content");
        check(str_contains(between($html, '<footer', '</footer>'), '<div class="band band--foot">'), "$l $id: footer band inside the footer");
    }
}
foreach (array_keys(BAND_HEIGHTS) as $band) {
    foreach (BAND_WIDTHS as $w) {
        foreach (['avif', 'webp'] as $ext) check(is_file("$public/img/$band-$w.$ext"), "band image $band-$w.$ext exists");
    }
}
// duplicate titles/descriptions compete with each other in search results
$all = array_merge(...array_map(fn ($p) => array_values($p), array_values(PAGES)));
check(count(array_unique(array_column($all, 2))) === count($all), 'titles unique');
check(count(array_unique(array_column($all, 3))) === count($all), 'descriptions unique');
check(count(array_unique(array_column($all, 0))) === count($all), 'paths unique');

// 404 in the visitor's language, not indexed
foreach (["$B/gibtsnicht" => 'de', "$B/en/nope" => 'en'] as $uri => $l) {
    $html = render($uri);
    check(str_contains($html, '<html lang="' . $l . '">') && str_contains($html, 'name="robots" content="noindex"'), "404 $uri: $l, noindex");
}

// tickets: the university sells, this website does not
check(str_contains($pages['de']['tickets'], 'Diese Website verkauft nichts'), 'tickets de: no-sale notice');
check(str_contains($pages['en']['tickets'], 'This website sells nothing'), 'tickets en: no-sale notice');
check(str_contains($pages['de']['impressum'], 'Kein Verkauf über diese Website'), 'impressum: no-sale section');
// privacy policy (Art. 13 DSGVO): concrete log retention and the hosting processor, no placeholder
foreach (LANGS as $l) {
    lang($l);
    $html = $pages[$l]['datenschutz'];
    check(str_contains($html, e(L(HOSTING['log_retention']))) && !str_contains($html, '[SPEICHERDAUER') && !str_contains($html, '[LOG RETENTION'), "datenschutz $l: log retention stated");
    check(str_contains($html, e(HOSTING['name'])) && str_contains($html, 'Art. 28'), "datenschutz $l: hosting processor named");
}

// home: event data follows Google's Event rich-result rules
foreach (LANGS as $l) {
    lang($l);
    $ld = ld_event();
    check($ld['inLanguage'] === $l && $ld['url'] === SITE_URL . PAGES['home'][$l][0], "event $l: language and url");
    check($ld['startDate'] === '2027-03-05T16:00:00+01:00' && $ld['endDate'] === '2027-03-07T17:30:00+01:00', "event $l: dates");
    // visible dates (home, programme, share alt text) come from the same start/end
    $label = ['de' => '5.–7. März 2027', 'en' => '5–7 March 2027'][$l];
    check(event_dates() === $label, "event $l: date label '$label'");
    check(str_contains($pages[$l]['programm'], $label), "programm $l: date label in the lead");
    check(array_column($ld['offers'], 'price') === [74, 79], "event $l: offer price = lowest price per ticket type");
    check(array_column($ld['offers'], 'availability') === ['https://schema.org/SoldOut', 'https://schema.org/InStock'], "event $l: offer availability");
    check(array_column(array_column($ld['offers'], 'seller'), 'name') === [L(EVENT['organizer']), L(EVENT['organizer'])], "event $l: university is the seller");
}
foreach ($ld['image'] as $img) {
    $file = $public . substr((string) parse_url($img, PHP_URL_PATH), strlen($B));
    $size = is_file($file) ? getimagesize($file) : false;
    check($size !== false && $size[0] >= 720, "event image exists and >= 720px: $img");
}
check(str_contains($pages['de']['home'], 'Move together. Grow together<'), 'final claim without trailing dot');

// programme: every slot from the timetable appears as text in both languages
$slots = 0;
foreach (LANGS as $l) {
    lang($l);
    foreach (PROGRAM as $day) {
        foreach ($day['items'] as [$from, $to, $kind, $block]) {
            $l === 'de' && $slots++;
            [$title] = slot_text($kind, $block);
            $html = $pages[$l]['programm'];
            check(str_contains($html, '<strong>' . e($title) . '</strong>') && str_contains($html, '<time>' . e($from ?? $to) . '</time>'), "programm $l: {$day['date']} $title");
        }
    }
}
check($slots === 30, "programm: 30 slots (got $slots)");
foreach (SLOT_LABELS as $kind => $labels) {
    check(isset($labels['de'], $labels['en']), "slot label $kind in both languages");
}

// times are consistent: each slot ends where the next starts
foreach (PROGRAM as $id => $day) {
    $items = array_values(array_filter($day['items'], fn ($i) => $i[0] !== null && $i[1] !== null));
    for ($i = 1; $i < count($items); $i++) {
        check($items[$i - 1][1] === $items[$i][0], "programm $id: {$items[$i - 1][1]} == {$items[$i][0]}");
    }
}

// OSM link points at the hall
check(osm_url() === 'https://www.openstreetmap.org/?mlat=51.46198&mlon=7.01754#map=18/51.46198/7.01754', 'osm url');

// sitemap lists every page in both languages with its alternates
$sitemap = render("$B/sitemap.xml");
check(preg_match('~^<\?xml[^>]*\?>\s*<urlset [^>]*>(\s*<url>.*?</url>)+\s*</urlset>\s*$~s', $sitemap) === 1, 'sitemap is well-formed');
foreach (PAGES as $id => $page) {
    foreach (LANGS as $l) {
        check(preg_match('~<url><loc>' . preg_quote(SITE_URL . $page[$l][0], '~') . '</loc>.*?</url>~', $sitemap, $u) === 1, "sitemap: $l {$page[$l][0]}");
        check(substr_count($u[0] ?? '', 'hreflang=') === count(LANGS) + 1, "sitemap: $l {$page[$l][0]} alternates");
    }
}

if ($prelaunch) {
    foreach (PROVIDER as $k => $v) check(!str_contains($v, '['), "prelaunch: PROVIDER[$k] filled in");
    check(HOSTING['confirmed'], 'prelaunch: Hetzner DPA concluded and www_logs maintenance rule set (HOSTING confirmed)');
    $ht = file_get_contents($public . '/.htaccess');
    check(!str_contains($ht, '# RewriteCond %{HTTPS} off'), 'prelaunch: HTTPS redirect enabled');
}

printf("%d passed, %d failed\n", $pass, $fail);
exit($fail ? 1 : 0);
