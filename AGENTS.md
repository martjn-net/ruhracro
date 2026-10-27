# AGENTS.md — Ruhr Acro Convention (Website)

## Zweck

Website der **Ruhr Acro Convention 2027** (Acroyoga & Akrobatik Festival,
5.–7. März 2027, Sporthalle an der Schützenbahn, Essen) unter
`https://ruhracro.de`, Deutsch (Standard) und Englisch (`/en`).
Gesamtdoku: `docs/README.md`. Offene Aufgaben: `TODO.md`.

**Zwei Designs, beide jederzeit baubar** (Entscheidung offen): `classic`
(Poster als Hero) und `scene` (Header-/Footer-Band aus dem Poster). Gleiche
Inhalte, Routen, SEO; nur der Rahmen unterscheidet sich. Auswahl über den
Schlüssel `design` der Deploy-Konfiguration (`docs/architektur.md`). Keines
der beiden darf kaputtgehen: Tests immer für beide laufen lassen.

## Struktur

```
web/lib/event.php        ← ALLE Fakten + Seitenregister PAGES (einzige Stelle für Inhalte)
web/lib/render.php       ← Router, Sprache (t/tr/L), Layout, Toggle, JSON-LD, Sitemap
web/lib/provider.php     ← Betreiber-Daten, NICHT im Repo (Vorlage: provider.example.php)
web/pages/*.php          ← Seitenvorlagen, DE und EN nebeneinander
web/public/              ← Docroot: index.php (Front-Controller), .htaccess, assets/ (site.css; scene.css nur Design scene); img/ fonts/ = Build-Ergebnis
web/tests/run_tests.php  ← Vertragstests;  web/tests/browser/check.mjs ← Browser-Matrix
tools/build.sh           ← Bilder + Schriften reproduzierbar bauen
tools/bundle.sh          ← Upload-Paket für Unterordner: tools/bundle.sh <url> [--preview] [--design classic|scene]
quelle/ exporte/         ← Poster, Exporte, Timetable (Binär, nicht im Repo)
docs/                    ← Doku (Deutsch)
```

## Regeln

- **Keine erfundenen Inhalte.** Nur Fakten aus Poster, `exporte/timetable.jpeg`
  und der buchsys-Seite (`docs/inhalte.md`). Fehlt etwas: weglassen oder den
  Owner fragen. Inhalte nur in `web/lib/event.php` ändern.
- **Keine Drittanbieter-Ressourcen.** Keine CDNs, Google Fonts, Embeds,
  Analytics, kein JavaScript. Externe Ziele nur als Link mit
  `target="_blank" rel="noopener"` über `ext()`.
- **Keine Inline-Styles** (CSP `style-src 'self'`); Farben als Klassen in
  `site.css`. Design `scene` lädt zusätzlich `scene.css`, die nur den Rahmen
  überschreibt; gemeinsame Komponenten bleiben in `site.css`.
- **Assets nur per Pipeline** (`tools/build.sh`), nie von Hand. Ändern sich
  Quellbilder: Koordinaten, SHA256 und `--place-y`-Werte inkl. Fallback
  anpassen (`docs/build.md`).
- **Claim** ist final: „Move together. Grow together“ (ohne Punkt).
- **Immer beide Sprachen.** Jeder sichtbare Text über `t('DE', 'EN')` /
  `tr()` oder `['de' => …, 'en' => …]` + `L()`; neue Seiten in `PAGES` mit
  Pfad, Label, Title, Description für beide Sprachen. Interne Links nur über
  `page_url()`, statische Dateien über `asset()`/`public_url()` – nie
  hartcodiert mit `/` beginnend (die Seite läuft auch in Unterordnern).
- **Rollen eindeutig:** Veranstalter und einziger Ticketverkäufer ist der
  Hochschulsport der UDE; der Organisator betreibt die Website, die nichts
  verkauft (`docs/recht.md`). Diese Aussagen nicht abschwächen oder entfernen.
- Code Englisch, Doku Deutsch.

## Prüfen (vor jedem Abschluss)

```bash
php web/tests/run_tests.php                      # muss 0 failed melden (Design classic)
ACRO_DEPLOY_CONFIG=web/tests/deploy-scene.php php web/tests/run_tests.php       # Design scene
ACRO_DEPLOY_CONFIG=web/tests/deploy-subfolder.php php web/tests/run_tests.php   # Unterordner
# bei CSS-/Layout-Änderungen zusätzlich, gegen Apache (docs/architektur.md):
cd web/tests/browser && BASE_URL=http://127.0.0.1:8932 node check.mjs
```

Vor dem Go-live: `php web/tests/run_tests.php --prelaunch` (Betreiber-Daten,
Log-Speicherdauer, HTTPS). Deploy-Ablauf: `docs/deploy.md`.

## PII / nicht committen

- `web/lib/provider.php` (Name, Anschrift, E-Mail, Telefon des Betreibers).
  Der Name erscheint auf der Website nur über `PROVIDER['name']`, nie
  hartcodiert in Code oder Doku.
- Weitere Personennamen von der buchsys-Seite gehören weder auf die Website
  noch in die Doku.
- Binärdateien: `quelle/`, `exporte/`, `web/public/img/`, `web/public/fonts/`
  (per `.gitignore` ausgeschlossen).

## Repository (öffentlich)

- `https://github.com/martjn-net/ruhracro`, **public**. Alles, was committet
  wird, ist öffentlich.
- Commit-Identität: `martjn-net <github@martjn.net>` (Adresse darf öffentlich
  sein), lokal im Repo gesetzt (`git config user.email`), nie die globale.
- Vor jedem Commit/Push: `git diff --cached` auf Personennamen, private oder
  dienstliche Mailadressen, Arbeitgeber-Bezüge und lokale Pfade prüfen. Einzige
  erlaubte Mailadresse von uns: `github@martjn.net`.
