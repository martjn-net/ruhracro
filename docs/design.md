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

**Die Poster-Szene rahmt jede Seite.** Oben die Szene mit Sonne, Skyline und
Akrobaten (ohne Schrift), unten die Pflanzen; beide laufen nahtlos ins
Nachtgrün, seitlich mit unscharfem Farbüberlauf. Dazwischen beliebig langer
Text. Der Header ist höchstens halb so hoch wie das Fenster und wird dafür
gleichmäßig verkleinert, nie beschnitten oder verzerrt; die sticky Navigation
sitzt direkt darüber. Titel, Ort und Datum stehen auf der Startseite als Text
unter dem Header. Bildaufbereitung: [build.md](build.md).

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

Schrift: **Kalam 700** für Titel, Ort, Datum, Überschriften; **Nunito**
(variabel) für Text und Buttons. Beide selbst gehostet.

## Seiten und Navigation

| Seite (DE / EN) | Inhalt |
|---|---|
| `/` · `/en` | Titel, Untertitel, Ort und Datum, Halle, Buttons, Zeile „Veranstalter: Hochschulsport …“; Festival, Angebot, Level, Inhalte, Fokus Standing Acro/Acroyoga |
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
- Footer jeder Seite: Lotus, Claim, Instagram, **Veranstalter** (Hochschulsport
  UDE, Link), **Organisation und Website** (Name aus `provider.php`), Satz
  „Diese Website informiert nur und verkauft nichts …“, Impressum, Datenschutz;
  darunter das Pflanzen-Band, dessen dunkle Oberkante unter die letzten Zeilen
  läuft.

## Layout

```
┌─────────────── Navigation (sticky) ───────────────┐
│ ░░ Unschärfe ░░ │  Header-Band: Szene  │ ░░ Unschärfe ░░ │  ≤ 50 % Fensterhöhe
├───────────────────────────────────────────────────┤
│            Inhalt auf Nachtgrün (beliebig lang)    │
├───────────────────────────────────────────────────┤
│            Footer-Text                             │
│ ░░ Unschärfe ░░ │  Footer-Band: Pflanzen │ ░░ ░░ │  steht bei wenig Inhalt unten
└───────────────────────────────────────────────────┘
```

Spalte beider Bänder: `min(100 % − 2 × min(200px, 14 %), 50vh × Seitenverhältnis)`;
die seitlichen Ränder füllt der eingebackene Farbüberlauf. Startseite:
Titel in Kalam, Untertitel in Versalien wie auf dem Poster, Ort · Datum in
Kalam, darunter Tage und Halle, „Jetzt buchen“ und „Programm ansehen“.

## Regeln

- **Keine erfundenen Inhalte:** nur Fakten aus [inhalte.md](inhalte.md).
- **Keine generischen Tells:** keine ALL-CAPS-Eyebrows (Ausnahme: der
  Untertitel, so gesetzt wie auf dem Poster), keine „A · B · C“-Metazeilen,
  kein „→“ an Buttons, keine Emojis als Icons.
- **CTA sagt, was passiert:** „Jetzt buchen“ (buchsys, neuer Tab), „Route
  planen“ (OpenStreetMap, neuer Tab), „Programm ansehen“.
- **Keine Drittanbieter-Ladevorgänge:** nur eigene Dateien. Externe Ziele sind
  normale Links mit `target="_blank" rel="noopener"` und versteckt „(öffnet in
  neuem Tab)“.
- **Qualitätsboden:** Kontrast ≥ 4,5:1 (Text) bzw. ≥ 3:1 (UI), sichtbarer
  Fokus, `prefers-reduced-motion`, Tap-Ziele ≥ 44 px, kein Horizontal-Scroll
  von 320 bis 1920 px, feste `width/height` gegen Layout-Shift, Überschriften
  ohne Sprung.
- **Motion:** keine Animation außer dem Button-Übergang (aus bei
  `prefers-reduced-motion`).
