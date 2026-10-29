# TODO – Ruhr Acro Convention Website

Stand 2026-10-27. Technisch fertig: 12 Seiten (DE + EN), Tests grün
(`php web/tests/run_tests.php`). Offen ist, was nur der Owner erledigen kann.
`php web/tests/run_tests.php --prelaunch` zeigt den Stand der Pflichtpunkte.

## Vor dem Go-live (Pflicht)

### Prio 1 – Impressums-Anschrift klären (blockiert alles Rechtliche)

- [ ] **Anschrift fürs Impressum ohne Privatadresse** – Reihenfolge:
      1. **Uni als Website-Betreiber** (Hochschulsport UDE im Impressum,
         Organisator nur als Ansprechpartner) – braucht schriftliche Zusage der
         Uni per E-Mail (Betreiber-Rolle + DSGVO-Verantwortlicher, Laufzeit
         bis einige Monate nach der Convention).
      2. **c/o-Adresse Hochschulsport** – gleiche Uni-Anfrage im selben
         Gespräch (Postannahme + Weiterleitung an den Organisator, schriftlich).
      3. **Fallback: gemietete Geschäftsadresse** (Coworking/Business
         Address mit echter Postannahme und Erreichbarkeit, ~20–60 €/Monat,
         kurzfristig kündbar; kein Briefkasten-Service ohne Büro).
      Hintergrund: § 5 DDG verlangt eine ladungsfähige Anschrift, kein
      Postfach ([docs/recht.md](docs/recht.md)).
- [ ] **Impressum-Daten** in `web/lib/provider.php` (nicht im Repo): Straße,
      PLZ/Ort, E-Mail – abhängig vom Prio-1-Ergebnis. Der Name ist lokal
      vorbelegt (Vorname laut Owner,
      Nachname von buchsys) – bestätigen. Telefon empfohlen; USt-IdNr.
      eintragen, falls vorhanden (gewerblich).
- [ ] **Hetzner, in konsoleH (Quellen: [docs/recht.md](docs/recht.md)):**
      1. Auftragsverarbeitungsvertrag abschließen (Kundenkonto,
         accounts.hetzner.com) – wird laut Hetzner-AGB nicht automatisch geschlossen.
      2. Je Domain (ruhracro.de **und** martjn.net für die Vorschau):
         Einstellungen → Accountwartung → Weblogs nach **7 Tagen** löschen.
         Standard bei Hetzner: nie löschen – die Datenschutzerklärung nennt 7 Tage.
      3. Danach in `web/lib/event.php` `HOSTING['confirmed'] => true`.
      Optional: eine Logdatei in `www_logs` ansehen und prüfen, ob die Felder
      zur Aufzählung in der Datenschutzerklärung passen.
- [ ] **Hochschulsport UDE abstimmen:** Nennung als Veranstalter, Formulierung
      „Verkauf ausschließlich über den Hochschulsport“, Link von buchsys bzw.
      der UDE-Seite auf ruhracro.de.
- [ ] **Rechtstexte prüfen lassen** (Impressum, Datenschutz; [docs/recht.md](docs/recht.md)).
- [ ] **Domain:** ruhracro.de in konsoleH anlegen, DNS auf den Webspace,
      Docroot `web/public`, PHP ≥ 8.1 wählen.
- [ ] **HTTPS:** Let's Encrypt aktivieren, in `web/public/.htaccess` den Block
      „HTTPS + no www“ und die HSTS-Zeile einkommentieren.
- [ ] `tools/build.sh`, dann `php web/tests/run_tests.php --prelaunch` → 0 failed.
- [ ] Upload und Live-Checks nach [docs/deploy.md](docs/deploy.md).

## Direkt nach dem Go-live

- [ ] Google Search Console (Domain-Property) + `sitemap.xml` einreichen; Bing
      Webmaster Tools importieren.
- [ ] Rich-Results-Test für `https://ruhracro.de/` und `https://ruhracro.de/en`.
- [ ] PageSpeed Insights gegen die Live-Domain (lokal: 99–100).
- [ ] Backlinks nach Maßnahmenplan, Woche 1–4
      ([docs/backlink-strategie.md](docs/backlink-strategie.md)): zuerst UDE
      (Neuigkeiten, Veranstaltungskalender), DestinationHub.Ruhr, AcroWorld,
      AcroPassport.

## Inhalte, sobald vorhanden

- [ ] Englische Texte von einer englischsprachigen Person gegenlesen lassen.
- [ ] Ticketstatus regelmäßig mit buchsys abgleichen; Regular ausverkauft →
      `TICKETS` `'available' => false` (sonst falsches „InStock“ bei Google).
- [ ] ÖPNV-Haltestellen für Anreise (nur aus geprüfter Quelle, z. B. Ruhrbahn).
- [ ] Teacher und Workshop-Titel je Block → Programm und JSON-LD `performer`.
- [ ] Fotos/Video der Convention (nach März 2027) → stärkster Content für 2028.

## Optional

- [ ] Test auf echtem iPhone und einem älteren Safari (15) – bisher Playwright
      WebKit und Simulation.
