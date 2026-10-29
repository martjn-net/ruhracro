# Deploy auf Hetzner Webhosting (konsoleH)

Hetzner Webhosting: Apache mit PHP, `.htaccess` aktiv, kein SSH – Upload per
WebFTP/SFTP.

## Einmalig vor dem Go-live

1. **Betreiber-Daten:** `web/lib/provider.php` (aus `provider.example.php`)
   mit Name, Anschrift, E-Mail, optional Telefon und USt-IdNr. Die Datei ist
   per `.gitignore` ausgeschlossen (personenbezogene Daten, Repo-Regel). Sie
   speist Impressum, Datenschutz und den Footer („Organisation und Website“).
2. **Hosting-Angaben:** In konsoleH den Auftragsverarbeitungsvertrag schließen
   und per Accountwartung die Weblogs nach 7 Tagen löschen lassen (Standard:
   nie), dann `HOSTING['confirmed'] => true` in `web/lib/event.php`
   ([recht.md](recht.md)).
3. **Domain:** `ruhracro.de` in konsoleH anlegen; DNS (A/AAAA) auf den
   Webspace; PHP ≥ 8.1 wählen. Stand 2026-10-27: noch kein DNS-Eintrag.
4. **Docroot:** konsoleH → Domain → Einstellungen → Docroot auf
   `…/ruhracro/web/public/` setzen. Geht das nicht, schützen
   `.htaccess`-Dateien mit `Require all denied` in `lib/`, `pages/` und
   `tests/` die übrigen Ordner (geprüft: 403).
5. **HTTPS:** konsoleH → Domain → SSL → Let's Encrypt aktivieren. Danach in
   `web/public/.htaccess` den Block „HTTPS + no www“ und die
   `Strict-Transport-Security`-Zeile einkommentieren.
6. **Prelaunch-Test** lokal:
   ```bash
   php web/tests/run_tests.php --prelaunch   # muss 0 failed melden
   ```

## Jeder Upload

1. `tools/build.sh` (nur nötig, wenn `img/` oder `fonts/` fehlen oder sich
   Quellen geändert haben).
2. `php web/tests/run_tests.php` → `0 failed`.
3. Per WebFTP nach `…/ruhracro/web/` hochladen: `lib/` (inkl. `provider.php`),
   `pages/`, `public/`, `tests/.htaccess`. **Nicht** hochladen:
   `Dockerfile.dev`, `dev-router.php`, `tests/browser/` (werden nicht gebraucht).
4. Live prüfen:
   ```bash
   B=https://ruhracro.de
   for p in / /programm /tickets /anreise /impressum /datenschutz \
            /en /en/programme /en/tickets /en/directions /en/legal-notice /en/privacy \
            /sitemap.xml /robots.txt; do
     curl -s -o /dev/null -w "%{http_code} $p\n" $B$p; done          # alles 200
   curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" $B/programm/     # 301 → /programm
   curl -s -o /dev/null -w "%{http_code}\n" $B/gibtsnicht                    # 404
   curl -sI $B/ | grep -i content-security-policy                            # vorhanden
   ```
   Dazu einmal Startseite auf dem Handy öffnen: Ort und Datum auf den Pinseln.

## Nach dem Go-live (Suchmaschinen)

1. Google Search Console: Domain-Property `ruhracro.de` per DNS-TXT
   verifizieren, `https://ruhracro.de/sitemap.xml` einreichen.
2. Rich-Results-Test für `https://ruhracro.de/` (Event): erwartet ein gültiges
   Event ohne Fehler.
3. Bing Webmaster Tools: Import aus Search Console.
4. Backlinks nach Maßnahmenplan ([backlink-strategie.md](backlink-strategie.md)).

## Rollback

Vorige Dateien liegen lokal im Git-Stand; einfach den vorherigen Stand erneut
hochladen. Keine Datenbank, kein Zustand auf dem Server.

## Vorschau in einem Unterordner (martjn.net/acro)

Zum Zeigen vor dem Go-live läuft die Seite unter `https://martjn.net/acro` –
mit gelbem Vorschau-Banner auf jeder Seite, `noindex` und den Platzhaltern
aus `provider.example.php` (`provider.php` wird nie eingepackt).

1. Paket bauen (führt die Tests mit genau dieser Konfiguration aus):
   ```bash
   tools/bundle.sh https://martjn.net/acro --preview
   # → dist/acro/ und dist/acro.zip
   ```
2. In konsoleH/WebFTP den Ordner `public_html/acro/` **leeren** (alte Uploads
   entfernen) und den **Inhalt** von `dist/acro/` hineinladen – inklusive der
   versteckten `.htaccess`-Dateien (`acro/.htaccess`, `acro/_app/.htaccess`,
   `_app/lib/.htaccess`, `_app/pages/.htaccess`). Ohne sie ist der Code unter
   `_app/` lesbar und die Seiten liefern 404.
3. Prüfen:
   ```bash
   B=https://martjn.net/acro
   for p in / /programm /en /en/directions; do curl -s -o /dev/null -w "%{http_code} $p\n" $B$p; done  # 200
   curl -s -o /dev/null -w "%{http_code}\n" $B/_app/lib/event.php                                    # 403
   curl -s $B/ | grep -c 'class="preview"'                                                           # 1
   ```

Andere Ziele: beliebige URL mit Unterordner an `tools/bundle.sh` übergeben;
ohne `--preview` entfallen Banner und `noindex`. Die Konfiguration landet als
`_app/lib/deploy.php` im Paket (Vorlage: `web/lib/deploy.example.php`).

Die frühere Parallel-Vorschau `public_html/acro-scene/` wird nicht mehr
gebraucht und kann gelöscht werden.
