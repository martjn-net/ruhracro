# Architektur: PHP-Seiten statt einer HTML-Datei

## Entscheidung

**Kleine PHP-Seiten mit gemeinsamer Datenquelle, gemeinsamem Layout und
einem Front-Controller, zweisprachig (Deutsch Standard, Englisch unter `/en`).**
Kein Framework, kein Composer, kein JavaScript.

| Kriterium | Eine `index.html` | PHP-Seiten (gewählt) |
|---|---|---|
| Programm, Tickets, Anreise als eigene URLs (SEO, teilbare Links) | Anker auf einer Seite | eigene Seiten mit eigenem Title/Description |
| Navigation, Head, Footer | auf jeder Seite kopiert | einmal in `render.php` |
| Fakten (Datum, Preise, Zeitplan) | mehrfach im HTML, im JSON-LD, in der Sitemap | einmal in `event.php`, überall daraus gerendert |
| Sitemap, hreflang, Sprachumschalter | von Hand | aus `PAGES` generiert |
| Tests | nur Browser | `run_tests.php` rendert alles in-process |
| Hosting | überall | Hetzner Webhosting mit PHP ist vorhanden |

PHP rendert pro Request wenige KB ohne Datenbank; Ladezeit identisch zu
statischem HTML (siehe [qualitaet.md](qualitaet.md)).

## Struktur

```
web/
├── lib/                    ← nicht per URL erreichbar (.htaccess: denied)
│   ├── event.php           ← alle Fakten + Seitenregister PAGES (Single Source of Truth)
│   ├── render.php          ← Router, Sprache, Layout, Navigation, Toggle, JSON-LD, Sitemap
│   ├── provider.example.php← Vorlage Betreiber-Daten (provider.php: nicht im Repo)
│   └── .htaccess
├── pages/                  ← Seitenvorlagen, DE und EN nebeneinander
│   └── home programm tickets anreise impressum datenschutz 404 (.php)
├── public/                 ← Docroot
│   ├── index.php           ← Front-Controller: route() → serve()
│   ├── .htaccess robots.txt
│   ├── assets/site.css, flag-de.svg, flag-en.svg   ← Cache-Busting über ?v=<md5>
│   ├── img/  fonts/        ← Build-Ergebnis (build.md), nicht im Repo
├── tests/run_tests.php     ← Vertragstests (.htaccess: denied); tests/browser/check.mjs
│   deploy-subfolder.php    ← Test-Konfiguration Unterordner-Vorschau
├── dev-router.php          ← lokaler Ersatz für .htaccess bei `php -S`
└── Dockerfile.dev          ← lokales Apache wie Hetzner (nicht deployen)
```

## Seitenrahmen

Jede Seite: sticky Navigation, Header-Band, Inhalt, Footer mit Pflanzen-Band
(Gestaltung: [design.md](design.md), Bilder: [build.md](build.md)).
`page_head()` gibt Kopf, Navigation und `band('head')` aus, `page_foot()` den
Footer mit `band('foot')`. Gemeinsame Bausteine in `render.php`:
`event_dates()` (sichtbares Datum aus `EVENT` start/end), `book_button()`
(alle Buchen-Links), `ext()` (externe Links).

Refactorings werden gegen einen Render-Schnappschuss aller Routen geprüft
(Ausgabe bis auf Leerraum gleich) und die Bildpipeline gegen SHA256 aller
Dateien in `web/public/img/`.

## Mehrsprachigkeit

- `PAGES[id][lang] = [Pfad, Navi-Label, <title>, Description]` – daraus
  entstehen Routing, Navigation, Flaggen-Toggle, `hreflang` und Sitemap.
- Deutsch ist Standard (`/`, `x-default`), Englisch liegt unter `/en/…` mit
  englischen Slugs. Keine automatische Umleitung nach Browsersprache (Crawler
  und geteilte Links sehen immer dieselbe Sprache).
- Texte stehen in den Vorlagen nebeneinander: `t('Deutsch', 'English')`
  (escaped) bzw. `tr()` (roh, für eigenes Markup); übersetzbare Fakten in
  `event.php` als `['de' => …, 'en' => …]`, gelesen mit `L()`. Programmpunkte
  sind reine Daten (Zeit, Art, Block), Beschriftungen kommen aus `SLOT_LABELS`.
- Flaggen-Toggle: zwei normale Links mit `hreflang`, `lang`, `aria-label`
  („Deutsch“/„English“), aktuelle Sprache `aria-current`. Unter 360 px nur die
  Flagge der anderen Sprache.
- Tests verhindern deutsche UI-Texte auf englischen Seiten (und umgekehrt) und
  interne Links in die falsche Sprache.

## URLs

| Anfrage | Antwort |
|---|---|
| `/`, `/programm`, `/tickets`, `/anreise`, `/impressum`, `/datenschutz` | 200, Deutsch |
| `/en`, `/en/programme`, `/en/tickets`, `/en/directions`, `/en/legal-notice`, `/en/privacy` | 200, Englisch |
| `/sitemap.xml` | 200, 12 URLs mit `xhtml:link`-Alternativen |
| `/programm/`, `/en/`, `/index.php` | 301 auf die saubere URL |
| alles andere | 404 (`noindex`), Englisch unter `/en/…`, sonst Deutsch |
| HTTP, `www.` | 301 auf `https://ruhracro.de` (nach HTTPS-Aktivierung, [deploy.md](deploy.md)) |

`.htaccess` leitet jede Anfrage, die keine echte Datei ist, an `index.php`;
alle Regeln stehen in `route()` und werden ohne Server getestet.

## Sicherheit und Datenschutz

- **CSP:** `default-src 'self'; script-src 'none'; style-src 'self'; frame-ancestors 'self'; form-action 'none'`.
  Keine Inline-Styles und keine Skripte (Test prüft `style=""`).
- `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`.
- Keine Cookies, kein Tracking, keine Drittanbieter-Ressourcen. Externe Links
  öffnen in neuem Tab mit `rel="noopener"`.
- Personenbezogene Daten (Impressum) nur in `web/lib/provider.php`, nicht im Repo.

## Caching

| Typ | Header |
|---|---|
| `site.css?v=<hash>` | `max-age=31536000, immutable` |
| Fonts, Bilder | 1 Monat (nicht versioniert, weil `@font-face`-URL und Preload übereinstimmen müssen) |
| HTML | `max-age=0` |
| gzip | HTML, CSS, XML, Text, JSON-LD |

## Lokal entwickeln

```bash
# schnell, ohne Apache:
php -S 127.0.0.1:8931 -t web/public web/dev-router.php

# wie Produktion (.htaccess, gzip, Header):
docker build -f web/Dockerfile.dev -t ruhracro-web web
docker run --rm -p 127.0.0.1:8932:80 -v "$PWD/web:/var/www/site:ro" ruhracro-web
```

## Tests

```bash
php web/tests/run_tests.php              # Pflicht vor jedem Upload
php web/tests/run_tests.php --prelaunch  # zusätzlich: Impressum-Daten + HTTPS
```

Prüft je Seite unter anderem: Titel (≤ 70 Zeichen) und Description (50–160),
beide seitenübergreifend eindeutig, Canonical, `og:image`, `lang="de"`, genau
eine `h1`, Überschriften ohne Sprung, Impressum und Datenschutz in Navigation
und Footer, Buchungs- und Instagram-Link, externe Links mit `_blank` und
`noopener`, keine externen Ressourcen, `alt`/`width`/`height` an Bildern, keine
Inline-Styles, gültiges JSON-LD, Event-Markup nur auf `/`, Event-Bilder
≥ 720 px vorhanden, Preload-URL gleich `@font-face`-URL, alle 30
Timetable-Slots als Text, lückenloser Zeitplan, entfernte Inhalte („Infos
folgen“, „Termin speichern“, `.ics`, OSM-Suchlink) bleiben weg, Header-Band
zwischen Navigation und Inhalt, Footer-Band im Footer, alle Bandbilder
vorhanden.
Läuft ohne die PHP-Erweiterungen dom, xml und mbstring.
