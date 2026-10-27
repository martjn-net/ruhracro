# Backlink-Analyse & -Strategie — ruhracro.de (Ruhr Acro Convention 2027)

Version 2 (Recherche vertieft), Stand 2026-10-27

## Ausgangslage

Die Seite für die Ruhr Acro Convention (5.–7. März 2027, Sporthalle an der Schützenbahn, Essen) ist technisch fertig unter `https://ruhracro.de`, aber **DNS ist noch nicht live** (Vermerk in `web/lib/event.php`). Die Domain hat daher vermutlich null Backlinks, null Indexierung und keine Search-Console-Historie — die Strategie ist ein Aufbau von null an.

Bestehende und geplante externe Berührungspunkte:

| Quelle | Status | Rolle für Links |
|---|---|---|
| `buchsys.de` (Buchungsportal Hochschulsport UDE) | aktiv | Verweist auf das Angebot, nicht auf ruhracro.de; Eintrag lässt sich um eigene URL ergänzen |
| Instagram `@ruhracroconvention` | aktiv | Bio-Link = einziger aktueller Websitesprung; kein follow-Link, aber Traffic und Markensignale |
| `uni-due.de/hochschulsport` | Veranstalter | Stärkster realistischer erster Backlink (Uni-Domainautorität); Kontaktdaten vorhanden |
| Presse/lokale Medien | nicht kontaktiert | Offen |

Technische Basis, die Backlinks haltbar macht, ist gegeben: saubere Canonicals und 301-Rewrites (`.htaccess`), generierte `sitemap.xml`, `robots.txt` mit Sitemap-Verweis, Event-JSON-LD mit Offers und Ort, Open-Graph-Image (1200×630). Die HTTPS-Weiterleitung ist vorbereitet, aber noch auskommentiert — **vor dem ersten Linkaufbau aktivieren**, sonst landen früh gesetzte Links auf `http://`-Redirectketten.

## Bewertung der Startposition

Stärken:
- **Event mit hartem Datum und Ort** — die natürliche Linkform für Festivals ist der Kalendereintrag. Die Zielkategorie („Acro-Festival Deutschland") besteht fast ausschließlich aus redaktionell gepflegten Kalendern und Community-Verzeichnissen mit niedriger Hürde.
- **Eigene Buchungsstrecke** (buchsys.de) und **kein Kommerz-Overlay** (keine Tracker, keine Werbung) — Community-Verzeichnisse und Blogs verlinken solche Seiten gern.
- **Hochwertige Original-Assets** (Retro-Poster 4:5/9:16, Social-Image, Zeitplan) — Blogs und Kalender betten Poster gern ein; jedes eingebettete Bild mit Quellenangabe ist ein potenzieller Link.
- **Veranstalter ist ein Unisport-Programm** — uni-interne Veranstaltungsseiten, Newsletter und Studierendenportale verlinken ohne kalte Akquise.

Schwächen:
- Keine Ranking-Historie der Domain; „Ruhr Acro Convention" ist ein Eigenbegriff ohne Suchvolumen — frühe Rankings laufen über **Brand-Suche und Long-Tail** („acro festival 2027", „acroyoga festival märz"), nicht über den Markennamen.
- Nur 6 Seiten, keine Content-Fläche (Blog/FAQ). Für den Eventzeitraum ausreichend; Backlinks müssen fast ausschließlich auf die Startseite zielen.
- Zeitfenster: ~4 Monate bis zum Event. Linkaufbau muss im November/Dezember anlaufen, nicht erst im Februar.

## Zielsuchanfragen und Ankertexte

Relevante Suchintentionen: „acroyoga festival 2027", „acro festival deutschland", „acro convention märz 2027", „akrobatik festival ruhrgebiet", „acroyoga essen workshop". Daraus ergeben sich natürliche Ankertexte — wichtig ist Variation, nie nur „Ruhr Acro Convention":

- Brand: „Ruhr Acro Convention"
- Brand + Ort/Termin: „Ruhr Acro Convention 2027 in Essen"
- Thematisch: „Acroyoga-Festival im Ruhrgebiet", „Acro-Convention im März"
- URL/„hier": Mischform für Verzeichnisseiten, die standardisieren

## Backlink-Ziele nach Kategorien

### 1. Acro-Szene-Kalender (höchste Relevanz, geringe Hürde)

Die Szene hat zentrale Festival-/Event-Kalender — genau dort sucht die Zielgruppe. Recherche-Ergebnisse zur Eintragsprozedur:

| Ziel | Eintragsform | Priorität |
|---|---|---|
| [AcroWorld — Acro-Festivals Deutschland](https://acroworld.net/events/festivals/germany) | Betreiberplattform mit Veranstalter-Konten („Follow teachers, book tickets"); Organizer-Konto anlegen, Festival als Event einreichen | 1 |
| [AcroWorld (deutsche Version)](https://acroworld.de/) | dieselbe Plattform, deutschsprachige Festival-/Convention-Liste | 1 |
| [AcroPassport — Events](https://acropassport.com/de/events) | Kein öffentliches Formular (JS-App); Eintrag per E-Mail an `hello@acropassport.com` anfragen; 3.700+ Events, aktives Verzeichnis | 1 |
| [European Acro Festivals — Kalender](https://acro-festivals.com/calendar) | SvelteKit-App ohne sichtbares Eintragsformular; Kontakt über Betreiber (Impressum/Kontakt route) suchen, ggf. Instagram | 2 |
| [AcroYoga Live — Communities](https://acroyoga.live/country/germany/) | Community-/Event-Eintrag; listet z. B. das Berlin AcroYoga Collective | 2 |
| [acro.map](https://acromap.info/de/acro-map-deutsch/) | Trainingsort-/Event-Meldung (Beispiel: [Acro Jam Köln](https://acromap.info/spot/free-acro-jam-eb-koeln/)) | 3 |
| [AcroYoga.org — Find](https://www.acroyoga.org/find?lang=de) | internationale Lehrer-/Event-Suche; Eintrag als Veranstlungsseite anstreben | 3 |

### 2. Community-Websites und -Verzeichnisse

- [Akrobatik-Wiki — Akrobatik-Gruppen in Essen](https://akrobatik.fandom.com/de/wiki/Akrobatik-Gruppen_in_Essen): verweist bereits auf den Hochschulsport; community-editierbar — Convention als Eintrag ergänzen (geringe Autorität, hohe thematische Präzision).
- [Acrologne — AkroYoga Köln](https://acrologne.com/) mit [Events-Seite](https://acrologne.com/events): persönlicher Kontakt über die Website, naheliegendste Community-Website im Ruhrgebiet-Nachbarraum.
- [LATIBUL Köln — AcroYoga Sunday Session](https://latibul.de/freizeit/freies-training/acro-yoga-sunday-session): etablierte Session-Seite; Ankündigung des Festivals anfragen.
- Stadt-Communities (Berlin AcroYoga Collective, Acroyoga Freiburg, Ruhrgebiets-Jams): über Instagram austauschen; Jams verlinken kommende Festivals in ihren Ankündigungen. 5–10 echte Kontakte aus dem eigenen Netzwerk, keine Massenmail.

### 3. Etablierte deutsche Acro-Festivals (Cross-Promotion)

Die großen Festivals betreiben eigene Terminkanäle und verlinken sich gegenseitig; sie sind zugleich Multiplikatoren in der Szene:

- [ACRONIA Festival](https://acronia.de/) (10 Tage, großes Programm) — Cross-Ankündigung über deren Kanäle anfragen
- [Acronyx Festivals](https://acronyx.org/) (Würzburg/Randersacker, Jahreswechsel)
- [Acro-Sense Festival](https://acro-sense.de/festival/) (Kassel, März-Termin — direkte Terminnachbarschaft)
- [acryo.de](https://www.acroyo.de/) mit [Termine-Seite](https://www.acroyo.de/termine/) (Acroyoga & Akrobatik, Festivals & Events)

Kein Linktausch im Sinne von „du mich, ich dich" erzwingen; eine Ankündigung beim jeweils anderen Festival ist in der Szene üblich und erzeugt natürliche Links.

### 4. Tourismus- und Event-Kalender (verifizierte Eingaben)

Der Ruhrgebiet-Tourismus betreibt eine zentrale Veranstaltungsdatenbank — ein Eintrag verteilt sich auf mehrere Portale:

| Ziel | Eintragsform | Besonderheit |
|---|---|---|
| [Visit Essen — Veranstaltungseingabe im DestinationHub.Ruhr](https://www.visitessen.de/essentourismus_veranstaltungen/infos_fuer_veranstalter/veranstaltungseingabeimeventmanager/veranstaltungskalender_eventmanager.de.html) | kostenloses Online-Formular, kurze Prüfung, dann Veröffentlichung | Der [DestinationHub.Ruhr](https://www.ruhr-tourismus.de/open-data-finder/) ist die zentrale Datenbank der Metropole Ruhr; Einträge erscheinen in mehreren Essener/Ruhr-Kalendern ([Beispiel](https://kurti-essen.de/erscheine-auch-im-essener-veranstaltungskalendern/)) |
| [Kulturinfo Ruhr — Veranstaltung anlegen](https://kulturinfo.ruhr/metanavigation-unten/veranstaltung-anlegen) | kostenloser Eintrag, ggf. vorher [Veranstaltungsstätte anlegen](https://kulturinfo.ruhr/metanavigation-unten/veranstaltungsstaette-anlegen) | Ruhr-Kulturportal mit eigener Reichweite |
| [Rheinruhraktuell — Veranstaltung eintragen](https://rheinruhraktuell.de/veranstaltung-eintragen/) | kostenloser Eintrag | Veröffentlichung auch in Partnermedien |
| [Eventfrog-Kalender](https://eventfrog.de/de/veranstalten/kalendereintrag.html) | kostenloser Kalendereintrag | nationale Reichweite |
| [meinestadt.de-Veranstaltungen](https://veranstaltungen.meinestadt.de/deutschland/veranstaltung-eintragen) | deutschlandweiter Eventkalender | Essen-Kategorie |
| [eventfinder.de](https://www.eventfinder.de/veranstaltungen-kostenlos-eintragen/) | klassischer Veranstaltungs-Eintrag | |
| [veranstaltungen-eintragen.de](https://www.veranstaltungen-eintragen.de/) | spartenübergreifender Kalender | |

### 5. Yoga-/Movement-Kontext (Publikum jenseits der Acro-Blase)

- [FindeDeinYoga.org](https://findedeinyoga.org/): listet bereits [Acro-Yoga-Angebote in Essen](https://findedeinyoga.org/yogakurs/essen/yogastil-acro-yoga) — Festival bei deren [Festival-Übersicht](https://findedeinyoga.org/blog/die-besten-yoga-festivals) bzw. als Veranstaltung anfragen.
- [YogaMeHome — Yoga-Veranstalter-Liste](https://www.yogamehome.org/yoga-veranstalter-liste): E-Mail-Formular für Festivals.
- [das-yoga-magazin.de — Yoga-Veranstaltungen](https://www.das-yoga-magazin.de/finde-yoga-veranstaltungen-in-deiner-nahe.html): redaktioneller Pitch mit Pressetext + Poster.
- Studios im Ruhrgebiet (z. B. Yoganova Essen, siehe [YogaEasy-Studioübersicht Ruhrpott](https://www.yogaeasy.de/artikel/yoga-im-ruhrpott-sonnengruss-statt-kohlenruss)): Aushang + Link als „Empfehlung für Fortgeschrittene"; Aushänge im Studio führen fast immer auch zu einem Link auf der Studio-Website.
- [citysports.de — Akrobatik Essen](https://www.citysports.de/nordrhein-westfalen/essen/sportart/akrobatik/): Anbieter-/Eventverzeichnis; Eintrag ergänzen.

Nicht verfolgen: ProntoPro-, Cylex- und Gelbe-Seiten-Verzeichnisse — reine Firmenverzeichnisse ohne Event-Publikum, Aufwand steht in keinem Verhältnis.

### 6. Hochschule/Veranstalter-Umfeld (beste Autorität)

- `uni-due.de/hochschulsport`: Der Hochschulsport publiziert Event-Neuigkeiten auf einer eigenen [Neuigkeiten-Seite](https://www.uni-due.de/hochschulsport/neuigkeiten.php) — dort ist ein Eintrag mit Link auf ruhracro.de der erste Backlink mit hoher Domain-Autorität. Kontaktdaten über das eigene Programm vorhanden.
- Die UDE betreibt zudem einen zentralen [Veranstaltungskalender](https://www.uni-due.de/de/veranstaltungen/) und ein [Ressort Zentrale Veranstaltungen](https://www.uni-due.de/de/event/) — Eintrag bzw. Aufnahme anfragen.
- Angrenzende Hochschulen (HRW, FWU) sind in den Ticketpreisgruppen benannt — auch dort Newsletter/Aushang erbeten.

### 7. Lokalpresse und Lokalradio

- [WAZ Essen](https://www.waz.de/lokales/essen/): veröffentlicht eine [Jahresübersicht der Events in Essen](https://www.waz.de/lokales/essen/article411005017/events-und-veranstaltungen-in-essen-2026-die-grosse-uebersicht.html) — Aufnahme über die [Redaktionskontakte](https://www.waz.de/service/impressum/) anfragen.
- [Radio Essen — Veranstaltungstipps](https://www.radioessen.de/service/veranstaltungstipps): Tipp einreichen; kostenloses Radioformat mit Web-Auftritt.
- Kurzpressemitteilung mit Poster, Datum, Ort und Verweis auf die Preisspanne ab 79 € (Hochschulsport) — lokale Redaktionen greifen Sport-Community-Events gern auf.

## Priorisierung

| Prio | Aktionen | Aufwand | Wirkung |
|---|---|---|---|
| 1 | HTTPS aktivieren, Search Console anlegen, Uni-Seiten (Neuigkeiten + Veranstalterprofil), Visit-Essen/DestinationHub, Acro-Szene-Kalender (Kategorie 1) | gering | Referenz-Backlinks + Sichtbarkeit in der Szene |
| 2 | Kulturinfo Ruhr, übrige Event-Kalender, Yoga-Verzeichnisse, Community-Kontakte, Akrobatik-Wiki | gering bis mittel | Volumen, Traffic, Long-Tail |
| 3 | Presse-/Redaktionspitches (WAZ, Radio Essen, Yoga-Magazine), Studios, Festival-Cross-Promotion | mittel | Autorität, reichweitenstarke Erwähnungen |
| 4 | Sonstige (acro.map, acroyoga.org, veran.st u. ä.) | gering | Ergänzung |

Reihenfolge begründet: Szene-Kalender und DestinationHub kommen aus der echten Zielgruppe bzw. füttern mehrere Portale mit einem Aufwand; sie erzeugen Bookmarks, Weiterempfehlungen und Conversions — ein Verzeichnislink ohne Publikumsnähe bringt nur einen authorityneutralen Eintrag.

## Linkfähige Assets

Die Seite selbst bietet bereits Anlässe für organische Links — nicht erfinden, nur deutlich machen:

- **Retro-Poster (4:5, 9:16, Social-1200×630)**: Blogs und Presse betten es ein; im Event-Eintrag als „Poster: ruhracro.de" benennen, mit Quellenangabe anbieten.
- **Zeitplan** (`/programm`): Redaktionelle Eventkalender verlinken die Programmseite, wenn ein solider Zeitplan existiert — attraktives SEO-Asset für „acroyoga festival programm" u. ä.
- **Anreise** (`/anreise`): eigene, saubere Anreise-Anleitung, verknüpfbar von Community-Foren und Messenger-Gruppen (Verweis von Jam-Organisatoren an Teilnehmer).
- **Termin im Eintrag**: Datum/Ort direkt im AcroWorld-/AcroPassport-/Eventfrog-Eintrag pflegen (der `.ics`-Download wurde von der Seite entfernt).
- Das „Move together. Grow together"-Motto und der Instagram-Handle sind Share-Anlässe, aber keine Backlink-Quelle (Nofollow).

## Maßnahmenplan

| Zeitraum | Aktion |
|---|---|
| Woche 1 | HTTPS-Weiterleitung + `Strict-Transport-Security` aktivieren; Google Search Console und Bing Webmaster verifizieren; Sitemap einreichen |
| Woche 1 | Hochschulsport UDE: Eintrag auf der Neuigkeiten-Seite + Veranstalterprofil mit Link auf ruhracro.de |
| Woche 2 | Visit Essen/DestinationHub.Ruhr-Eintrag (füttert mehrere Portale), Kulturinfo Ruhr, Rheinruhraktuell |
| Woche 2–3 | AcroWorld (Organizer-Konto), AcroPassport (E-Mail), European Acro Festivals (Kontakt), übrige Event-Kalender |
| Woche 3–4 | YogaMeHome, FindeDeinYoga, Community-Kontakte (5–10) über Instagram, Akrobatik-Wiki, Aushänge für Ruhrgebiets-Studios |
| Monat 2 (≈ Dezember) | Pressemitteilung an WAZ Essen/Radio Essen mit Poster-Bildern; Festival-Cross-Promotion (ACRONIA, Acro-Sense, acryo.de) |
| Monat 2–3 | Search Console prüfen: indexierte Seiten, erste verweisende Domains, fehlende Einträge nachfassen |
| Februar/März 2027 | Erinnerungs-Posts; Kalendereinträge um „Tickets ab 79 €/Fast ausverkauft"-Status ergänzen |

## Erfolgsmessung

- **Google Search Console**: verweisende Domains (Ziel: [__] bis Jahreswechsel, [__] bis Februar 2027 — Werte nach 4 Wochen Erfahrung setzen), Impressionen/Klicks auf Brand- und Long-Tail-Anfragen, Indexstatus der 6 URLs.
- **Referral-Traffic** aus Server-Logauswertungen, Instagram-Bio-Klicks.
- **Conversions**: Buchungszahlen über buchsys.de (Referenzgröße) im Verhältnis zu Referral-Spitzen nach Kalendereinträgen — ein Eintrag in AcroWorld ist ein Erfolg, wenn er Verweise und Buchungen erzeugt, nicht nur einen Link.
- Kontrollrhythmus: monatlich Search Console, kurz nach jeder Eintrags-/Pitch-Welle (1 Woche später).

## Risiken und No-Gos

- **Kein Linkkauf, keine Linkmietnetze, keine massenhaften Webkataloge** — Kurzatmigkeit, Schadenspotenzial, unnötig für ein Event mit natürlichem Linkanlass.
- **Keine Ankertext-Optimierung mit immer demselben Keyword** — jede Quelle soll natürlich formulieren (s. Zielsuchanfragen).
- **Keine irrelevanten Firmenverzeichnisse** (ProntoPro, Cylex, Gelbe Seiten): kein Event-Publikum, Aufwand ohne Echo.
- **Keine Über-Pitching-Massenmails an Communities** — 5–10 echte Kontakte schlagen 50 Formulare.
- **Nach dem Event**: 301 von ruhracro.de nicht aufgeben — Folgeworkshops/2028er-Auflage sollen auf der etablierten Domain weiterlaufen; sonst verfällt der gesamte Linkaufbau.

## Offene Punkte

- Eintragsprozedur bei [European Acro Festivals](https://acro-festivals.com/calendar): App ohne sichtbares Formular; Kontaktweg beim Betreiber klären (Lücke).
- Domain-Autoritätswerte (DR/DA) der Ziele lassen sich ohne Tool-Zugang (Ahrefs/Sistrix) nicht beziffern; erste verweisende Domains nach Go-Live über die Search Console erheben (Lücke, Zahlen `[__]` dann nachtragen).

## Ergänzende Quellen (zweite Recherche, 2026-10-27)

Nicht oben enthalten, gleiche Priorisierungslogik:

| Kategorie | Ziel |
|---|---|
| 1 Acro-Kalender | [acrocalendar.com](https://acrocalendar.com), [StayAcro Events](https://stayacro.com/en/events), [AcroYoga World Events](https://acroyogaworld.fr/events), [Acro Insiders](https://acroinsiders.com), [Jacob Brown Acro Calendar](https://jacobbrownacro.com/acro-calendar), [AcroPassport Community Germany](https://acropassport.com/communities/germany) |
| 2 Community | [Die Wunderliste](https://die-wunderliste.de), [Hochschulsport Bochum – Acroyoga](https://hochschulsport-bochum.ruhr-uni-bochum.de), Facebook-Gruppen AcroYoga Dortmund (ID 334001526791842) und Bochum (ID 850650738384031), Instagram `@acroyoga_essen`, [lets-meet.org „Ruhr acts!“](https://lets-meet.org) |
| 4 Event-Kalender | [venyoo.de](https://www.venyoo.de), [aboutix](https://www.aboutix.com) |
