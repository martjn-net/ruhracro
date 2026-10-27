# SEO

Stand 2026-10-27, geprüft lokal gegen Apache mit der echten `.htaccess`.
Lighthouse SEO: **100** auf allen geprüften Seiten (`/`, `/programm`,
`/tickets`, `/anreise`).

## Keyword-Recherche

Methode: Google-Autocomplete (DE) für Seed-Begriffe, 2026-10-27. Keine
Suchvolumen-Zahlen (kein Keyword-Planer-Zugang); „nur sich selbst als
Vorschlag“ = geringes Volumen.

| Seed | Vorschläge (Auszug) | Einschätzung |
|---|---|---|
| acroyoga | acroyoga essen, köln, münster, düsseldorf, bonn | **„acroyoga essen“ ist Top-Vorschlag** – Hauptziel Startseite |
| acroyoga festival | festivals 2026, festival 2025 deutschland, europe, berlin | jahresbezogen gesucht → „2027“ in Title |
| acroyoga convention / acro convention | convention 2026, acro convention bremen, münchen | Gattungsbegriff „Acro Convention“ im Namen gesetzt |
| akrobatik festival | akrobatik festival 2026, regensburg, karlsruhe | im Title der Startseite |
| akrobatik workshop | erwachsene, köln, **nrw**, in der nähe | „Workshops“ auf `/programm` |
| partnerakrobatik | anfänger, figuren, zu zweit | Begriff auf der Startseite |
| acroyoga anfänger | übungen, figuren, kurs, workshop | Absatz „Acroyoga für Anfänger und Fortgeschrittene“ |
| standing acro | standing acroyoga, standing acrobatics | Fokus-Karte Startseite |
| hochschulsport essen | kurse, programm, anmeldung | Veranstalter genannt, Buchung über buchsys |
| acroyoga ruhrgebiet / nrw / wochenende, hand to hand akrobatik | nur sich selbst | Long Tail, natürlich im Text |
| acroyoga 2027 | keine | Nachfrage entsteht erst 2026/27 |

**Zuordnung**

| Seite | Primär | Sekundär |
|---|---|---|
| `/` | acroyoga essen, acroyoga festival, acro convention | akrobatik festival, partnerakrobatik, acroyoga anfänger, ruhrgebiet |
| `/programm` | acroyoga workshop, akrobatik workshop | timetable, jam |
| `/tickets` | ruhr acro convention tickets | preise, hochschulsport ude |
| `/anreise` | sporthalle schützenbahn essen | campus essen |

Keine Keyword-Häufung: Begriffe stehen einmal natürlich in Lead-Absätzen und
Überschriften. Das JSON-LD-Feld `keywords` wurde entfernt (von Google
ignoriert).

## On-Page

| Prüfpunkt | Umsetzung | Test |
|---|---|---|
| `<title>` je Seite, ≤ 70 Zeichen, eindeutig | aus `PAGES` | ✔ |
| Meta-Description 50–160 Zeichen, eindeutig | aus `PAGES` | ✔ |
| genau eine `h1`, Hierarchie ohne Sprung | Programm-Tage als `h2` | ✔ |
| `lang="de"` | | ✔ |
| Canonical absolut auf `https://ruhracro.de` | | ✔ |
| Bilder mit `alt`, `width`, `height` | dekorative mit `alt=""` | ✔ |
| Inhalte als Text statt Bild | Timetable komplett als HTML-Liste | ✔ |
| interne Verlinkung | Navigation, „Programm ansehen“, „Tickets und Preise“, Footer | |
| Breadcrumbs | JSON-LD `BreadcrumbList` auf Programm, Tickets, Anreise | |

## Content-Qualität

- Alle Aussagen belegt ([inhalte.md](inhalte.md)); keine Platzhalter mehr
  („Programm und weitere Infos folgen“ entfernt).
- Seiten beantworten die Fragen der Suchenden: Wann, wo, für wen
  (Level), was (Inhalte, Zeitplan), was kostet es, wie hinkommen, was
  mitbringen/essen.
- Schwach: `/anreise` hat keine ÖPNV-Angaben (nicht verifiziert). Sobald es
  Teacher, Workshop-Titel je Block oder Fotos gibt, ist das der größte
  Content-Hebel (eigene Inhalte, die keine andere Seite hat).

## Struktur und Crawlbarkeit

| Prüfpunkt | Ergebnis |
|---|---|
| `robots.txt` | `Allow: /`, verweist auf Sitemap |
| `sitemap.xml` | 12 URLs (DE + EN), je mit `xhtml:link`-Alternativen, `lastmod` aus Dateidatum, aus `PAGES` generiert |
| saubere URLs | `/programm`, `/en/programme`; Trailing Slash und `/index.php` → 301, alte `.php`-URLs 404 (nie live gewesen) |
| Mehrsprachigkeit | `hreflang` de / en / x-default (= de) auf jeder Seite, `og:locale` + `og:locale:alternate`, `<html lang>`; keine automatische Sprachumleitung |
| Statuscodes | 200 / 301 / 404 geprüft (Apache) |
| 404-Seite | `noindex`, volle Navigation, Link zur Startseite |
| HTTPS + non-www | Regel vorbereitet, nach Zertifikat aktivieren ([deploy.md](deploy.md)) |
| kein JS nötig | alles serverseitig gerendert |
| Impressum, Datenschutz | indexierbar (übliche Praxis, schadet nicht) |

## Strukturierte Daten (JSON-LD)

Geprüft gegen [Googles Event-Richtlinien](https://developers.google.com/search/docs/appearance/structured-data/event)
und mit `structured-data-testing-tool` (Preset Google: bestanden).

**Startseite `/` und `/en`**: `Event` (gleiche `@id`, `inLanguage` je Sprache,
`offers.seller` = Hochschulsport UDE) + `WebSite`.

Englische Keywords wurden nicht separat recherchiert; die EN-Seiten zielen auf
„acroyoga festival germany“, „acro convention 2027“ und internationale
Acro-Kalender (siehe [backlink-strategie.md](backlink-strategie.md)).

| Feld | Wert | Google |
|---|---|---|
| `name` | Ruhr Acro Convention 2027 | Pflicht |
| `startDate` / `endDate` | 2027-03-05T16:00+01:00 / 2027-03-07T17:30+01:00 | Pflicht / empfohlen |
| `location` | `Place` mit Name, `PostalAddress`, `GeoCoordinates` | Pflicht |
| `eventStatus`, `eventAttendanceMode` | Scheduled, Offline | empfohlen |
| `description` | Inhalte und Level, ohne Datum/Ort zu wiederholen | empfohlen |
| `image` | 3 Formate 1:1, 4:3, 16:9, ≥ 1200 px breit | empfohlen |
| `offers` | **ein `Offer` je Tickettyp**: Early Bird 74 € `SoldOut`, Regular 79 € `InStock`; `price` = niedrigster Preis; Preisgruppen als `UnitPriceSpecification` | empfohlen |
| `organizer` | Hochschulsport der UDE + URL | empfohlen |
| `sameAs` | Instagram | |

**„Product“:** Google sieht für Veranstaltungstickets die `offers` des
`Event` vor (siehe Richtlinie oben). Ein separates `Product` bringt hier keinen
Rich-Result-Typ (verkauft wird auf buchsys, nicht auf dieser Seite) und würde
dieselben Preise doppelt pflegen; deshalb nicht verwendet.

**Nicht markiert (bewusst):**

- Workshop-Blöcke als Unter-Events: Google unterstützt nur ein Event pro Seite
  und rät von Markup auf Zeitplan-Seiten ab. Ein Test stellt sicher, dass nur `/`
  ein `Event` enthält.
- `performer`: Teacher noch nicht bekannt. Nachtragen, sobald veröffentlicht
  (`Person` je Teacher).

**Programm, Tickets, Anreise:** `BreadcrumbList`. Impressum, Datenschutz: kein
Markup.

## Social / Open Graph

Jede Seite: `og:type`, `og:locale` (de_DE), `og:site_name`, `og:title`,
`og:description`, `og:url`, `og:image` (`social-1200x630.jpg`, JPEG, mit
Breite/Höhe/Alt), `twitter:card=summary_large_image`. Das Share-Bild zeigt
das Original-Poster plus Datum, Ort, Claim.

## Nach dem Go-live

1. Search Console + Sitemap ([deploy.md](deploy.md)).
2. Rich-Results-Test für `/`.
3. Event-Daten aktuell halten: wenn Regular ausverkauft, `available => false`
   (sonst falsches `InStock` im Suchergebnis).
4. Backlinks ([backlink-strategie.md](backlink-strategie.md)).
