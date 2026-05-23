---
title: "Wie ein einziger Git-Tag deine gesamte CI-Pipeline kompromittiert — Lehren aus dem Laravel-Lang-Vorfall"
date: 2026-05-23
draft: false
description: "Mai 2026: 233 Versionen in vier Composer-Paketen über rund 700 GitHub-Repos vergiftet. Was geschah, welche Pakete betroffen sind, wie Sie in 15 Minuten prüfen, ob Sie es auch sind."
tags: ["supply-chain", "composer", "laravel", "ci-pipeline", "bsi-grundschutz", "kmu-cybersecurity"]
author: "Cyber Aspis"
seo:
  primary_keyword: "Supply Chain Angriff Composer"
  secondary: ["Laravel-Lang Backdoor", "composer.lock prüfen", "GitHub Tag Hijacking"]
---

## TL;DR

- **Mai 2026:** Ein Angreifer kompromittierte vier beliebte Composer-Pakete (PHP-Paketverwaltung, vergleichbar mit npm oder pip) der Laravel-Lang-Familie und injizierte bösartigen Code in **233 Paket-Versionen über rund 700 GitHub-Repository-Tags**.
- **Der Trick:** Nicht das Repository selbst wurde gehackt — stattdessen wurden Git-Tags auf einen manipulierten Fork umgebogen. Entwickler merkten nichts, bis die bösartige PHP-Datei über die `autoload.files`-Funktion von Composer automatisch beim Anwendungsstart ausgeführt wurde.
- **Das Risiko:** Das Skript suchte Credentials in der Cloud-Umgebung, Kubernetes-Token (K8s, das Cluster-Management-System), SSH-Keys, `.env`-Dateien, Laravel `APP_KEY` — alles für den automatisierten Upload zu Angreifer-Servern.
- **Ihre sofortige Aktion:** Führen Sie `composer audit` durch, prüfen Sie `$TMPDIR/.laravel_locale/` auf Ihren Produktionsservern, und rotieren Sie kritische Secrets sofort (Reihenfolge im Artikel).

---

## Was passiert ist: Die Zeitlinie

Im Mai 2026 entdeckte die PHP-Security-Community einen Vorfall, der Supply-Chain-Attacken auf ein neues Niveau hob. Ein oder mehrere Angreifer kompromittierten die GitHub-Organisationen hinter mehreren Composer-Paketen.

**Betroffene Pakete (Stand 23.05.2026, laut Aikido Security und Socket.dev):**

- `laravel-lang/lang`
- `laravel-lang/http-statuses`
- `laravel-lang/attributes`
- `laravel-lang/actions` *(nur in Socket-Report genannt; Aikido bestätigt die ersten drei)*

Diese sind keine Nischen-Tools. `laravel-lang/lang` allein zählt über 7.800 GitHub-Stars und ist **de facto Standard** in der Laravel-Community für Mehrsprachigkeit (Localization).

Kombiniert sind **zehntausende bis hunderttausende abhängige Anwendungen** betroffen — direkt oder transitiv in Web-Anwendungen aller Größen weltweit.

Das Kernproblem: Der Angreifer wurde nicht Committer des Repositorys (das hätte ein Commit-Log hinterlassen, das Entwickler sehen würden). Stattdessen wurden **historische Git-Tags umgebogen**. Ein Git-Tag ist im Composer-Ökosystem eine Versions-Markierung. Wenn Ihr `composer.json` sagt `"laravel-lang/lang": "^5.0"`, zieht Composer die **neueste passende Tag-Version**.

Der Angreifer änderte die Tag-Definitionen so, dass sie auf manipulierte Versionen in einem von ihm kontrollierten Fork zeigten. Das bedeutete: Entwickler, die `composer install` oder `composer update` ausführten, bekamen automatisch die bösartige Version — **ohne dass die Commit-History des offiziellen Repositorys etwas verraten hätte**.

Die Zahl: **233 Paket-Versionen wurden vergiftet, verteilt auf rund 700 GitHub-Repository-Tag-Rewrites.** (Die oft zitierte "700+"-Zahl bezeichnet die Repo-Reichweite, nicht die Anzahl distinkter Versionen.)

---

## Warum war dieser Angriff so gefährlich?

Um zu verstehen, warum Sie diesen Vorfall ernst nehmen sollten, müssen wir verstehen, wie automatische Code-Ausführung in Composer funktioniert.

### Der unsichtbare Einstiegspunkt: `autoload.files`

In PHP-Paketen können Entwickler eine besondere Anweisung namens `autoload.files` in der `composer.json` hinterlegen — eine Composer-Funktion, die PHP-Dateien automatisch beim Anwendungsstart lädt:

```json
{
  "autoload": {
    "files": ["src/helpers.php"]
  }
}
```

Das sieht harmlos aus — `src/helpers.php` wird beim Autoloader-Start geladen. Aber hier ist das Problem: **Die Datei wird automatisch geladen, ohne dass Ihr Code sie explizit aufruft.** Ein bösartiges Paket kann Code dort einfügen, und dieser wird **beim Anwendungsstart ausgeführt** — vor jeder Ihrer Sicherheitsregeln, Logging-Systeme oder Firewalls.

### Was der bösartige Code tat

Das Laravel-Lang-Backdoor-Skript führte das Standardmuster einer Data-Exfil-Attacke durch:

1. **Datensammlung:** Das Skript durchsuchte die Runtime-Umgebung nach Secrets (laut Aikido + Socket-Analyse):
   - Umgebungsvariablen (`getenv()` — PHP-Funktion zum Auslesen von Umgebungsvariablen) — hier liegen Cloud-API-Keys, DB-Credentials, VCS-Token
   - Dateisystem-Scans nach `.env`-Dateien (Laravel-Standard für Secrets)
   - Laravel-Framework-Dumps: `APP_KEY` (Verschlüsselungsgeheimnis, könnte historische Sessions dechiffrieren)
   - Kubernetes-`serviceaccount`-Token (falls containerisiert)
   - SSH-Keys aus `~/.ssh/` und `~/.aws/`
   - Cloud-Metadata-Endpunkt `169.254.169.254` (AWS/Azure IMDS) — Diebstahl temporärer Instanz-Credentials
   - VPN-Configs und CI/CD-Pipeline-Secrets

   *Stealer dieser Klasse zielen typischerweise auch auf Browser-Profile und Passwort-Manager-Datenbanken (1Password, LastPass, Bitwarden) — Risiko erhöht, falls die kompromittierte Maschine ein Dev-Rechner war. Für diesen konkreten Stealer ist das in den Primärquellen jedoch nicht explizit bestätigt.*

2. **Heimliche Übertragung:** Die gesammelten Daten wurden per HTTPS an die C2-Domain `flipboxstudio.info` (Exfiltrations-Endpunkt `/exfil`) hochgeladen, um Detection zu vermeiden.

3. **Kein offensichtlicher Schaden sofort:** Das Backdoor-Skript sabotierte Ihre Anwendung nicht, zerstörte keine Daten, verlangte kein Lösegeld. Es arbeitete still und lautlos — was es besonders perfide machte.

**Die Implikation:** Falls Ihre Laravel-App eines dieser Pakete in einer kompromittierten Version eingebunden hatte, besitzt der Angreifer potenziell:
- Zugang zu Ihrer Datenbank (über DB-Credentials)
- GitHub/GitLab-Tokens (Zugang zu allen Ihren Repositorys)
- Cloud-Zugangsdaten (AWS-Keys, Azure-Credentials, GCP-Service-Accounts)
- SSH-Keys für Server-Admin-Zugriff
- API-Keys für SaaS-Tools (Stripe, Twilio, SendGrid etc.)

Ein einzelnes kompromittiertes Paket = potenzieller Zugang zu Ihrer **gesamten Infrastruktur-Kette**.

---

## Hat es mich getroffen? Self-Check-Befehle

Hier sind konkrete Befehle zum Kopieren. Diese funktionieren auf Linux/macOS; Windows-Nutzer können bash via Git Bash oder WSL nutzen — oder PowerShell-Äquivalente (siehe Punkt 3).

### 1. Composer-Audit durchführen

```bash
cd /pfad/zu/ihrer/app
composer audit
```

**Wichtig:** `composer audit` (seit Composer 2.4) prüft nur Pakete, die in der Packagist Security Advisory-Datenbank registriert sind. Stand 23.05.2026 ist nicht garantiert, dass alle vier Laravel-Lang-Pakete bereits dort gelistet sind — daher den direkten Lockfile-Check (Punkt 2) zusätzlich ausführen.

### 2. composer.lock direkt prüfen

```bash
grep -A2 -E '"name": "laravel-lang/(lang|http-statuses|attributes|actions)"' composer.lock
```

Notieren Sie die Versionsnummern. Falls einer dieser Pakete auftaucht, prüfen Sie die Version gegen die offiziellen Maintainer-Advisories (siehe Quellen am Artikelende).

### 3. Auf Ihren **Produktionsservern** nach IoCs (Indicators of Compromise, Spuren eines Angriffs) suchen:

```bash
# Drop-Verzeichnis des Stealers (Linux/macOS)
ls -la "${TMPDIR:-/tmp}/.laravel_locale/" 2>/dev/null && echo "WARNUNG: IoC gefunden!"

# Windows-Variante (cmd.exe / PowerShell)
dir %TEMP%\DebugChromium.exe 2>nul && echo WARNUNG IoC gefunden

# C2-DNS-Auflösung in vorhandenen DNS-Logs prüfen
grep -i 'flipboxstudio' /var/log/dnsmasq.log /var/log/named/* 2>/dev/null

# Verdächtige PHP-Child-Prozesse mit Netz-Aktivität
ps aux | grep -iE 'php.*(curl|wget)' | grep -v grep

# Logs auf Exfil-Verbindungen prüfen
grep -E 'flipboxstudio|169\.254\.169\.254' /var/log/auth.log /var/log/syslog 2>/dev/null | tail -20
```

### 4. Für Python-Nutzer (pip-Äquivalent):

```bash
pip install pip-audit   # einmalig
pip-audit               # in jedem Projektverzeichnis
```

`pip-audit` ist ein separates PyPA-Tool (nicht im pip-Kern enthalten).

### 5. Für Node.js-Nutzer:

Laravel-Lang ist PHP-only; ein direkter Treffer auf npm ist nicht zu erwarten. Generischer JS-Stack-Audit:

```bash
npm audit --audit-level=high
npm outdated
```

### IoCs (Indicators of Compromise) — Stand 23.05.2026

Folgende IoCs sind durch Aikido Security und Socket.dev öffentlich verifiziert:

- **C2-Domain:** `flipboxstudio.info` (Payload-Abruf: `/payload`, Exfiltration: `/exfil`)
- **Eingeschleuste Datei in Paketen:** `src/helpers.php` (gehookt via `composer.json` → `autoload.files`)
- **Drop-Verzeichnis (Linux/macOS):** `$TMPDIR/.laravel_locale/` mit zufällig benannten `.php`- und `.vbs`-Files (8–12 Hex-Zeichen)
- **Drop-Artefakt (Windows):** `DebugChromium.exe` im temp-Verzeichnis plus `.vbs`-Launcher
- **Cloud-Metadata-Zugriff:** Requests an `169.254.169.254` (AWS/Azure IMDS) aus PHP-Prozess
- **Attacker-Identität:** Bislang nicht öffentlich attribuiert (Stand 23.05.2026)

Falls Sie einen dieser IoCs finden, **isolieren Sie die Maschine sofort und kontaktieren Sie Ihre IT-Sicherheit.**

---

## Was Sie jetzt tun müssen: Sofortmaßnahmen

### Phase 1: Vulnerabilität schließen (heute)

1. **Composer-Dependencies updaten:**
   ```bash
   composer update laravel-lang/lang laravel-lang/http-statuses laravel-lang/attributes laravel-lang/actions
   ```
   Die Maintainer sollten gepatchte Versionen veröffentlicht haben — prüfen Sie die jeweilige `CHANGELOG.md` und das Datum des neuesten Tags.

2. **Staging/Dev-Umgebungen neu bauen** (von aktuellem Code):
   ```bash
   composer install
   ```

3. **Production-Deployment:** Koordinieren Sie mit Ihrem DevOps-Team ein Deployment des gepatchten Codes.

### Phase 2: Credential-Rotation (innerhalb von 48 Stunden)

**Reihenfolge ist wichtig.** Priorisieren Sie nach **Blast-Radius** und **Detection-Window**: Cloud- und CI/CD-Tokens haben oft kurze Lebensdauer, hohe Reichweite (Container starten, IAM-Nutzer anlegen, Code-Manipulation) und sind das Primärziel laut Aikido-Bericht. Laravel `APP_KEY` zuerst zu rotieren ist disruptiv (Sessions invalidieren) und schützt vor _Re-Use_ historischer Daten — kein akut-blockendes Risiko.

1. **CI/CD-Pipeline-Tokens & Cloud-API-Keys** *(höchstes Lateral-Movement-Risiko)*
   - AWS Access Keys / Azure Service Principals / GCP Service Accounts
   - GitHub-Actions-, GitLab-CI-, Jenkins-Tokens

2. **VCS-Tokens** (GitHub/GitLab Personal Access Tokens, kurz PATs, sowie Deploy-SSH-Keys für CI/CD)
   - Regenerieren Sie alle PATs in der jeweiligen Account-Konsole
   - Rotieren Sie Deploy-Keys, die in CI/CD-Pipelines hinterlegt sind

3. **Datenbankpasswörter** — Falls die DB von außen erreichbar ist
   - Ändern Sie alle DB-Nutzer-Passwörter in `.env` und Ihrer Password-Management-Lösung
   - Testverbindung: `mysql -u user -p -h dbhost < /dev/null`

4. **Laravel `APP_KEY`** — Verschlüsselungsgeheimnis (könnte historische Session-Daten dechiffrieren)
   ```bash
   php artisan key:generate  # neuen Key generieren
   ```
   ⚠️ **Warnung:** Das invalidiert aktive Sessions. Tun Sie das außerhalb von Business-Stunden.
   ⚠️ **Sonderfall:** Falls Datenbankfelder via Laravel-`encrypted`-Casts mit dem alten `APP_KEY` verschlüsselt wurden, MUSS der `APP_KEY` **vor** der DB-Passwort-Rotation gewechselt und das Re-Encrypt-Verfahren der Maintainer-Docs befolgt werden — sonst sind verschlüsselte Felder verloren.

5. **SSH-Keys für Server-Admin-Zugriff**
   - Auf allen Servern `~/.ssh/authorized_keys` überprüfen und unbekannte Keys entfernen
   - Neue lokal-generierte Keys für Administrative Tasks erstellen

6. **SaaS API-Keys und Webhook-Secrets** (Stripe, Twilio, SendGrid, etc.)
   - Für jeden Third-Party-Service: Token in der Admin-Console regenerieren

### Phase 3: Monitoring und Response-Plan (diese Woche)

- **Audit-Logs prüfen:** Hat jemand die verdächtig-rotierten Credentials verwendet?
  ```bash
  grep 'failed password\|Invalid user' /var/log/auth.log | wc -l
  ```

- **Cloud-Umgebung auditieren:** AWS CloudTrail, Azure Activity Log, GCP Cloud Audit Logs nach verdächtigen API-Aufrufen
  - Suchen Sie nach Ressourcen-Löschen, IAM-Änderungen, ungewöhnlichen API-Keys-Generierungen

- **Incident-Responder kontaktieren** (falls Sie eine größere Organisation sind): CISO, Security-Team, ggf. externe Forensik-Dienstleister

---

## Wie Sie sich strukturell schützen: Langfristige Maßnahmen

Dieser Vorfall war möglich, weil Composer standardmäßig die **neueste passende Version** eines Pakets installiert — ohne dass Sie kontrollieren können, welche Versionen Sie bekommen.

### 1. **Software Bill of Materials (SBOM) pflegen**

Eine SBOM ist eine strukturierte Liste aller Dependencies mit exakten Versionen. Das klingt bürokratisch, aber es ist Ihre Versicherungspolice:

```bash
composer show > sbom.txt  # einfach, aber genug für KMUs
# oder mit Tools wie CycloneDX (formelles SBOM-Format für Compliance-Audits)
```

Versionieren Sie diese Datei in Git. Bei Verdacht können Sie schnell sehen, welche Version Sie damals hatten.

### 2. **composer.lock Hashing implementieren**

Benutzen Sie `composer install` (nicht `composer update`) in Ihrem CI/CD. Das zieht immer exakte Versionen aus `composer.lock`:

```bash
composer install --no-dev  # Production
```

**Wichtig:** `composer.lock` selbst in Git versionieren — das ist Ihre Versionsgarantie.

### 3. **Ähnliche Muster in Ihrem Stack schließen**

- **Python:** `pip install -r requirements.txt` (nicht `pip install` ohne Lock) — und `pip install pip-tools && pip-compile --generate-hashes requirements.in` für Hash-Validierung
- **Node.js:** `npm ci` (nicht `npm install`) — `package-lock.json` ist Ihre Versionsgarantie
- **Docker:** Images by Digest pinnen, nicht by Tag:
  ```dockerfile
  FROM node:20@sha256:abc123...  # Hash, nicht 'node:20'
  ```

### 4. **Automated Dependency Scanning**

Tools wie **Dependabot** (GitHub native), **Renovate**, oder **Snyk** scannen Ihre Dependencies täglich auf bekannte Vulnerabilities. Nicht perfekt — wie dieser Vorfall zeigt, neue Exploits werden oft nicht sofort gemeldet — aber besser als nichts.

### 5. **Build-Pipeline-Isolierung**

- Ihr CI/CD-Container sollte **keinen Zugriff** auf Produktions-Secrets haben
- Wenn ein Paket bösartigen Code ausführt, sollte dieser keine wertvollen Credentials finden
- **Secret-Rotation auf Basis von Builds:** Jeder Build verwendet kurzlebige, job-spezifische Tokens (z.B. via OpenID Connect/OIDC bei GitHub Actions — ein Token-Standard für kurzlebige CI-Credentials, nicht hardgecodete PATs)

### 6. **Code-Review für Critical Dependencies**

Bei Laravel-Lang hätte ein Audit der `autoload.files` offenbart, dass automatisch `.php`-Dateien geladen werden. Für Security-sensitive Packages: Ein Review der `composer.json` und `src/helpers.php` wäre sinnvoll gewesen.

---

## BSI-Grundschutz Bezug: Relevante Bausteine

Falls Sie in Deutschland tätig sind und IT-Grundschutz umsetzen, bieten folgende Bausteine den Rahmen für Supply-Chain-Sicherheit und die Reaktion nach einem Vorfall:

- **CON.8 — Software-Entwicklung:** Adressiert sichere Eigenentwicklung, inklusive Sorgfaltspflichten bei der Auswahl externer Komponenten und Werkzeuge — direkt einschlägig für Composer-, npm- und pip-Abhängigkeiten.
- **OPS.1.1.6 — Software-Tests und Freigaben:** Definiert das Test- und Freigabe-Verfahren für Software, das auch für Dritt-Abhängigkeiten greift. Eine dokumentierte Freigabe ohne SBOM ist schwer belastbar zu führen.
- **OPS.1.1.3 — Patch- und Änderungsmanagement** und **CON.3 — Datensicherungskonzept** geben den Rahmen für die Rotations- und Recovery-Phase nach einem bestätigten Vorfall.
- **OPS.2.4 — Cloud-Nutzung:** Falls Ihre Laravel-App in der Cloud läuft, ist die Exfiltration von Cloud-Credentials und IMDS-Daten die Kernzielscheibe — entsprechende IAM-Hardening-Anforderungen werden hier adressiert.

Genaue Wortlaute und Anforderungs-Nummern: [BSI IT-Grundschutz-Kompendium](https://www.bsi.bund.de/grundschutz).

**Konkrete Handlung:** Falls Sie an Grundschutz-Compliance arbeiten, nutzen Sie diesen Vorfall als Begründung für die nächsten Audits — "Supply-Chain-Sicherheit ist nicht optional."

---

## Call-to-Action

Dieser Vorfall ist ein Weckruf. Supply-Chain-Attacks werden Standard; das Laravel-Lang-Paket ist nur die nächste prominente Geschichte in einer langen Reihe (SolarWinds, log4j, XZ-Utils).

**Ihre nächsten Schritte:**

1. ✅ **Self-Check durchführen** — Nutzen Sie die Befehle oben. 15 Minuten.
2. ✅ **Credentials rotieren** — priorisiert. 2–4 Stunden organisatorisch.
3. ✅ **Struktur überprüfen** — SBOM, Lockfile-Strategie, CI/CD-Isolation. Diese Woche oder Monat.

Falls Sie nicht wissen, wo Sie anfangen sollen, oder Ihre Organisation Hilfe bei der Identifikation von Vulnerabilities braucht:

**[Erstgespräch buchen](https://cal.com/cyber-aspis)** — Cyber Aspis bietet Supply-Chain-Audits und Security-Reviews für KMUs an. Erste 30 Minuten kostenlos; wir prüfen Ihre Build-Pipeline und geben konkrete Handlungsschritte.

---

## Quellen & Weiterführende Ressourcen

- [Aikido Security — Supply-Chain-Attack on Laravel-Lang Packages with Credential Stealer (Mai 2026)](https://www.aikido.dev/blog/supply-chain-attack-targets-laravel-lang-packages-with-credential-stealer)
- [Socket.dev — Laravel-Lang Compromise (Mai 2026)](https://socket.dev/blog/laravel-lang-compromise)
- [BSI IT-Grundschutz-Kompendium — CON.8 & OPS.1.1.6](https://www.bsi.bund.de/grundschutz)
- [Composer Best Practices — `install` vs. `update`](https://getcomposer.org/doc/03-cli.md#install)
- [GitHub Security Advisory Database](https://github.com/advisories)
