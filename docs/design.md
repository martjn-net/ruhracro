# Design

Gilt für alle Seiten unter `web/public/` und alle Assets. Assets entstehen nur
per Pipeline ([build.md](build.md)).

## Auftrag

- **Subjekt:** Ruhr Acro Convention 2027, Acroyoga & Akrobatik Festival,
  5.–7. März 2027 (Fr–So), Sporthalle an der Schützenbahn, 45127 Essen.
- **Publikum:** Acro-Szene vom Einsteiger bis zum Profi, mobil zuerst.
- **Job der Seite:** Termin, Ort, Programm und Charakter klar machen und zur
  Buchung bei buchsys führen.
- **Claim:** „Move together. Grow together“ (final, ohne Punkt).

## Leitidee

Zwei Designs, Entscheidung offen ([architektur.md](architektur.md#zwei-designs)):

- **`classic` – das Poster ist die Startseite.** Das Info-Panel des Posters
  ist zu flachem Nachtgrün ausgemalt; die Seite setzt dieses Grün nahtlos
  fort. Ort und Datum stehen als echter Text auf den leeren
  Original-Pinselstrichen. Unterseiten sind ruhige Textseiten im selben
  Farbraum.
- **`scene` – die Poster-Szene rahmt jede Seite.** Oben die Szene mit Sonne,
  Skyline und Akrobaten (ohne Schrift), unten die Pflanzen; beide laufen
  nahtlos ins Nachtgrün, seitlich mit unscharfem Farbüberlauf. Dazwischen
  beliebig langer Text. Der Header ist höchstens halb so hoch wie das Fenster
  und wird dafür gleichmäßig verkleinert, nie beschnitten oder verzerrt; die
  sticky Navigation sitzt direkt darüber. Titel, Ort und Datum stehen auf der
  Startseite als Text unter dem Header. Bildaufbereitung: [build.md](build.md).

## Tokens (aus Poster und Exporten gemessen)

| Name | Hex | Rolle |
|---|---|---|
| Nachtgrün | `#001913` | Seitengrund (= Poster-Panel exakt) |
| Nachtgrün erhöht | `#062319` | Karten |
| Himmel | `#FDF8EC` | Haupttext |
| Text gedämpft | `#C9D3C6` | Fließtext (11,9:1) |
| Sonnengold | `#FDC671` | Überschriften, Primär-Button, Fokusring |
| Tinte | `#010D0A` | Text auf Gold |
| Level 1–4 | `#8CC08D` `#F4BB4B` `#E08D5B` `#E0676B` | Level-Balken (alle ≥ 5,5:1) |

Farben stehen nur in `site.css` (Klassen `accent-*`, `.meter[data-level]`),
nie in `style=""`: Die CSP (`style-src 'self'`) blockiert Inline-Styles; ein
Test erzwingt das.

Schrift: Logo bleibt Teil der Illustration (`h1` visuell versteckt).
**Kalam 700** für Ort, Datum, Überschriften; **Nunito** (variabel) für Text und
Buttons. Beide selbst gehostet.

## Seiten und Navigation

| Seite (DE / EN) | Inhalt |
|---|---|
| `/` · `/en` | Poster-Hero mit Zeile „Veranstalter: Hochschulsport …“, Festival, Angebot, Level, Inhalte, Fokus Standing Acro/Acroyoga |
| `/programm` · `/en/programme` | kompletter Timetable als Text, je Tag eine `h2` |
| `/tickets` · `/en/tickets` | Hinweis „Verkauf nur über die Universität“, Early Bird (ausgebucht), Regular, Preisgruppen, Helfer, Shirt, Verpflegung |
| `/anreise` · `/en/directions` | Adresse, Eingang, „Route planen“ (OpenStreetMap), Hinweise fürs Wochenende |
| `/impressum` · `/en/legal-notice`, `/datenschutz` · `/en/privacy` | Pflichtangaben; EN als Übersetzung, DE verbindlich |

Navigation dauerhaft sichtbar (sticky): Marke, Programm, Tickets, Anreise,
Impressum, Datenschutz, **Flaggen-Toggle** (Deutschland- und UK-Flagge als
SVG, aktive Sprache hell hinterlegt, Desktop mit „DE“/„EN“), Button „Jetzt buchen“.

- Unter 52rem rutschen die Links in eine zweite Zeile (`--nav-h: 6.25rem`);
  Toggle und Button bleiben in Zeile 1, nur Flaggen ohne Kürzel.
- Unter 27rem werden Impressum und Datenschutz zu „Rechtliches“ / „Legal“
  (Link aufs Impressum); Datenschutz bleibt im Footer erreichbar.
- Unter 24rem engerer Abstand, unter 22,4rem (< 360 px) nur die Flagge der
  anderen Sprache. Geprüft 320–430 px: Marke, Toggle, Button immer in einer Zeile.
- Footer jeder Seite: Instagram, **Veranstalter** (Hochschulsport UDE, Link),
  **Organisation und Website** (Name aus `provider.php`), Satz „Diese Website
  informiert nur und verkauft nichts …“, Impressum, Datenschutz.

## Layout Startseite

```
Desktop/Querformat (≥ 48rem, Seitenverhältnis ≥ 1:1, Höhe ≥ 34rem)
┌──────── Poster 4:5 (sticky ab 46rem Höhe) ────────┬──── Inhalt ─────────────┐
│  Logo · Sonne · Akro · Skyline · Wasser            │ Drei Tage Acro …        │
│  [Essen]  [5.–7. März 2027]   ← Text auf Pinsel    │ Workshops/Community/Jams│
│  Freitag bis Sonntag · Halle                       │ Für alle Level          │
│  (Jetzt buchen)  Programm ansehen                  │ Fokus, Footer           │
└────────────────────────────────────────────────────┴─────────────────────────┘
Tablet hochkant: Poster 4:5 volle Breite, Inhalt darunter.
Phone (≤ 3:5): eigenes 9:16-Artwork, Buttons gestapelt.
```

Overlay-Geometrie in `cqw` der Poster-Box (Poster-y ÷ Poster-Breite):

| Artwork | `--place-y` | `--date-y` | `--stage-y` |
|---|---|---|---|
| 4:5 | 78.33 | 85.27 | 89.45 |
| 9:16 | 109.56 | 118.4 | 123.3 |

Fallback ohne Container-Query-Einheiten (Safari < 16, Chrome < 105): dieselben
Werte in `vw`, Poster immer volle Breite (kein Split-Layout). Geprüft per
Simulation: Ortstext bei 390/820/1440 px auf 61,6/62,7/62,7 % – identisch zur
modernen Darstellung.

## Regeln

- **Keine erfundenen Inhalte:** nur Fakten aus [inhalte.md](inhalte.md).
- **Keine generischen Tells:** keine ALL-CAPS-Eyebrows, keine „A · B · C“-
  Metazeilen, kein „→“ an Buttons, keine Emojis als Icons.
- **CTA sagt, was passiert:** „Jetzt buchen“ (buchsys, neuer Tab), „Route
  planen“ (OpenStreetMap, neuer Tab), „Programm ansehen“.
- **Keine Drittanbieter-Ladevorgänge:** nur eigene Dateien. Externe Ziele sind
  normale Links mit `target="_blank" rel="noopener"` und versteckt „(öffnet in
  neuem Tab)“.
- **Qualitätsboden:** Kontrast ≥ 4,5:1 (Text) bzw. ≥ 3:1 (UI), sichtbarer
  Fokus, `prefers-reduced-motion`, Tap-Ziele ≥ 44 px, kein Horizontal-Scroll
  von 320 bis 1920 px, feste `width/height` gegen Layout-Shift, Überschriften
  ohne Sprung.
- **Motion:** genau ein Moment – Ort und Datum werden auf die Pinsel „gemalt“.
