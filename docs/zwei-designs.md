# Ruhr Acro Convention – zwei Website-Designs (Stand 2026-10-27)

Repo: `martjn-net/ruhracro`, Commit `474df7a`.

## Prinzip
- Deploy-Konfiguration `design`: `classic` (alte Website, Standard) oder `scene` (neue Version).
- Gemeinsam: Texte, Seiten, Sprachumschalter, SEO/JSON-LD, Sitemap, Rechtstexte. Nur der Rahmen unterscheidet sich.
- `classic` liefert byte-identisches HTML wie vor der Umstellung (alle 12 Seiten geprüft).
- Entscheidung, welches Design live geht: offen (`TODO.md`).

## Bauen
```bash
tools/bundle.sh https://martjn.net/acro --preview                      # classic -> dist/acro/
tools/bundle.sh https://martjn.net/acro-scene --preview --design scene # scene   -> dist/acro-scene/
```
Jedes Paket enthält nur die Bilder seines Designs.

## Design `scene`
- Header-Band: Poster-Szene ohne Schrift, max. 50 % Fensterhöhe, gleichmäßig skaliert, seitlich unscharfer Farbüberlauf (min(200px, 14 %)).
- Navigation sticky direkt über dem Header (überdeckt die Szene nicht).
- Startseite: Titel, Untertitel, Ort, Datum, Halle, „Jetzt buchen“, „Programm ansehen“, Veranstalter als Text unter dem Header.
- Footer: Lotus, Claim, Instagram, Veranstalter, Betreiber, „verkauft nichts“, Impressum/Datenschutz; darunter Pflanzen-Band.
- Dateien: `web/public/assets/scene.css`, `band()` in `web/lib/render.php`, Bilder aus `tools/build_assets.py` (`head-*`, `foot-*`, AVIF 4:4:4 + WebP).

## Lokal
- classic: http://127.0.0.1:8931
- scene: `ACRO_DEPLOY_CONFIG=web/tests/deploy-scene.php php -S 127.0.0.1:8936 -t web/public web/dev-router.php`

## Tests
```bash
php web/tests/run_tests.php
ACRO_DEPLOY_CONFIG=web/tests/deploy-scene.php php web/tests/run_tests.php
ACRO_DEPLOY_CONFIG=web/tests/deploy-subfolder.php php web/tests/run_tests.php
cd web/tests/browser && BASE_URL=<url> node check.mjs   # erkennt das Design selbst
```
Alle grün; Browsermatrix (Chromium, Firefox, WebKit × 4 Viewports) für beide Designs bestanden.

## Offen
- `dist/acro-scene/` nach `public_html/acro-scene/` hochladen (inkl. `.htaccess`).
- Design entscheiden.
