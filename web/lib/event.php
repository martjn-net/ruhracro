<?php
declare(strict_types=1);

/**
 * Single source of truth for every fact on the site (HTML, JSON-LD,
 * sitemap). Sources: quelle/ poster, exporte/timetable.jpeg and the booking
 * page on buchsys.de (see docs/inhalte.md). Change facts only here.
 * Translatable values are ['de' => ..., 'en' => ...]; pick with L().
 */

/**
 * Deployment: production is https://ruhracro.de at the domain root. Another
 * target (e.g. a preview in a sub-folder) sets site_url/preview/design in an
 * untracked lib/deploy.php or the file named by $ACRO_DEPLOY_CONFIG
 * (template: deploy.example.php; tools/bundle.sh writes one).
 * Two designs share content, routing and SEO; only the page chrome differs
 * (docs/architektur.md). Which one goes live is not decided yet.
 */
const DESIGNS = ['classic', 'scene']; // classic: poster hero; scene: header/footer bands
(function () {
    $file = getenv('ACRO_DEPLOY_CONFIG') ?: __DIR__ . '/deploy.php';
    $cfg = (is_file($file) ? require $file : []) + [
        'site_url' => 'https://ruhracro.de', // confirmed by owner 2026-10-27; DNS not live yet
        'preview'  => false,                 // true: noindex + "preview" banner on every page
        'design'   => 'classic',
    ];
    if (!in_array($cfg['design'], DESIGNS, true)) throw new InvalidArgumentException("unknown design {$cfg['design']}");
    define('SITE_URL', rtrim($cfg['site_url'], '/'));
    define('BASE_PATH', rtrim((string) parse_url(SITE_URL, PHP_URL_PATH), '/')); // '' or '/acro'
    define('PREVIEW', (bool) $cfg['preview']);
    define('DESIGN', $cfg['design']);
})();
const LANGS = ['de', 'en'];             // first = default (x-default)

const EVENT = [
    'name'        => 'Ruhr Acro Convention 2027',
    'claim'       => 'Move together. Grow together',
    'subtitle'    => ['de' => 'Acroyoga & Akrobatik Festival', 'en' => 'Acroyoga & Acrobatics Festival'],
    'start'       => '2027-03-05T16:00:00+01:00',
    'end'         => '2027-03-07T17:30:00+01:00',
    'date_label'  => ['de' => '5.–7. März 2027', 'en' => '5–7 March 2027'],
    'days_label'  => ['de' => 'Freitag bis Sonntag', 'en' => 'Friday to Sunday'],
    'booking_url' => 'https://www.buchsys.de/due/angebote/aktueller_zeitraum_1/_AcroYoga__und__Akrobatik_Festival.html',
    'instagram'   => 'https://www.instagram.com/ruhracroconvention',
    // the university is the event host and the only seller of tickets
    'organizer'   => ['de' => 'Hochschulsport der Universität Duisburg-Essen',
                      'en' => 'Hochschulsport (University Sports) of the University of Duisburg-Essen'],
    'organizer_url' => 'https://www.uni-due.de/hochschulsport/',
];

/**
 * Hosting provider (processor, Art. 28 DSGVO) for the privacy policy, as
 * documented by Hetzner (checked 2026-10-27, sources in docs/recht.md):
 * - access and error logs (www_logs) store IP addresses anonymised
 * - www_logs are NOT deleted by default; an account maintenance rule in
 *   konsoleH (domain → Einstellungen → Accountwartung → Weblogs) deletes them
 * - the DPA (AV-Vertrag) is not concluded automatically (Hetzner terms)
 * - processing only in the EU/EEA (AV-Vertrag § 3)
 * confirmed: owner has concluded the DPA and set the log rule to
 * log_retention for every domain serving the site; --prelaunch fails until then.
 */
const HOSTING = [
    'name'          => 'Hetzner Online GmbH',
    'address'       => 'Industriestr. 25, 91710 Gunzenhausen',
    'log_retention' => ['de' => '7 Tagen', 'en' => '7 days'],
    'confirmed'     => false,
];

/**
 * Website operator and organiser of the convention: Impressum (§ 5 DDG) and
 * controller for Datenschutz (Art. 13 DSGVO). Personal data, so it lives in
 * the untracked web/lib/provider.php (copy of provider.example.php); the
 * example keeps placeholders and `run_tests.php --prelaunch` fails while they
 * remain (docs/deploy.md). Optional keys: phone, vat_id.
 */
define('PROVIDER', require __DIR__ . (is_file(__DIR__ . '/provider.php') ? '/provider.php' : '/provider.example.php'));

const VENUE = [
    'name'     => 'Sporthalle an der Schützenbahn',
    'street'   => 'Schützenbahn 70',
    'entrance' => ['de' => 'Eingang Waldthausenstraße, Gebäude S-M', 'en' => 'Entrance Waldthausenstraße, building S-M'],
    'zip'      => '45127',
    'city'     => 'Essen',
    'region'   => 'NRW',
    'country'  => 'DE',
    'lat'      => 51.46198,
    'lon'      => 7.01754,
];

/** OpenStreetMap: marker on the hall entrance (the search URL finds nothing). */
function osm_url(): string
{
    return sprintf('https://www.openstreetmap.org/?mlat=%1$.5f&mlon=%2$.5f#map=18/%1$.5f/%2$.5f', VENUE['lat'], VENUE['lon']);
}

/** Tickets as listed on buchsys.de (status checked 2026-10-27). */
const TICKETS = [
    ['name' => 'Early Bird', 'available' => false, 'prices' => [74, 89, 105]],
    ['name' => 'Regular',    'available' => true,  'prices' => [79, 99, 119]],
];

const PRICE_GROUPS = [
    ['de' => 'Studierende der UDE, FWU und HRW, Angehörige der UDE, Gasthörer*innen, Schüler*innen ab 16',
     'en' => 'Students of UDE, FWU and HRW, UDE members, guest students, school pupils aged 16+'],
    ['de' => 'Studierende anderer Hochschulen, Beschäftigte von UDE und HRW, Azubis extern',
     'en' => 'Students of other universities, staff of UDE and HRW, external apprentices'],
    ['de' => 'Alle anderen', 'en' => 'Everyone else'],
];

/**
 * Timetable from exporte/timetable.jpeg: [from, to, kind, workshop block].
 * from = null: "before <to>"; to = null: "from <from>". Texts: SLOT_LABELS.
 */
const PROGRAM = [
    'freitag' => ['label' => ['de' => 'Freitag', 'en' => 'Friday'], 'date' => '2027-03-05', 'items' => [
        [null,    '16:00', 'setup',    null],
        ['16:00', '17:20', 'arrival',  null],
        ['17:20', '17:50', 'welcome',  null],
        ['17:50', '18:00', 'demo',     1],
        ['18:00', '20:00', 'workshop', 1],
        ['20:00', '22:00', 'evening',  null],
    ]],
    'samstag' => ['label' => ['de' => 'Samstag', 'en' => 'Saturday'], 'date' => '2027-03-06', 'items' => [
        ['09:10', '09:50', 'arrival',  null],
        ['09:50', '10:00', 'demo',     2],
        ['10:00', '11:00', 'workshop', 2],
        ['11:00', '11:20', 'break',    null],
        ['11:20', '11:30', 'demo',     3],
        ['11:30', '13:30', 'workshop', 3],
        ['13:30', '14:50', 'lunch',    null],
        ['14:50', '15:00', 'demo',     4],
        ['15:00', '17:00', 'workshop', 4],
        ['17:00', '17:20', 'break',    null],
        ['17:20', '17:30', 'demo',     5],
        ['17:30', '19:30', 'workshop', 5],
        ['19:30', '23:00', 'evening',  null],
    ]],
    'sonntag' => ['label' => ['de' => 'Sonntag', 'en' => 'Sunday'], 'date' => '2027-03-07', 'items' => [
        ['09:20', '09:50', 'arrival',  null],
        ['09:50', '10:00', 'demo',     6],
        ['10:00', '11:00', 'workshop', 6],
        ['11:00', '11:20', 'break',    null],
        ['11:20', '11:30', 'demo',     7],
        ['11:30', '13:30', 'workshop', 7],
        ['13:30', '14:50', 'lunch',    null],
        ['14:50', '15:00', 'demo',     8],
        ['15:00', '17:00', 'workshop', 8],
        ['17:00', '17:30', 'closing',  null],
        ['17:30', null,    'teardown', null],
    ]],
];

/** kind => [title, note] per language; %d = workshop block. */
const SLOT_LABELS = [
    'setup'    => ['de' => ['Aufbau', 'Helfende vor Ort'],                      'en' => ['Setup', 'helpers on site']],
    'arrival'  => ['de' => ['Check-in', 'und Ankommen'],                         'en' => ['Check-in', 'and arrival']],
    'welcome'  => ['de' => ['Welcome Circle', 'und Begrüßung'],                  'en' => ['Welcome Circle', 'and welcome']],
    'demo'     => ['de' => ['Demos der Workshops', 'von Block %d'],              'en' => ['Workshop demos', 'for block %d']],
    'workshop' => ['de' => ['Workshop Block %d', null],                          'en' => ['Workshop block %d', null]],
    'break'    => ['de' => ['Pause', null],                                      'en' => ['Break', null]],
    'lunch'    => ['de' => ['Mittagspause', 'Jam und freies Training'],          'en' => ['Lunch break', 'jam and open training']],
    'evening'  => ['de' => ['Abendpause', 'Jam und freies Training'],            'en' => ['Evening break', 'jam and open training']],
    'closing'  => ['de' => ['Closing Circle', 'und gemeinsamer Abschluss'],      'en' => ['Closing Circle', 'and farewell']],
    'teardown' => ['de' => ['Abbau', null],                                      'en' => ['Teardown', null]],
];

/**
 * Page registry: id => per language [path, nav label, <title>, meta description].
 * Drives routing, navigation, the language toggle, hreflang and the sitemap.
 */
const PAGES = [
    'home' => [
        'de' => ['/', 'Start', 'Ruhr Acro Convention 2027 – Acroyoga & Akrobatik Festival in Essen',
                 'Acroyoga- und Akrobatik-Festival vom 5. bis 7. März 2027 in Essen: Workshops für alle Level, Jams und Community. Veranstalter: Hochschulsport der UDE.'],
        'en' => ['/en', 'Home', 'Ruhr Acro Convention 2027 – Acroyoga & Acrobatics Festival, Essen',
                 'Acroyoga and acrobatics festival in Essen, Germany, 5–7 March 2027: workshops for all levels, jams and community. Hosted by UDE university sports.'],
    ],
    'programm' => [
        'de' => ['/programm', 'Programm', 'Programm & Zeitplan – Ruhr Acro Convention 2027',
                 'Zeitplan der Ruhr Acro Convention 2027 in Essen: 8 Workshop-Blöcke von Freitag bis Sonntag, Workshop-Demos, Jams und freies Training.'],
        'en' => ['/en/programme', 'Programme', 'Programme & Schedule – Ruhr Acro Convention 2027',
                 'Schedule of the Ruhr Acro Convention 2027 in Essen: 8 workshop blocks from Friday to Sunday, workshop demos, jams and open training.'],
    ],
    'tickets' => [
        'de' => ['/tickets', 'Tickets', 'Tickets & Preise – Ruhr Acro Convention 2027',
                 'Tickets für die Ruhr Acro Convention 2027 in Essen ab 79 €. Verkauf ausschließlich über den Hochschulsport der Universität Duisburg-Essen.'],
        'en' => ['/en/tickets', 'Tickets', 'Tickets & Prices – Ruhr Acro Convention 2027',
                 'Tickets for the Ruhr Acro Convention 2027 in Essen from €79, sold exclusively by the university sports of the University of Duisburg-Essen.'],
    ],
    'anreise' => [
        'de' => ['/anreise', 'Anreise', 'Anreise – Sporthalle an der Schützenbahn, Essen',
                 'So kommst du zur Ruhr Acro Convention: Sporthalle an der Schützenbahn 70, 45127 Essen, Eingang Waldthausenstraße.'],
        'en' => ['/en/directions', 'Directions', 'Directions – Sporthalle an der Schützenbahn, Essen',
                 'How to get to the Ruhr Acro Convention: Sporthalle an der Schützenbahn, Schützenbahn 70, 45127 Essen, entrance Waldthausenstraße.'],
    ],
    'impressum' => [
        'de' => ['/impressum', 'Impressum', 'Impressum – Ruhr Acro Convention 2027',
                 'Impressum der Website der Ruhr Acro Convention 2027: Betreiber, Kontakt, Veranstalter (Hochschulsport UDE) und Ticketverkauf.'],
        'en' => ['/en/legal-notice', 'Legal notice', 'Legal Notice – Ruhr Acro Convention 2027',
                 'Legal notice of the Ruhr Acro Convention 2027 website: operator, contact, event host (UDE university sports) and ticket sales.'],
    ],
    'datenschutz' => [
        'de' => ['/datenschutz', 'Datenschutz', 'Datenschutz – Ruhr Acro Convention 2027',
                 'Datenschutzerklärung der Ruhr Acro Convention 2027: keine Cookies, keine Tracker, keine Formulare, keine externen Dienste. Infos zu Server-Logs.'],
        'en' => ['/en/privacy', 'Privacy', 'Privacy Policy – Ruhr Acro Convention 2027',
                 'Privacy policy of the Ruhr Acro Convention 2027 website: no cookies, no tracking, no forms, no external services. Information on server logs.'],
    ],
];

/** Pages shown in the small legal group of the navigation. */
const LEGAL_PAGES = ['impressum', 'datenschutz'];
