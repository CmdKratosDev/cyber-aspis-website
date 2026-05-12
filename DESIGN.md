# Cyber-Aspis — Design-Konvention

> Single Source of Truth für das visuelle Erscheinungsbild von Cyber-Aspis (Website, Toolkit-Frontend, Reports, Slides). Extrahiert aus `index.html`. Updates an Brand-Tokens IMMER hier zuerst, dann in den Implementations propagieren.

## Brand-Identität

**Tonalität:** Cyberpunk-Modern, technisch-präzise, KMU-zugänglich. Kein Hacker-Klischee (kein Matrix-Grün, kein Skull-Logo), sondern **Defensive Tech mit Neon-Akzenten** — vermittelt Kompetenz + Modernität ohne Bedrohlichkeit.

**Sprache:** Deutsch (Sie-Form auf Marketing, Du-Form intern). Tech-Begriffe Englisch (Pentest, Audit, Scope). Keine Übersetzungen für etablierte Begriffe.

**Visueller Hook:** Dunkles Indigo-Schwarz + Cyan/Magenta-Neon-Akzente + Orbitron-Display-Font.

---

## Surface-Profiles — Übersicht

Cyber-Aspis hat historisch drei eigenständige visuelle Systeme, die parallel produktiv sind. Sie teilen die **Defensive-Cyber-Tonalität**, divergieren aber in Fonts und Farb-Nuancen weil jede Surface andere Render-/Lese-Anforderungen hat. **Diese Datei dokumentiert alle drei — keine wird "vereinheitlicht", existierende Designs bleiben so wie sie sind.**

| Profile | Quelle | Fonts | Renderer | Format |
|---------|--------|-------|----------|--------|
| **A — Web** (Source-of-Truth dieser Datei) | `cyber-aspis-website/index.html` | Orbitron · Exo 2 · Space Mono | Browser | responsive Web |
| **B — Social-Cards** (LinkedIn-Carousels) | `Claude-cowork/Cyber-Aspis/cowork-pipeline/templates/` | Bebas Neue · Manrope · JetBrains Mono | WeasyPrint (PDF→PNG) | 1080×1350 portrait |
| **C — PDF-Reports** (Audit-Output) | `Cyber-Aspis/toolkit/backend/templates/report_*.html.j2` | Helvetica Neue · Courier New | WeasyPrint (PDF) | A4 (210×297mm) |

**Wann welches Profile verwenden:**
- Web-Komponente, Landing-Sektion, Web-App-UI → **Profile A**
- LinkedIn-Carousel, Instagram-Post, Social-Card → **Profile B**
- Audit-Bericht, Quick-Check-Report, Investor-Dokument → **Profile C**

---

## PROFILE A — Web

> Tokens unten gelten ausschließlich für Web-Surfaces. Für Social/Reports → Profile B/C unten.

## Farb-Token

### Backgrounds (dark theme, primary)

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--bg-primary` | `#0d0d1a` | Page-Background, Hero, Services, Trust, Contact |
| `--bg-secondary` | `#12082e` | About, Process — kontrastiver Sektions-Wechsel |
| `--bg-card` | `#1a1a35` | Service-Cards, Feature-Boxes |

### Text

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--text-primary` | `#ffffff` | Headlines, Body-Text auf dark |
| `--text-muted` | `#9090b8` | Sekundär-Text, Meta-Infos, Lead-Sätze |

### Accents (Neon)

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--accent-cyan` | `#00d4ff` | Hover-States, Sekundär-Buttons, Trust-Signale, Tech-Akzente |
| `--accent-magenta` | `#e040fb` | Primär-CTAs, Highlights, Tag-Pulse, Kontrast-Anker |
| `--accent-purple` | `#7b2ff7` | Gradient-Mid-Stop, Glow-Effekte |

### Gradients

```css
--gradient-wave: linear-gradient(135deg, #00d4ff 0%, #7b2ff7 50%, #cc00ff 100%);
```

**Verwendung:**
- Hero-Headline-Highlight (gradient-text via `-webkit-text-fill-color: transparent`)
- Section-Title-Underline (4px Höhe, 80px Breite, zentriert)
- Section-Card-Top-Bar (subtile Akzent-Linie)

**Banner-Gradient (Construction-Modus, separat):**
```css
linear-gradient(90deg, #ff6b00 0%, #e040fb 50%, #ff6b00 100%);
/* mit bannerShift 4s ease infinite (background-position 0% → 100%) */
```

### Glow-Shadows (Signature-Effekt)

```css
--glow-magenta-soft:   0 0 20px rgba(224,64,251,0.20);
--glow-magenta-medium: 0 0 32px rgba(224,64,251,0.42);
--glow-magenta-strong: 0 0 52px rgba(224,64,251,0.65);
--glow-cyan-soft:      0 0 20px rgba(0,212,255,0.20);
```

**Regel:** Glow nur auf interaktiven Elementen (Buttons, Hover-States) und Hero-Highlights — niemals auf Body-Content (zerstört Lesbarkeit).

---

## Typografie

### Font-Stack (DSGVO-konform — nur lokal, kein Google CDN)

| Token | Font | Fallback | Use-Case | Weights |
|-------|------|----------|----------|---------|
| `--font-display` | **Orbitron** | monospace | Headlines, Section-Titles, Logo, CTA-Text | 700–900 |
| `--font-body` | **Exo 2** | sans-serif | Body, Lead, Buttons, Navigation | 100–900 (variabel) |
| `--font-mono` | **Space Mono** | monospace | Code, Terminal-UI, Tech-Snippets, Tag-Labels | 400, 700 |

**Files:** `assets/fonts/{orbitron,exo2,spacemono-regular,spacemono-bold}.woff2`
**Load-Strategy:** `font-display: swap` — kein FOUT-Block.

### Type-Scale

| Element | Size | Weight | Line-Height | Letter-Spacing |
|---------|------|--------|-------------|----------------|
| Hero-Headline | `clamp(2.2rem, 5vw, 4.6rem)` | 900 | 1.1 | default |
| Section-Title | `clamp(1.4rem, 3vw, 2.2rem)` | 700 | 1.3 | 0.06em |
| Card-Title | 1.4rem | 700 (display) | 1.3 | default |
| Lead-Text | 1.1rem | 300 | 1.7 | 0.03em |
| Body | 1rem | 400 | 1.6 | default |
| Small / Meta | 0.82rem | 600–700 | 1.3 | 0.07em (uppercase) |
| Tag-Label | 0.72rem | 700 (mono) | 1.3 | 0.12em (uppercase) |
| Code / Terminal | 0.72–0.84rem | 400/700 (mono) | 1.5 | default |

**Regel:** Display-Font (Orbitron) NUR für ≤3 Worte oder kurze Headlines. Längere Texte werden unleserlich.

---

## Spacing & Layout

### Container

- **Section-Padding:** `96px 6%` (vertikal/horizontal — % gibt Responsive-Atmung)
- **Max-Width Hero-Content:** 720px
- **Nav-Padding:** `0 6%` (gleiche Horizontal-Marge wie Sections)

### Border-Radius

| Verwendung | Radius |
|------------|--------|
| Cards, Feature-Boxes | `14px` |
| Buttons (Primary + Secondary) | `50px` (pill-shape) |
| Pills, Tags | `100px` |
| Avatars, Dots | `50%` |
| Section-Title-Underline | `2px` |

### Component-Padding

| Komponente | Padding |
|------------|---------|
| Primary-Button | `14px 30px` |
| Secondary-Button | `14px 30px` |
| Pill / Tag | `6px 16px` |
| Card | `20–24px` (kontextabhängig) |
| Banner | `9px 48px` |

---

## Komponenten-Patterns

### Primary Button (Magenta-CTA)

```css
background: var(--accent-magenta);   /* #e040fb */
color: white;
padding: 14px 30px;
border-radius: 50px;
font-weight: 700;
font-size: 0.93rem;
box-shadow: 0 0 32px rgba(224,64,251,0.42);
transition: all 0.2s;

&:hover {
  background: #cc00ff;
  box-shadow: 0 0 52px rgba(224,64,251,0.65);
}
```

### Secondary Button (Cyan-Outline)

```css
background: transparent;
color: var(--accent-cyan);
border: 1.5px solid var(--accent-cyan);  /* implizit aus Style-Pattern */
padding: 14px 30px;
border-radius: 50px;
font-weight: 600;

&:hover {
  background: rgba(0,212,255,0.08);
  box-shadow: 0 0 20px rgba(0,212,255,0.2);
}
```

### Card (Service / Feature)

```css
background: var(--bg-card);     /* #1a1a35 */
border-radius: 14px;
padding: 20–24px;
position: relative;
overflow: hidden;

/* Optional Top-Accent-Bar mit gradient-wave (opacity 0.14) */
```

### Tag / Pill

```css
font-family: var(--font-mono);   /* Space Mono */
font-size: 0.72rem;
font-weight: 700;
letter-spacing: 0.12em;
text-transform: uppercase;
padding: 6px 16px;
border-radius: 100px;
color: var(--accent-magenta);
background: rgba(224,64,251,0.13);
```

### Terminal-Mockup (About-Sektion)

- Background: `#080810` (tiefer als bg-primary)
- Header-Bar: `#14142a` mit drei Traffic-Light-Dots `#ff5f57 / #ffbd2e / #28ca41` (8px, border-radius 50%)
- Content: Space-Mono, `font-size: 0.79rem`

---

## Animationen

| Name | Dauer | Easing | Verwendung |
|------|-------|--------|------------|
| `bannerShift` | 4s | ease infinite | Construction-Banner-Gradient (background-position 0%→100%→0%) |
| `blobFloat` | (siehe index.html) | infinite | Hero-Background-Blobs |
| `tagPulse` | (siehe index.html) | infinite | Status-Dot in Pills |

**Regel:** Animations IMMER mit `prefers-reduced-motion`-Fallback. ✅ Implementiert in `index.html` (alle dekorativen Animationen gestoppt, Skeleton-Shimmer auf Opacity-Pulse reduziert).

---

## Assets

| Asset | Pfad | Use-Case |
|-------|------|----------|
| Logo | `assets/logo.png` | Header, Reports |
| OG-Image | `assets/og-image.png` + `assets/og-image.svg` | Social-Sharing, Meta-Tags |
| Fonts (woff2) | `assets/fonts/` | Lokale Font-Loads, DSGVO-konform |

**Logo-Regeln:**
- Mindestgröße: 32px Höhe (ab Header)
- Kein Re-Coloring — immer Original-PNG/SVG
- Auf hellen Backgrounds: ggf. Dark-Variante anlegen (TODO: existiert noch nicht)

---

## Dont's

- ❌ Google Fonts CDN (DSGVO-Risiko, alle Fonts lokal in `assets/fonts/`)
- ❌ Matrix-Grün (`#00ff00`) oder Hacker-Skull-Visuals — verwässert Defensive-Brand
- ❌ Glow-Shadows auf Body-Text (zerstört Kontrast)
- ❌ Orbitron für Body/Lead — nur Display-Font
- ❌ Light-Mode-Variante ohne Brand-Review (Cyber-Aspis ist primär dark — Light-Theme braucht eigene Token-Map)
- ❌ Reine Magenta-auf-Cyan- oder Cyan-auf-Magenta-Kombinationen (Vibrieren, A11y-Fail bei großen Flächen)

---

## Accessibility-Status

- ✅ Kontrast Body-Text (#ffffff auf #0d0d1a): >18:1
- ✅ Kontrast Muted-Text (#9090b8 auf #0d0d1a): ~6:1 (AA für Normal-Text)
- ⚠️ Cyan auf bg-primary (#00d4ff auf #0d0d1a): ~10:1, OK — aber NICHT für kleinen Text auf bg-secondary (#12082e) verwenden ohne Re-Check
- ⚠️ Magenta-CTA-Hover (#cc00ff): Kontrast prüfen bei kleineren Button-Sizes
- ✅ `prefers-reduced-motion` implementiert — dekorative Animationen gestoppt, Skeleton-Shimmer auf Opacity-Pulse reduziert

---

---

## PROFILE B — Social-Cards (LinkedIn-Carousels)

> Quelle: `Claude-cowork/Cyber-Aspis/cowork-pipeline/templates/base.css` + `slide-{1-4}.html`. **Bewusst eigenes System** — Bebas Neue + Manrope rendern auf Social-Plattformen kompakter und scannbarer als Orbitron + Exo 2.

### Format & Renderer

- **Größe:** 1080×1350 px (LinkedIn Portrait, 4:5)
- **Padding:** `72px 80px` (außen)
- **Renderer:** WeasyPrint via Python-Pipeline (`render.py`), PDF→PNG-Export
- **Fonts:** lokal in `cowork-pipeline/fonts/` (.deb-Packages)

### Farb-Token (Profile B)

```css
:root {
  --bg-deep:       #0a0e1a;   /* tieferes Schwarz als Web */
  --bg-mid:        #0f1729;
  --brand-cyan:    #00d9ff;   /* Achtung: ≠ Web (#00d4ff) */
  --brand-cyan-d:  #00a8c4;   /* dunklere Cyan-Variante */
  --alarm-red:     #ff3c3c;   /* eigene Akzent-Farbe für Mythos/Warnung */
  --alarm-red-d:   #c92a2a;
  --text-primary:  #f0f4f8;   /* leicht off-white statt #ffffff */
  --text-muted:    #8b95a8;
  --text-subtle:   #4a5568;
}
```

### Background-Komposition (Signature)

```css
background:
  radial-gradient(circle at 85% 15%, rgba(255,60,60,0.12), transparent 45%),
  radial-gradient(circle at 15% 90%, rgba(0,217,255,0.08), transparent 45%),
  linear-gradient(135deg, #0a0e1a 0%, #0f1729 100%);
```

**Plus subtiler 50px-Grid-Overlay** (`rgba(0,217,255,0.04)` Linien):

```css
.slide::before {
  background-image:
    linear-gradient(rgba(0,217,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,217,255,0.04) 1px, transparent 1px);
  background-size: 50px 50px;
}
```

### Typografie (Profile B)

| Element | Font | Size | Weight | Letter-Spacing |
|---------|------|------|--------|----------------|
| **Headline-1 (Hook)** | Bebas Neue | 138px | 400 | 0.005em |
| **Headline-2/3/4** | Bebas Neue | 84px | 400 | 0.005em |
| **CTA-Headline** | Bebas Neue | 36px | 400 | 0.04em |
| **Brand-Title** | Bebas Neue | 24px | 400 | 0.12em |
| **Logo-Text "CA"** | Bebas Neue | 24px | 400 | 0.05em |
| **Subline** | Manrope | 20–22px | 400 | default |
| **Verdict-Text** | Manrope | 22px | 400 | default |
| **Option-Text** | Manrope | 19px | 400 | default |
| **Terminal-Cmd** (Header) | JetBrains Mono | 16px | 400 | default |
| **Slide-Counter / Footer-Marker** | JetBrains Mono | 14px | 400 | 0.10em |
| **Mono-Label** | JetBrains Mono | 14px | 400 | 0.08em (lowercase) |
| **Badge** | JetBrains Mono | 14px | 600 | 0.18em (uppercase) |
| **Brand-Tagline** | JetBrains Mono | 11px | 400 | 0.18em (uppercase) |

**Display-Regel:** Bebas Neue IMMER `text-transform: uppercase` + `line-height: 1.02`.

### Komponenten-Patterns (Profile B)

#### Slide-Header (Terminal-Style)
```html
<header class="slide-header">
  <span class="terminal-cmd">$ scope --target "..." --framework bsi-grundschutz</span>
  <span class="slide-counter">01 / 04</span>
</header>
```
JetBrains-Mono, Cyan-Command + grauer Counter — referenziert Pentest-Tool-Vibe.

#### Brand-Block (Footer)
```html
<div class="brand">
  <div class="ca-logo">CA</div>            <!-- 48×48 cyan-border -->
  <div class="brand-name">
    <div class="brand-title">Cyber Aspis</div>
    <div class="brand-tagline">IT-Sicherheit für jeden</div>
  </div>
</div>
```

#### Badges (Mythos/Basis)
```css
.badge.mythos { color: #ff3c3c; border: 1px solid #ff3c3c; }   /* Warn-Akzent */
.badge.basis  { color: #00d9ff; border: 1px solid #00d9ff; }   /* Defensive-Akzent */
```

#### Verdict-Box (Hero-Statement)
```css
.verdict-box       { background: rgba(255,60,60,0.10); border: 1px solid rgba(255,60,60,0.45); }   /* Warning */
.verdict-box.cyan  { background: rgba(0,217,255,0.08); border: 1px solid rgba(0,217,255,0.45); }   /* Defensive */
```
Padding `24px 28px`, Icon links, Text mit `<span class="highlight">` für Akzent-Wörter.

#### Basis-Row (Numbered List)
```html
<div class="basis-row">
  <span class="basis-num">[ 01 ]</span>
  <span class="basis-label">MFA für Admin-Zugänge</span>
</div>
```
Cyan-Border-Left 3px, Bebas-Label 34px, JetBrains-Nummer 14px in cyan-border-Box.

#### CTA-Box
```css
.cta-box {
  border-left: 4px solid #00d9ff;
  background: rgba(0, 217, 255, 0.06);
  padding: 28px 32px;
}
```

### Slide-Architektur (4-Slide-Carousel)

| Slide | Zweck | Hauptelement |
|-------|-------|--------------|
| **01** Hook | Provokante Headline | `headline-1` 138px + Verdict-Box |
| **02** Liste | 5 Punkte / Maßnahmen | `basis-list` aus 5 `basis-row` |
| **03** Hebel | Aufwand vs. Wirkung | `lever-row` Grid (Effort 220px, Arrow 24px, Text 1fr) |
| **04** CTA | Frage an Community + Action | `options-grid` 2×2 + CTA-Box |

### Don'ts (Profile B)

- ❌ Orbitron oder Exo 2 (Web-Fonts) — komplett anderes Profile
- ❌ Magenta `#e040fb` (Web-CTA-Farbe) — Profile B benutzt Cyan oder Alarm-Red
- ❌ Glow-Shadows wie Web — Carousel-Look ist matter, mit Border+Background-Tint statt Glow
- ❌ Über 4 Slides ohne Reason — LinkedIn-Engagement bricht ab Slide 5

---

## PROFILE C — PDF-Reports (Toolkit Audit-Output)

> Quelle: `Cyber-Aspis/toolkit/backend/templates/report_{quick_check,interim,final,professional}.html.j2`. **Bewusst Print-optimiert** — Helvetica Neue + Light-Theme weil Reports gedruckt + an Steuerberater/Anwälte/KMU-Geschäftsführer geschickt werden, die kein Cyberpunk-Theme erwarten.

### Format & Renderer

- **Größe:** A4 (210×297mm)
- **Margin:** `2cm 2.5cm` (Standard) oder `0` für Cover-Page (Professional)
- **Renderer:** WeasyPrint via Jinja2 (`backend/app/services/report.py`)
- **Page-Numbering:** automatisch via `@bottom-right { content: "Seite " counter(page) " von " counter(pages); }`

### Sub-Variant C1 — Quick-Check Report

**Use-Case:** €99-299 KMU-Quick-Check, kompakt, lesbar, severity-fokussiert.

#### Farb-Token (C1)

```css
/* Brand */
--brand-navy:      #1e3a5f;     /* Primary — Header-Underline, Title-Block-BG, h2-Color */
--brand-purple:    #7c3aed;     /* Akzent — Brand-Span, h2-Border-Left */

/* Text */
--text-body:       #1f2937;
--text-meta:       #6b7280;
--text-strong:     #374151;

/* Severity-Skala (3-stufig) */
--sev-high:        #dc2626;     /* + box: bg #fef2f2, border #ef4444 */
--sev-medium:      #d97706;     /* + box: bg #fffbeb, border #f59e0b */
--sev-low:         #059669;     /* + box: bg #ecfdf5, border #10b981 */
--sev-total:       #374151;     /* + box: bg #f9fafb, border #6b7280 */

/* Layout */
--rule:            #e5e7eb;     /* Tabellen-Borders */
--scope-bg:        #f8fafc;
--scope-border:    #e2e8f0;
```

#### Typografie (C1)

| Element | Size | Weight |
|---------|------|--------|
| Body | 10.5pt | 400 |
| Brand (Header) | 18pt | 700 (navy + purple span) |
| Title-Block H1 | 16pt | 700 (white auf navy) |
| H2 | 12pt | 700 (navy, purple border-left 4px) |
| Summary-Box-Count | 24pt | 700 |
| Summary-Box-Label | 8pt | 600 (uppercase, letter-spacing 0.05em) |
| Tabellen-Header | 9.5pt | 600 (white auf navy) |
| Page-Footer | 9pt | 400 (meta-grau) |

**Font-Stack:** `"Helvetica Neue", Arial, sans-serif` für alles, `monospace` nur für CVSS-Werte.

#### Komponenten-Patterns (C1)

- **Title-Block:** Navy-Background `#1e3a5f`, weißer Text, padding `18px 20px`, border-radius 4px
- **Summary-Grid:** 4 Boxen flex-row mit count + label, severity-coded
- **Severity-Badge:** pill-shape (border-radius 10px), padding 2px 8px, severity-coded background+border
- **Recommendation-Item:** colored border-left 4px (sev-coded), pastel background, padding 10px 14px
- **Scope-Box:** light-grey `#f8fafc`, border `#e2e8f0`, font-size 9.5pt

### Sub-Variant C2 — Professional / Interim / Final Reports

**Use-Case:** Größere Engagements (€500+), Cover-Page, 5-stufige Severity-Skala (mit Critical + Info), refinierter Look.

**State-Color-Coding (3 Varianten):** Die C2-Familie unterscheidet sich in **EINEM Token**
(`--brand-accent` für h2-border-left), das den Report-Status semantisch farb-codiert. Cover,
Severity-Skala, Typografie, alles andere ist identisch.

| C2-Variante | Source | Accent (h2-border) | Semantik |
|-------------|--------|-------------------|----------|
| **Professional** | `report_professional.html.j2` | `#818cf8` Indigo | formaler Vollbericht, neutral |
| **Interim** | `report_interim.html.j2` | `#f59e0b` Orange | Zwischenbericht, work-in-progress / Achtung |
| **Final** | `report_final.html.j2` | `#34d399` Green | Abschlussbericht, completion / closed |

→ Beim Erzeugen einer neuen C2-Variante: nur `--brand-accent` setzen, alles andere von der
Professional-Variante erben.

#### Farb-Token (C2)

```css
/* Cover (eigene Farbwelt — dark) — IDENTISCH in allen 3 Varianten */
--cover-bg:         #0f172a;
--cover-text:       #f8fafc;
--cover-meta:       #e2e8f0;
--cover-meta-lbl:   #64748b;
--cover-divider:    #334155;
--cover-footer-rule:#1e293b;

/* Brand (Content-Pages) */
--brand-indigo:     #818cf8;    /* Primary-Akzent — h2-border-left, brand-span, subtitle */
--brand-deep:       #0f172a;    /* H2-Color */

/* Severity-Skala (5-stufig) */
--sev-critical:     #7c3aed;    /* + box: bg #f5f3ff, border #a78bfa */
--sev-high:         #dc2626;    /* + box: bg #fef2f2, border #fca5a5 */
--sev-medium:       #d97706;    /* + box: bg #fffbeb, border #fcd34d */
--sev-low:          #059669;    /* + box: bg #ecfdf5, border #6ee7b7 */
--sev-info:         #2563eb;    /* + box: bg #eff6ff, border #93c5fd */
--sev-total:        #374151;    /* + box: bg #f9fafb, border #d1d5db */
```

#### Typografie (C2)

| Element | Size | Weight |
|---------|------|--------|
| Cover-Title | 26pt | 700 |
| Cover-Logo | 22pt | 800 (white + indigo span) |
| Cover-Subtitle | 13pt | 500 (indigo) |
| Cover-Tagline | 8.5pt | 400 (uppercase, letter-spacing 0.08em) |
| Cover-Meta-Label | 7.5pt | 400 (uppercase, letter-spacing 0.1em) |
| Cover-Meta-Value | 11pt | 600 |
| Content-H2 | 13pt | 700 (deep, indigo border-left) |
| Content-H3 | 10.5pt | 700 |
| Severity-Box-Num | 22pt+ | 700 |
| Severity-Label | 7.5pt | 600 (uppercase, letter-spacing 0.05em) |

#### Cover-Page-Pattern

```css
@page cover {
  size: A4;
  margin: 0;
  background: #0f172a;
}
.cover-logo span    { color: #818cf8; }       /* "Cyber" weiß, "Aspis" indigo */
.cover-divider      { border-top: 1px solid #334155; }
.cover-meta-label   { color: #64748b; uppercase; }
.cover-meta-value   { color: #e2e8f0; }
.cover-footer       { border-top: 1px solid #1e293b; color: #475569; }
```

### Don'ts (Profile C)

- ❌ Cyan/Magenta-Neon (Web-Profile) — wirkt unprofessionell auf Audit-Reports
- ❌ Orbitron/Bebas Neue — Display-Fonts auf Print-Body unleserlich
- ❌ Glow-Shadows — auf Print kein Effekt, nur Druckkosten
- ❌ Severity-Farben mischen zwischen C1 (3-stufig) und C2 (5-stufig) — pro Report-Typ konsistent

---

## Cross-Profile Konventionen

Trotz divergierender Tokens gelten überall:

- **Sprache:** Deutsch (Sie-Form Marketing/Reports, Du-Form intern, Content-Tonalität siehe `Brand-Identität` oben)
- **Logo:** `cyber-aspis-website/assets/logo.png` (Web) bzw. CA-Glyph in Bebas Neue + Cyan-Border (Carousel) bzw. Wortmarke "Cyber **Aspis**" mit accent-span (Reports). Nie verändern, nur skalieren.
- **Tagline:** "IT-Sicherheit für jeden" (auf allen Surfaces wo Tagline existiert)
- **DSGVO-Fonts:** Alle Fonts lokal eingebunden, kein Google CDN, in keiner Surface

---

## Surface-Drifts — bewusst akzeptiert

Drei minimale Abweichungen zwischen Profile-A/B/C-Tokens sind nach Phase-4-Review
(2026-05-12) bewusst beibehalten worden — keine Harmonisierung geplant. Diese
Sektion dokumentiert sie als nicht-zu-„fixende" Surface-Optimierungen.

### Drift 1 · Cyan-Akzent (Web vs Social)

| Surface | Wert | Rationale |
|---------|------|-----------|
| Profile A (Web) | `#00d4ff` | Browser-nativ, sRGB-Display |
| Profile B (Social) | `#00d9ff` | Kompensiert WeasyPrint→PNG + LinkedIn-Re-Kompression |

**Owner-Decision (2026-05-12):** Beibehalten. Render-Pfad-Optimierung > Single-Token-Konsistenz.

### Drift 2 · Text-Primary (Web vs Social)

| Surface | Wert | Rationale |
|---------|------|-----------|
| Profile A (Web) | `#ffffff` | Maximaler Web-Kontrast |
| Profile B (Social) | `#f0f4f8` | Editorial-Off-White, reduziert Blendung bei Mobile-OLED-Konsum |

**Owner-Decision (2026-05-12):** Beibehalten als bewusste Editorial-Wahl für Social-Surface.

### Drift 3 · Severity-Stufen (C1 vs C2)

| Profile | Skala | Rationale |
|---------|-------|-----------|
| C1 Quick-Check | 3-stufig (High/Medium/Low) | Triage-Produkt, kein „Critical"-Eskalations-Druck |
| C2 Professional | 5-stufig (Critical/High/Medium/Low/Info) | Vollanalyse, saubere Priorisierung großer Findings-Listen |

**Owner-Decision (2026-05-12):** Kein Drift sondern bewusste Produkt-Differenzierung. Severity-Skala = Produkt-Sprache, nicht Brand-Sprache.

---

## Konsumenten-Tabelle (aktualisiert)

| Wo verwendet | Profile | Status |
|--------------|---------|--------|
| `cyber-aspis-website/index.html` | A — Web | ✅ canonical |
| `Claude-cowork/Cyber-Aspis/cowork-pipeline/` | B — Social | ✅ canonical (4-Slide-Carousel) |
| `Cyber-Aspis/toolkit/backend/templates/report_quick_check.html.j2` | C1 — Quick-Check | ✅ canonical |
| `Cyber-Aspis/toolkit/backend/templates/report_professional.html.j2` | C2 — Professional (Indigo) | ✅ canonical |
| `Cyber-Aspis/toolkit/backend/templates/report_interim.html.j2` | C2 — Interim (Orange) | ✅ canonical |
| `Cyber-Aspis/toolkit/backend/templates/report_final.html.j2` | C2 — Final (Green) | ✅ canonical |
| `Cyber-Aspis/toolkit/frontend/` (React + Tailwind) | A-Web (TD) | ⚠️ Tailwind-Config noch nicht auf Profile-A-Tokens gemappt |
| Slides / Pitch-Decks | — | ❌ nicht standardisiert — bei Bedarf neues Profile D |

---

## Changelog

| Datum | Änderung | Autor |
|-------|----------|-------|
| 2026-05-12 | Initial — Profile A (Web) extrahiert aus `index.html` | Claude (Session) |
| 2026-05-12 | + Profile B (Social-Cards/Carousels) extrahiert aus `cowork-pipeline/` | Claude (Session) |
| 2026-05-12 | + Profile C1+C2 (PDF-Reports Quick-Check + Professional) extrahiert aus `toolkit/backend/templates/` | Claude (Session) |
| 2026-05-12 | Phase-4 Drift-Review abgeschlossen — alle 3 Drifts beibehalten + als Surface-Optimierungen dokumentiert (Sektion „Surface-Drifts — bewusst akzeptiert") | Owner-Decision |
| 2026-05-12 | Phase-5 Ticket 1 — `prefers-reduced-motion` in index.html implementiert (alle dekorativen Animationen + Skeleton-Shimmer-Reduktion) | Phase-5 |
| 2026-05-12 | Phase-5 Ticket 3 — C2-Familie vollständig dokumentiert: report_interim als C2-Interim (Orange) und report_final als C2-Final (Green) eingeordnet. State-Color-Coding-Pattern dokumentiert (Professional Indigo / Interim Orange / Final Green — sonst identisch zu C2-Professional) | Phase-5 |
