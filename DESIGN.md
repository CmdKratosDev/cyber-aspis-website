# Cyber-Aspis — Design-Konvention

> Single Source of Truth für das visuelle Erscheinungsbild von Cyber-Aspis (Website, Toolkit-Frontend, Reports, Slides). Extrahiert aus `index.html`. Updates an Brand-Tokens IMMER hier zuerst, dann in den Implementations propagieren.

## Brand-Identität

**Tonalität:** Cyberpunk-Modern, technisch-präzise, KMU-zugänglich. Kein Hacker-Klischee (kein Matrix-Grün, kein Skull-Logo), sondern **Defensive Tech mit Neon-Akzenten** — vermittelt Kompetenz + Modernität ohne Bedrohlichkeit.

**Sprache:** Deutsch (Sie-Form auf Marketing, Du-Form intern). Tech-Begriffe Englisch (Pentest, Audit, Scope). Keine Übersetzungen für etablierte Begriffe.

**Visueller Hook:** Dunkles Indigo-Schwarz + Cyan/Magenta-Neon-Akzente + Orbitron-Display-Font.

---

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

**Regel:** Animations IMMER mit `prefers-reduced-motion`-Fallback (TODO: in index.html ergänzen).

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
- ❌ `prefers-reduced-motion` noch nicht implementiert (TODO)

---

## Konsumenten dieser Konvention

| Wo verwendet | Status |
|--------------|--------|
| `cyber-aspis-website/index.html` | ✅ Source-of-Truth (extrahiert aus hier) |
| `Cyber-Aspis/toolkit/frontend/` (React + Tailwind) | ⚠️ Tailwind-Config noch nicht auf Brand-Tokens gemappt — TD |
| Audit-Reports (PDF via WeasyPrint) | ⚠️ Eigenes Stylesheet, sollte Token-Subset übernehmen — TD |
| Slides / Pitch-Decks | ❌ Nicht standardisiert — bei Bedarf hier ergänzen |

---

## Changelog

| Datum | Änderung | Autor |
|-------|----------|-------|
| 2026-05-12 | Initial — extrahiert aus `index.html` | Claude (Session) |
