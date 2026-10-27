# Wiederholbarkeit, fehlender Kontext, Grenzen

Offene Aufgaben stehen in [TODO.md](../TODO.md).

## Wiederholbarkeit der Prozesse (Prüfung 2026-10-27)

| Prozess | Vorher | Jetzt | Nachweis |
|---|---|---|---|
| Bilder bauen | Anleitung mit `/tmp/srenv`, ungepinnte Pakete, Modell ohne Prüfsumme, zweiter Schritt mit System-Python | `tools/build.sh`: gepinnte venv, SHA256 für Modell und Quellbilder | zwei Läufe byte-identisch |
| Schriften | Herkunft unbekannt, Datei lag einfach da | `build.sh` lädt Fontsource 5.3.0 mit SHA256 | gelöscht und neu geladen: identisch |
| Quellbilder | nicht versioniert, nicht beschrieben | SHA256 in `build.sh`, Herkunft in [build.md](build.md) | Build bricht bei Abweichung ab |
| Inhalte | im HTML verstreut | `web/lib/event.php`, Quellen in [inhalte.md](inhalte.md) | Tests prüfen Zeitplan und Preise |
| Seiten prüfen | manuell im Browser | `run_tests.php` (1112 Checks, DE + EN), `tests/browser/check.mjs` | Gegenproben: entfernte Fallback-Regel, fehlendes `target`, Zeitlücke, deutscher Text auf EN-Seite, falsch-sprachiger Link werden erkannt |
| Produktionsserver lokal | nicht möglich | `web/Dockerfile.dev` (Apache + `.htaccess`) | Statuscodes, Header, gzip geprüft |
| Deploy | nicht beschrieben | [deploy.md](deploy.md), `--prelaunch`-Test | |
| Impressum-Daten | Platzhalter im Code, echte Daten wären im Repo gelandet | `web/lib/provider.php` (ignoriert) + Vorlage | Prelaunch-Test schlägt bei Platzhaltern fehl |

## Fehlender Kontext (gefunden und geschlossen)

- **Domain** (`ruhracro.de`) und **Veranstalter** (Hochschulsport UDE): beim
  Owner erfragt, in `event.php`.
- **Preise, Preisgruppen, Verpflegung, Helfer, Shirt:** von buchsys übernommen.
- **Schrift-Herkunft und Lizenz:** Fontsource, SIL OFL 1.1.
- **Hosting:** vorhandenes Hetzner-Webhosting (konsoleH, kein SSH), Docroot
  auf `web/public`.
- **EU-OS-Plattform:** am 20.07.2025 abgeschaltet, Hinweis aus dem Impressum
  entfernt.
- **Projekt-Doku:** `DESIGN.md` und `tools/README.md` waren veraltet (verwiesen
  auf `index.html`, `.ics`, „Programm folgt“) → ersetzt durch `docs/`.
- **Agenten-Kontext:** `acro/AGENTS.md` fehlte; angelegt, Zeile in der
  Root-`AGENTS.md` ergänzt.
- **Rollen:** Hochschulsport UDE = Veranstalter und einziger Ticketverkäufer,
  Organisator = Betreiber der Website (Owner, 2026-10-27); auf jeder Seite
  ausgewiesen ([recht.md](recht.md)).

## Bekannte Grenzen

- Lokales PHP 8.1 ohne dom/xml/mbstring; Tests sind darauf ausgelegt. PHP auf
  Hetzner ist neuer und nicht separat geprüft – nach dem Upload Live-Checks aus
  [deploy.md](deploy.md) ausführen.
- Ladezeiten lokal gemessen; echte Werte nach dem Go-live mit PageSpeed
  Insights.
- Alte Browser ohne Container Queries simuliert, nicht auf echtem Safari 15.
