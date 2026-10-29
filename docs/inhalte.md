# Inhalte und Faktenquellen

Jede Aussage auf der Website stammt aus einer der drei Quellen unten. Nichts
wird ergänzt, geschätzt oder „schöner formuliert“, als die Quelle es hergibt.
Alle Fakten stehen an genau einer Stelle: `web/lib/event.php`.

| Quelle | Inhalt | Stand |
|---|---|---|
| Poster `quelle/ruhr-acro-convention-2027.jpg` | Name, Untertitel, Datum, Ort, Halle, „Für alle Level“, Icons | 2026-10-27 |
| Timetable `exporte/timetable.jpeg` | kompletter Zeitplan Fr–So (30 Slots, 8 Workshop-Blöcke) | 2026-10-27 |
| buchsys-Seite des Hochschulsports UDE ([Link](https://www.buchsys.de/due/angebote/aktueller_zeitraum_1/_AcroYoga__und__Akrobatik_Festival.html)) | Tickets, Preise, Preisgruppen, geplante Inhalte, Verpflegung, Helfer, Shirt, Longen | 2026-10-27 |

Vom Owner bestätigt (Chat 2026-10-27): Domain **ruhracro.de**, Veranstalter
**Hochschulsport der Universität Duisburg-Essen**, Claim **„Move together. Grow
together“** (ohne Punkt), Instagram **@ruhracroconvention**.

## Daten in `web/lib/event.php`

| Konstante | Inhalt |
|---|---|
| `SITE_URL`, `PREVIEW` | aus der Deploy-Konfiguration; Standard `https://ruhracro.de`, keine Vorschau ([deploy.md](deploy.md)) |
| `EVENT` | Marke, Name, Claim, Start `2027-03-05T16:00+01:00`, Ende `2027-03-07T17:30+01:00`, Buchungs-URL, Instagram, Veranstalter (DE/EN). Angezeigtes Datum („5.–7. März 2027“) entsteht aus Start/Ende über `event_dates()` in `render.php` – nur Start/Ende pflegen |
| `HOSTING` | Hetzner als Auftragsverarbeiter, Log-Speicherdauer 7 Tage, `confirmed` = AV-Vertrag und Löschregel in konsoleH erledigt ([recht.md](recht.md)) |
| `PROVIDER` | Betreiber der Website und Organisator: Impressum, Datenschutz, Footer; aus `web/lib/provider.php` (nicht im Repo, siehe [deploy.md](deploy.md)) |
| `VENUE` | Sporthalle an der Schützenbahn, Schützenbahn 70, 45127 Essen, Eingang Waldthausenstraße, Gebäude S-M; Koordinaten 51.46198 / 7.01754 (Halleneingang) |
| `TICKETS` | Early Bird 74/89/105 € (**ausverkauft**), Regular 79/99/119 € (buchbar) |
| `PRICE_GROUPS` | 3 Preisgruppen laut buchsys |
| `PROGRAM` | Timetable, je Slot `[von, bis, Art, Block]`; Beschriftung DE/EN aus `SLOT_LABELS` |
| `PAGES` | je Seite und Sprache: Pfad, Navi-Label, `<title>`, Description; speist Routing, Toggle, hreflang, Sitemap |

Übersetzbare Werte stehen als `['de' => …, 'en' => …]` direkt beim Fakt. Die
englischen Texte sind Übersetzungen der belegten deutschen Aussagen, keine
neuen Inhalte. Rollen: Veranstalter und einziger Verkäufer ist der
Hochschulsport der UDE; die Website gehört dem Organisator und verkauft nichts
([recht.md](recht.md)).

## Timetable (aus `exporte/timetable.jpeg`)

| Tag | Zeit | Programmpunkt |
|---|---|---|
| Fr 5.3. | vor 16:00 | Aufbau (Helfende vor Ort) |
| | 16:00–17:20 | Check-in und Ankommen |
| | 17:20–17:50 | Welcome Circle und Begrüßung |
| | 17:50–18:00 | Demos der Workshops von Block 1 |
| | 18:00–20:00 | Workshop Block 1 |
| | 20:00–22:00 | Abendpause, Jam und freies Training |
| Sa 6.3. | 09:10–09:50 | Check-in |
| | 09:50–10:00 | Demos Block 2 |
| | 10:00–11:00 | Workshop Block 2 |
| | 11:00–11:20 | Pause |
| | 11:20–11:30 | Demos Block 3 |
| | 11:30–13:30 | Workshop Block 3 |
| | 13:30–14:50 | Mittagspause mit Jam |
| | 14:50–15:00 | Demos Block 4 |
| | 15:00–17:00 | Workshop Block 4 |
| | 17:00–17:20 | Pause |
| | 17:20–17:30 | Demos Block 5 |
| | 17:30–19:30 | Workshop Block 5 |
| | 19:30–23:00 | Abendpause mit Jam |
| So 7.3. | 09:20–09:50 | Check-in |
| | 09:50–10:00 | Demos Block 6 |
| | 10:00–11:00 | Workshop Block 6 |
| | 11:00–11:20 | Pause |
| | 11:20–11:30 | Demos Block 7 |
| | 11:30–13:30 | Workshop Block 7 |
| | 13:30–14:50 | Mittagspause mit Jam |
| | 14:50–15:00 | Demos Block 8 |
| | 15:00–17:00 | Workshop Block 8 |
| | 17:00–17:30 | Closing Circle |
| | ab 17:30 | Abbau |

Der Test prüft, dass jeder Slot dort endet, wo der nächste beginnt, und dass
alle 30 Slots als Text auf `/programm` stehen.

## Von buchsys übernommen (wörtlich sinngemäß)

- Geplante Inhalte: Standing Acro, Acroyoga, Hand to Hand, Foot to Hand, Pops,
  Whips, Icarians, Flows.
- Mehrere Workshops parallel; Level bei der Anmeldung angeben; kein fester
  Trainingspartner nötig; Einsteiger-Angebote, ohne Vorerfahrung empfiehlt
  sich ein Workshop vorab.
- Longen für fortgeschrittenere Elemente.
- Verpflegung: Snacks das ganze Wochenende; eigenes Essen mitbringen möglich,
  Essbereich vor Ort; Restaurants, Imbisse, Einkauf in der Umgebung.
- Helferschicht: 30 € zurück nach der Schicht. Workshopleitende: ein Drittel
  des Ticketpreises zurück.
- Convention-Shirt oder Sport-BH bei der Anmeldung bestellbar (15–20 €),
  Ausgabe vor Ort.

## Bewusst nicht auf der Seite

- **ÖPNV-Haltestellen:** nicht verifiziert (Overpass-API nicht erreichbar).
  Nur mit geprüfter Quelle ergänzen (z. B. Ruhrbahn-Fahrplanauskunft).
- **Teacher-Namen, Workshop-Titel je Block:** noch nicht veröffentlicht.
- **Personen von buchsys** („verantwortlich“, „Leitung“): personenbezogen,
  für die Seite nicht nötig.

## Pflege

Ändert sich ein Fakt (z. B. Regular ausverkauft, neuer Zeitplan):

1. Nur `web/lib/event.php` ändern (z. B. `'available' => false`).
2. `php web/tests/run_tests.php` ausführen.
3. Stand-Datum in dieser Datei und im Kommentar über `TICKETS` aktualisieren.
4. Geänderte Dateien hochladen ([deploy.md](deploy.md)).
