# Ruhr Acro Convention – Website

Website für die Ruhr Acro Convention 2027 (5.–7. März 2027, Essen) unter
`https://ruhracro.de`, Deutsch (Standard) und Englisch (`/en`). PHP-Seiten
ohne JavaScript, alle Ressourcen selbst gehostet, Deploy auf Hetzner
Webhosting. Offene Aufgaben: [TODO.md](../TODO.md).

| Dokument | Inhalt |
|---|---|
| [inhalte.md](inhalte.md) | Faktenquellen, Timetable, Tickets, Pflege |
| [recht.md](recht.md) | Rollen (Veranstalter, Betreiber), Impressum, Datenschutz |
| [architektur.md](architektur.md) | Entscheidung PHP, Struktur, Mehrsprachigkeit, URLs, Sicherheit, Tests |
| [design.md](design.md) | Gestaltung, Tokens, Navigation, Flaggen-Toggle, Layout |
| [build.md](build.md) | Bilder und Schriften bauen, Quellen, Reproduzierbarkeit |
| [deploy.md](deploy.md) | Go-live und Upload auf Hetzner |
| [qualitaet.md](qualitaet.md) | Browser, Mobil, Ladezeiten, Barrierefreiheit |
| [seo.md](seo.md) | Keywords, On-Page, Crawlbarkeit, hreflang, JSON-LD, Social |
| [backlink-strategie.md](backlink-strategie.md) | Backlink-Ziele, Priorisierung, Maßnahmenplan |
| [wiederholbarkeit.md](wiederholbarkeit.md) | Wiederholbarkeit, fehlender Kontext, Grenzen |

Schnellstart:

```bash
tools/build.sh                                            # Bilder + Schriften
php -S 127.0.0.1:8931 -t web/public web/dev-router.php    # lokal ansehen
php web/tests/run_tests.php                               # Tests
```
