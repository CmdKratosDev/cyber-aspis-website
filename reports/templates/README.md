# Report-Templates (Inventur-Spiegel für brand.html)

> **Diese Dateien sind 1:1-Kopien aus `Cyber-Aspis/toolkit/backend/templates/`** (PRIVATE Toolkit-Repo).
> Sie liegen hier im PUBLIC website-Repo, damit `brand.html` die Profile-C-Surfaces
> rendern kann ohne Cross-Repo-Auflösung.
>
> **Source-of-Truth ist und bleibt das Toolkit-Repo.** Updates passieren dort,
> dieser Spiegel wird manuell nachgezogen.

## Inhalt

| Datei | Profile | Use-Case |
|-------|---------|----------|
| `report_interim.html.j2` | C-Familie | Zwischenbericht während laufendem Audit-Engagement |
| `report_final.html.j2` | C-Familie | Abschlussbericht (Customer-Level, mit Roadmap SOFORT/30/90 Tage) |

`report_quick_check.html.j2` (C1) und `report_professional.html.j2` (C2) sind
in DESIGN.md vollständig spezifiziert und werden nicht zusätzlich gespiegelt
(brand.html kann sie aus den Token-Tabellen rekonstruieren).

## Sync-Regel

Wenn die Toolkit-Templates geändert werden:

```bash
cp /p/Development/Cyber-Aspis/toolkit/backend/templates/report_interim.html.j2 \
   /p/Development/Cyber-Aspis/website/reports/templates/
cp /p/Development/Cyber-Aspis/toolkit/backend/templates/report_final.html.j2 \
   /p/Development/Cyber-Aspis/website/reports/templates/
```

Dann hier committen + pushen.

## Sanity-Check vor Commit

Diese Templates enthalten **Jinja2-Platzhalter, keine Customer-Daten**.
Vor jedem Sync prüfen:

```bash
grep -iE "@gmail|@outlook|api_key|secret|password|token|hardcoded" \
  reports/templates/*.html.j2
# Erwartet: keine Treffer
```

Pre-Sync 2026-05-12 — keine Treffer. ✅
