# Impressum, Datenschutz, Rollen

Stand 2026-10-27. Keine Rechtsberatung – vor dem Go-live prüfen lassen
([TODO.md](../TODO.md)).

## Rollen (so steht es auf der Website)

| Rolle | Wer | Wo sichtbar |
|---|---|---|
| **Veranstalter** der Convention | Hochschulsport der Universität Duisburg-Essen | Footer jeder Seite, Hero der Startseite, Tickets, Impressum, JSON-LD (`organizer`, `offers.seller`) |
| **Organisator** und **Betreiber der Website** | der Owner (Name nur in `web/lib/provider.php`, nicht im Repo) | Footer jeder Seite, Impressum, Datenschutz (Verantwortlicher) |
| **Ticketverkauf** | ausschließlich der Hochschulsport über buchsys.de | Tickets („Verkauf nur über die Universität“), Footer, Impressum („Kein Verkauf über diese Website“) |

Die Website verkauft nichts, schließt keine Verträge und leitet zur Buchung nur
per Link weiter. Tests sichern, dass Footer, Tickets und Impressum das auf
jeder Seite in beiden Sprachen sagen.

## Impressum: ja

Die Website bewirbt eine kostenpflichtige Veranstaltung und ist damit ein
geschäftsmäßiges digitales Angebot → Impressumspflicht nach § 5 DDG. Inhalt:

- Name und ladungsfähige Anschrift (kein Postfach)
- E-Mail und ein zweiter schneller Kontaktweg; Telefon wird empfohlen
  (`phone` in `provider.php`, erscheint nur, wenn gesetzt)
- USt-IdNr., falls vorhanden (`vat_id`), da gewerblich
- Klarstellung Veranstalter / Betreiber / kein Verkauf

## Datenschutzerklärung: ja, aber kurz

„Die Website verarbeitet keine Daten“ stimmt nicht ganz: Jeder Seitenabruf
übermittelt die IP-Adresse an den Webserver, und Hetzner schreibt sie in
Logdateien. IP-Adressen sind personenbezogene Daten (EuGH C-582/14). Damit
gilt die Informationspflicht nach Art. 13 DSGVO für den Betreiber.

Was die Website **nicht** tut und deshalb nicht erklärt werden muss: Cookies,
Formulare, Tracking, Analyse, eingebettete Inhalte Dritter (Karten, Videos,
Social Plugins, Google Fonts). Die Erklärung ist entsprechend kurz:

1. Kurzfassung (keine eigene Datenerhebung)
2. Verantwortlicher
3. Server-Logs: welche Daten, Zweck, Rechtsgrundlage Art. 6 Abs. 1 lit. f,
   Hoster Hetzner als Auftragsverarbeiter (Art. 28), **Speicherdauer**
4. Externe Links (erst Klick überträgt Daten; Buchung verarbeitet der
   Hochschulsport)
5. Betroffenenrechte und Beschwerderecht

Offen: Die Speicherdauer der Logs steht nicht fest. Bis sie in konsoleH
geprüft und in `HOSTING['log_retention']` (`web/lib/event.php`) eingetragen
ist, zeigt die Seite einen Platzhalter und `run_tests.php --prelaunch` schlägt
fehl. Den Auftragsverarbeitungsvertrag mit Hetzner in konsoleH abschließen.

## Englische Fassung

`/en/legal-notice` und `/en/privacy` sind Übersetzungen mit dem Hinweis, dass
die deutsche Fassung verbindlich ist (Link darauf).
