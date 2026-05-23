---
title: "Selbst-Audit-Checkliste: Supply-Chain-Risiken in Ihrer Build-Pipeline"
date: 2026-05-23
draft: false
description: "20-Punkte-Selbst-Audit-Checkliste zum Auditieren Ihrer CI-Pipeline auf Supply-Chain-Vulnerabilities. Für PHP/Composer, Python/pip, Node.js/npm, Docker und CI/CD-Secrets."
tags: ["supply-chain", "audit", "ci-cd", "security-checklist", "kmu"]
author: "Cyber Aspis"
---

## Anleitung

Diese Checkliste hilft Ihnen, Ihre Build-Pipeline auf Supply-Chain-Risiken zu überprüfen. Folgen Sie den Befehlen in der Spalte **Wie (Befehl)** auf Ihrer lokalen Entwicklungsmaschine und auf Ihren CI/CD-Servern (GitHub Actions, GitLab CI, Jenkins, etc.).

Wenn Sie Treffer/Auffälligkeiten haben, dokumentieren Sie am Ende dieser Checkliste, was Sie gefunden haben und wann Sie es rotieren möchten.

---

## Checkliste

| # | Was prüfen | Wie (Befehl) | Erwartetes Ergebnis | Erledigt |
|---|-----------|--------------|---------------------|----------|
| **PHP / Composer** | | | | |
| 1 | Composer `audit` durchführen | `cd <projektroot> && composer audit` | Keine Vulnerabilities; falls vorhanden, Paket sofort updaten. | ☐ |
| 2 | Laravel-Lang Pakete spezifisch prüfen (Mai-2026-Vorfall) | `grep -A2 -E '"name": "laravel-lang/(lang\|http-statuses\|attributes\|actions)"' composer.lock` | Sollte leer sein oder nur Versionen, die nach dem Maintainer-Patch-Datum (ab 23.05.2026) getaggt sind | ☐ |
| 3 | Composer.lock in Git versioniert? | `git ls-files composer.lock` | Zeigt `composer.lock` an | ☐ |
| 4 | Composer.lock Integrität prüfen | `composer validate && composer install --dry-run` | Keine Fehler, Lock-Datei stimmt mit composer.json überein | ☐ |
| 5 | `autoload.files` in kritischen Paketen prüfen | `grep -r '"files"' vendor/*/composer.json \| head -20` | Liste aller `files`-Einträge bekannt; verdächtige Einträge wurden manuell geprüft. | ☐ |
| **Python / pip** | | | | |
| 6 | pip-audit durchführen | `pip install pip-audit && pip-audit` | Keine Vulnerabilities gemeldet | ☐ |
| 7 | Requirements.txt mit Hashes | `cat requirements.txt \| head -5` | Falls Sie `pip-compile --generate-hashes` einsetzen: Hash pro Paket. Sonst: Punkt 8 umsetzen. | ☐ |
| 8 | pip-compile mit Hash-Validierung nutzen | `pip install pip-tools && pip-compile --generate-hashes requirements.in` | `requirements.txt` wurde mit `--generate-hashes` erstellt | ☐ |
| **Node.js / npm** | | | | |
| 9 | npm `audit` durchführen | `npm audit` | Keine kritischen/hohen Vulnerabilities | ☐ |
| 10 | package-lock.json versioniert? | `git ls-files package-lock.json` | Zeigt `package-lock.json` an | ☐ |
| 11 | npm ci statt npm install in CI/CD | `grep -r 'npm install' .github/workflows/ .gitlab-ci.yml Jenkinsfile` | Sollte leer sein; überall `npm ci` oder `npm ci --omit=dev` | ☐ |
| **Docker / Container-Images** | | | | |
| 12 | Base-Images by Digest pinnen | `grep 'FROM' Dockerfile \| grep -v '@sha256'` | Sollte leer sein; alle `FROM` müssen `@sha256:...` haben, nicht nur `FROM node:20` | ☐ |
| 13 | Scan nach bösartigen Images (Mai-2026-IoC) | `docker images \| grep -iE '(flipboxstudio\|laravel_locale)'` | Sollte leer sein; keine verdächtigen Images lokal | ☐ |
| **CI/CD & Secrets** | | | | |
| 14 | Secrets-Scope in CI/CD beschränkt | `cat .github/workflows/main.yml \| grep -E 'secrets\.|env:'` | Jeder Secret ist job-spezifisch; nicht alle Secrets für alle Jobs | ☐ |
| 15 | SSH-Keys / Deploy-Keys mit Expiry | `ssh-keygen -l -f ~/.ssh/id_rsa` (oder in GitHub Settings) | Deploy-Keys haben ein Verfallsdatum; alte Keys sind gelöscht | ☐ |
| **Produktionsserver (SSH)** | | | | |
| 16 | IoC-Check: Laravel-Lang Drop-Verzeichnis | `ssh user@prodserver 'ls -la "${TMPDIR:-/tmp}/.laravel_locale/"'` | Sollte nichts zurückgeben (Verzeichnis existiert nicht) | ☐ |
| 17 | Verdächtige PHP-Prozesse mit Netz-Aktivität | `ssh user@prodserver 'ps aux \| grep -iE "php.*(curl\|wget)" \| grep -v grep'` | Sollte leer sein | ☐ |
| 18 | SSH authorized_keys prüfen | `ssh user@prodserver 'cat ~/.ssh/authorized_keys'` | Nur Ihre bekannten Keys; unbekannte Keys sofort entfernen | ☐ |
| 19 | `.env`-Dateien außerhalb von Repos? | `find . -name '.env*' -not -path './.git/*' -not -path './vendor/*' -not -path './node_modules/*'` | Sollte nur `.env.example` oder `.env.local` zeigen (ohne echte Secrets) | ☐ |
| 20 | Logs auf C2/IMDS-Verbindungen | `ssh user@prodserver 'grep -E "flipboxstudio\|169\.254\.169\.254" /var/log/auth.log /var/log/syslog 2>/dev/null \| tail -20'` | Sollte leer sein; keine verdächtigen Netzwerk-Aktivitäten | ☐ |

---

## Befundsdokumentation

Falls Sie während der Checkliste Treffer haben (z.B. alte Deploy-Keys gefunden, verdächtige Images entdeckt), dokumentieren Sie hier:

### Template:

**Befund #[N]:**
- **Was:** [Beschreibung, z.B. "Laravel-Lang 5.0 in composer.lock"]
- **Wo:** [Server/Datei, z.B. "prodserver1.example.com"]
- **Wann gefunden:** [Datum]
- **Auswirkung:** [Wie kritisch — P0/P1/P2]
- **Rotation-Plan:** [Was wann machen, z.B. "Composer update heute 14 Uhr, Deploy morgen früh"]
- **Verantwortlich:** [Wer macht es]
- **Status:** [ ] Offen | [x] In Bearbeitung | [ ] Abgeschlossen

---

### Befund-Beispiel:

**Befund #1:**
- **Was:** `laravel-lang/lang` Version vor Patch-Datum in composer.lock
- **Wo:** Production-Server web01, web02
- **Wann gefunden:** 2026-05-23 10:30 Uhr
- **Auswirkung:** P0 — potentielle Exfiltration von Cloud- und DB-Credentials
- **Rotation-Plan:**
  1. Heute 14:00 — Cloud-API-Keys und CI/CD-Tokens rotieren
  2. Heute 15:00 — `composer update` durchführen, Tests in Staging
  3. Morgen 02:00 — Deployment in Production (Off-Peak)
  4. Morgen 06:00 — VCS-Token und DB-Passwort rotieren
- **Verantwortlich:** [Name, Rolle — z.B. DevOps-Lead]
- **Status:** [ ] Offen | [x] In Bearbeitung | [ ] Abgeschlossen (geplant für 2026-05-24)

---

## Schnell-Referenz: Welche Secrets WANN rotieren?

Falls Sie Treffer haben und nicht wissen, in welcher Reihenfolge Sie rotieren sollen — priorisiert nach Blast-Radius und Detection-Window:

1. **Sofort (innerhalb von 2 Stunden):**
   - **Cloud-API-Keys** (AWS Access Keys, Azure Service Principals, GCP Service Accounts) — höchstes Lateral-Movement-Risiko
   - **CI/CD-Pipeline-Tokens** (GitHub Actions, GitLab CI, Jenkins)

2. **Heute (innerhalb von 8 Stunden):**
   - **VCS-Tokens** (GitHub/GitLab PATs, Deploy-SSH-Keys für CI/CD) — verhindert Code-Manipulation
   - **Datenbankpasswörter** — falls die DB von außen erreichbar ist
   - **Laravel `APP_KEY`** — Off-Peak, invalidiert aktive Sessions. Sonderfall bei `encrypted`-Casts: APP_KEY vor DB-PW rotieren und Re-Encrypt durchführen

3. **Diese Woche:**
   - **SSH-Keys für Server-Admin-Zugriff**
   - **SaaS-API-Keys** (Stripe, Twilio, SendGrid, etc.)
   - **Passwort-Manager-Master-Passwörter** (falls exponiert)

---

## Weiterführende Ressourcen

- **OWASP Dependency Check:** `https://owasp.org/www-project-dependency-check/`
- **Snyk Supply Chain Security:** `https://snyk.io/`
- **CISA Known Exploited Vulnerabilities Catalog:** `https://www.cisa.gov/known-exploited-vulnerabilities`
- **National Vulnerability Database (NVD):** `https://nvd.nist.gov/`

---

## Nächste Schritte

Falls Sie während der Checkliste Unsicherheiten oder Fragen haben, kontaktieren Sie:

- Ihren internen Security-Team
- Cyber Aspis für einen **Supply-Chain-Audit** (30 Min Erstgespräch kostenlos)
- Ihren Managed Security Service Provider (MSSP)

**[Erstgespräch mit Cyber Aspis buchen](https://cal.com/cyber-aspis)**
