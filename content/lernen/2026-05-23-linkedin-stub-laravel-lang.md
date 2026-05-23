---
title: "LinkedIn Stub: Laravel-Lang Supply-Chain-Angriff"
date: 2026-05-23
draft: false
internal: true
description: "LinkedIn-Post-Stub für Monday 2026-05-26 — Supply-Chain-Attack auf Laravel-Lang, 233 Versionen über rund 700 Repos, Self-Check-Snippet"
---

## LinkedIn-Hook (erste 3 Zeilen — Algorithmus-Optimiert)

**233 Composer-Paket-Versionen mit bösartigem Code. Über rund 700 GitHub-Repos verteilt. Ein einziges Git-Tag-Rewrite.**

Der Laravel-Lang-Supply-Chain-Vorfall Mai 2026 zeigt: Software-Lieferketten sind das neue Angriffsziel — und ein einfaches `composer install` könnte Ihre Produktionsumgebung kompromittieren.

---

## Body (150-200 Wörter)

Der Angriff war perfide, weil **nicht das Repository selbst gehackt wurde**, sondern historische Git-Tags auf einen Angreifer-Fork umgebogen wurden. Composer zog die manipulierten Versionen automatisch — ohne dass die Commit-History des offiziellen Repos etwas verriet.

Betroffene Pakete (Quellen: Aikido + Socket): `laravel-lang/lang`, `http-statuses`, `attributes`, `actions`.

Was das Backdoor-Skript tat: Cloud-Credentials, K8s-Tokens, SSH-Keys, `.env`-Inhalte und Cloud-Metadata exfiltrierten per HTTPS an die C2-Domain `flipboxstudio.info`.

**Was Sie jetzt tun:**
1. `composer.lock` auf die vier Pakete prüfen (15 Sek.)
2. `$TMPDIR/.laravel_locale/` auf Produktionsservern checken
3. Cloud-/CI-Tokens zuerst rotieren — vor APP_KEY

Wir haben eine vollständige Supply-Chain-Audit-Checkliste geschrieben — mit Befehlen zum Kopieren, Reihenfolge-Priorisierung und Langzeit-Schutzmaßnahmen.

---

## Self-Check-Snippet (zum direkten Kopieren)

```bash
# 15-Sekunden-Check: Hat es Sie getroffen?
grep -A2 -E '"name": "laravel-lang/(lang|http-statuses|attributes|actions)"' composer.lock
ssh prodserver 'ls -la "${TMPDIR:-/tmp}/.laravel_locale/"' 2>/dev/null && echo "WARNUNG: IoC gefunden!"
```

---

## CTA (Soft, nicht pushy)

Lesen Sie den [vollständigen Artikel mit Self-Audit-Checkliste](https://cyber-aspis.de/lernen/2026-05-23-supply-chain-laravel-lang) — inklusive Befehle für PHP, Python, Node.js und Credential-Rotation-Reihenfolge.

Falls Ihre Build-Pipeline überprüft werden sollte: **[Erstgespräch buchen](https://cal.com/cyber-aspis)** (kostenlos, 30 Min).

---

## Hashtags

#ITSecurity #KMU #SupplyChain #BSI #Cybersecurity

---

## Posting-Hinweise

- **Geplantes Posting:** Monday 2026-05-26, 09:00 Uhr (Peak-Zeit für deutschsprachiges IT-Publikum)
- **Markdown-Hinweis:** Beim Copy-Paste in LinkedIn: Asterisks `**...**` und Backticks `` `...` `` werden als wörtliche Zeichen gepostet (LinkedIn unterstützt kein Markdown). Optionen: (a) Code-Bereiche mit Anführungszeichen umgeben statt Backticks, (b) Bold-Text über LinkedIn-Editor selbst (Strg+B) formatieren, oder (c) Unicode-Bold via yaytext.com ersetzen.
- **Bild:** Optional — Screenshot aus dem Artikel oder Grafik mit Datenfluss-Diagramm (Exfil-Risiko visualisieren)
- **LinkedIn-URL:** Vollständig im CTA-Link, nicht gekürzt
