# Qualität: Browser, Mobil, Ladezeiten, Barrierefreiheit

Stand 2026-10-27. Gemessen lokal gegen Apache mit der echten `.htaccess`
(`web/Dockerfile.dev`, siehe [architektur.md](architektur.md)).

## Wiederholen

```bash
php web/tests/run_tests.php                    # Vertragstests, < 1 s

cd web/tests/browser
npm ci && npx playwright install chromium firefox webkit
BASE_URL=http://127.0.0.1:8932 node check.mjs  # ~45 s, Exit 1 bei Fehler
```

WebKit unter Ubuntu ohne Root: fehlt `libevent-2.1-7`, die `.so`-Dateien aus
dem Paket nach `~/.cache/ms-playwright/webkit-*/minibrowser-wpe/lib/` kopieren.

## Browser-Kompatibilität

`check.mjs`: 3 Engines × 4 Viewports × 12 Seiten (DE + EN), Klick auf das
Flaggen-Toggle (DE → EN → DE, gleiche Seite), Navigation höchstens zwei
Zeilen, Header-Band direkt unter der Navigation, höchstens halbe
Fensterhöhe, mittig, Footer-Band schließt die Seite ab, dazu
Fallback-Simulation. Letzter Lauf: siehe Commit-Historie; zuletzt alles
bestanden, auch als Unterordner-Paket unter Apache.

| Engine | steht für | Desktop 1440 | iPhone 13 | 320×640 | Quer 844×390 |
|---|---|---|---|---|---|
| Chromium 1248 | Chrome, Edge, Opera, Samsung Internet | ✔ | ✔ | ✔ | ✔ |
| Firefox 1555 | Firefox | ✔ | ✔ | ✔ | ✔ |
| WebKit 2370 | Safari macOS/iOS, alle iOS-Browser | ✔ | ✔ | ✔ | ✔ |

✔ heißt: kein horizontales Scrollen, beide Schriften geladen, Navigation
sticky, keine kaputten Bilder, keine JS-/Konsolenfehler, **keine Anfragen an
fremde Hosts**, Tap-Ziele ≥ 44 px, Bänder wie oben.

**Ältere Browser ohne Container Queries** (Safari 15, Chrome < 105): simuliert,
indem `check.mjs` das Stylesheet so ausliefert, wie eine alte Engine es
versteht; kein horizontales Scrollen bei 390/820/1440 px.

**doiuse** (Browserslist `defaults, Safari >= 15, iOS >= 15, Samsung >= 20`):
gemeldet nur Opera Mini (keine Relevanz) sowie für Safari 15 Container
Queries (Fallback siehe oben) und `:focus-visible` (Safari 15 zeigt dann
den Standard-Fokusring – funktional).

## Mobil

- Getestet 320, 360, 390, 430 px hoch und 844×390, 1024×600 quer: kein
  Overflow auf allen Seiten.
- Navigation: auf Phones 100 px (≤ 16 % der Höhe bei 320×640), quer 58 px.
- Tap-Ziele ≥ 44 px; Ausnahme nur Links im Fließtext (WCAG 2.5.8
  „inline“). Behoben: Marken-Link „Ruhr Acro“ war 19 px hoch.
- Bänder per `srcset`/`sizes` in 1600/2600/4000/6173 px (AVIF, WebP-Fallback).
- `viewport-fit=cover`, `theme-color`, Touch-Icon.

## Ladezeiten

Lighthouse 12.8 (Chromium, Standard-Drosselung „Slow 4G“, mobil), gemessen
mit dem früheren Poster-Hero-Layout; für das Band-Layout neu messen:

| Seite | Perf / A11y / Best Pr. / SEO | LCP | CLS | TBT | Übertragen |
|---|---|---|---|---|---|
| `/` mobil | 99 / 100 / 100 / 100 | 2,3 s | 0,002 | 0 ms | 250 KiB |
| `/` Desktop | 100 / 100 / 100 / 100 | 0,6 s | 0,006 | 0 ms | 324 KiB |
| `/programm` mobil / Desktop | 100 / 100 / 100 / 100 | 1,4 s / 0,3 s | 0,017 / 0,001 | 0 ms | 84 KiB |
| `/tickets` mobil / Desktop | 100 / 100 / 100 / 100 | 1,4 s / 0,3 s | 0,003 / 0,001 | 0 ms | 84 KiB |
| `/anreise` mobil / Desktop | 100 / 100 / 100 / 100 | 1,4 s / 0,4 s | 0,015 / 0,001 | 0 ms | 84 KiB |

Maßnahmen:

- LCP-Bild (Header-Band) mit `fetchpriority="high"`, `srcset`, AVIF/WebP;
  Footer-Band und alle anderen
  Bilder `loading="lazy"`.
- Eine CSS-Datei (5 KB gzip), kein JavaScript.
- Kalam vorgeladen; Preload-URL ist identisch mit der `@font-face`-URL (vorher
  doppelter Download, −22 KB). Ein Test sichert das.
- gzip für HTML/CSS/XML; CSS 1 Jahr `immutable` mit `?v=<hash>`, Bilder und
  Fonts 1 Monat.
- `width`/`height` an allen Bildern → kein Layout-Shift.

Bei echtem Hetzner-Hosting kommen TTFB und TLS hinzu; nach dem Go-live mit
PageSpeed Insights gegen `https://ruhracro.de` nachmessen.

## Barrierefreiheit

Lighthouse Accessibility **100** auf allen geprüften Seiten (vorher 98:
Programm-Tage als `h3` direkt unter `h1`, jetzt `h2`).
Kontraste ≥ 4,5:1, sichtbarer Fokus, Skip-Link zum Inhalt,
`prefers-reduced-motion`, `aria-current` in der Navigation, externe Links mit
versteckt „(öffnet in neuem Tab)“.

## Best Practices

Lighthouse **100** (vorher 93: Inline-`style`-Attribute wurden von der CSP
blockiert, wodurch Level-Farben fehlten; jetzt Klassen in `site.css`, Test
verhindert Rückfall).
