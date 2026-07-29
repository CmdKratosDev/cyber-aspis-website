---
title: "Selbst-Audit-Checkliste: Supply-Chain-Risiken in Ihrer Build-Pipeline"
date: 2026-05-23
draft: false
description: "20-Punkte-Selbst-Audit-Checkliste zum Auditieren Ihrer CI-Pipeline auf Supply-Chain-Vulnerabilities. Für PHP/Composer, Python/pip, Node.js/npm, Docker und CI/CD-Secrets."
tags: ["supply-chain", "audit", "ci-cd", "security-checklist", "kmu"]
author: "Cyber Aspis"
---

## Bevor Sie anfangen

Diese Checkliste richtet sich an Entwickler-Teams und IT-Verantwortliche mit direktem Zugriff auf Repository und CI/CD-Pipeline, nicht an Geschäftsführer ohne eigenes IT-Team. Wenn Sie die Befehle unten nicht selbst ausführen können, ist ein begleiteter Supply-Chain-Audit der passendere Weg.

**Autorisierung:** Prüfen Sie ausschließlich Systeme, die Ihnen gehören, oder Systeme, für die Ihnen eine schriftliche Freigabe des Betreibers vorliegt. Die Punkte 16 bis 20 greifen per SSH auf Produktivsysteme zu. Ohne Freigabe kann bereits der Zugriff strafbar sein, auch wenn Sie nur lesen. Bei fremd betriebenen Servern (Hoster, Managed Service, Konzernmutter) holen Sie die Freigabe vorher schriftlich ein.

Alle Befehle in dieser Checkliste sind lesend. Keiner verändert Daten, keiner greift ein System an.

---

## Der Vorfall, auf den sich diese Checkliste bezieht

Am 22. Mai 2026 ab 22:32 UTC hat ein Angreifer mit Schreibzugriff auf die GitHub-Organisation Laravel-Lang in vier Composer-Paketen (`laravel-lang/lang`, `laravel-lang/http-statuses`, `laravel-lang/attributes`, `laravel-lang/actions`) **sämtliche bestehenden Git-Tags per Force-Push auf eigene Commits umgebogen**, insgesamt rund 700 Versionen. Bei `laravel-lang/lang` allein waren es 502 Tags, alles innerhalb weniger Stunden.

Die manipulierten Commits fügten `src/helpers.php` in die `autoload.files`-Liste der `composer.json` ein. Damit wurde der Schadcode bei jedem Anwendungsstart automatisch geladen, ohne dass der eigene Code das Paket überhaupt aufrufen musste. Der Payload sammelte Cloud- und CI/CD-Credentials, GitHub- und GitLab-Tokens, SSH-Keys, Docker-Auth, `.env`-Inhalte, Kubernetes-Service-Account-Tokens und Vault-Tokens, fragte den EC2-Metadaten-Endpunkt `169.254.169.254` nach IAM-Credentials ab und lud alles an die C2-Domain `flipboxstudio.info` (`/payload` zum Nachladen, `/exfil` zum Ausleiten). Packagist entfernte die bösartigen Versionen am 23.05.2026.

**Für die Prüfung entscheidend:** Weil bestehende Tags überschrieben und die Versionsnummern später wieder auf legitimen Code zurückgemappt wurden, sagt die Versionsnummer im `composer.lock` nichts über eine Kompromittierung aus. Belastbar ist nur der Commit-SHA im Feld `source.reference`. Quellen dazu am Ende dieser Seite.

---

## Anleitung

Arbeiten Sie die Punkte auf Ihrer Entwicklungsmaschine und auf Ihren CI/CD-Servern (GitHub Actions, GitLab CI, Jenkins) ab. Ersetzen Sie Platzhalter wie `<projektroot>`, `user@prodserver` und `OWNER/REPO` durch Ihre echten Werte.

Die Befehle setzen eine POSIX-Shell voraus. Unter Windows nutzen Sie Git Bash oder WSL. Für die Punkte 02 und 13 brauchen Sie `jq`, für Punkt 15 optional die GitHub CLI (`gh`).

Wenn ein Punkt anschlägt, dokumentieren Sie den Befund im Abschnitt "Befundsdokumentation". Beachten Sie vorher den Hinweis zum Vorgehen bei Kompromittierungs-Spuren vor Punkt 16.

---

## Checkliste

### PHP / Composer

#### 01 Composer-Audit durchführen

```bash
cd <projektroot> && composer audit
```

**Erwartetes Ergebnis:** Keine Vulnerabilities. Falls doch, betroffenes Paket updaten. `composer audit` kennt nur Advisories, die in der Packagist-Datenbank registriert sind, deshalb ist Punkt 02 zusätzlich nötig. ☐

#### 02 Laravel-Lang-Pakete über den Commit-SHA prüfen (Vorfall 22.05.2026)

```bash
jq -r '(.packages + (."packages-dev" // []))[] | select(.name|startswith("laravel-lang/")) | "\(.name) \(.version) \(.source.reference) \(.source.url)"' composer.lock
```

Jeden ausgegebenen SHA gegen die Upstream-Historie prüfen (Repo-URL steht in der Ausgabe oben):

```bash
curl -s https://api.github.com/repos/Laravel-Lang/lang/commits/<SHA> | jq '{autor: .commit.author.name, mail: .commit.author.email, datum: .commit.author.date}'
```

**Erwartetes Ergebnis:** Entweder gar keine Ausgabe beim ersten Befehl (Pakete nicht installiert), oder ein Commit, dessen Autor ein bekannter Maintainer ist und dessen Datum **vor dem 22.05.2026 22:32 UTC** liegt.

Die Versionsnummer allein ist kein Nachweis: Der Angreifer hat bestehende Tags überschrieben, dieselbe Versionsnummer kann also sauberen oder bösartigen Code bezeichnen. Snyk formuliert es so, dass Versionsnummern allein nicht ausreichen, um eine Betroffenheit zu bestätigen. Entscheidend ist der Commit-SHA aus `source.reference`.

Zwei Merkmale entlarven die eingeschmuggelten Commits: Sie tragen alle die Autor-Identität `Your Name <you@example.com>`, und sie sind von keinem Branch des Upstream-Repositorys erreichbar, nur die umgebogenen Tags zeigten darauf.

Achten Sie dabei auf die Auswertung: Die GitHub-API liefert einen solchen Commit weiterhin mit HTTP 200 aus, weil er im Objektspeicher des Repositorys liegt. Eine erfolgreiche Antwort ist deshalb kein Nachweis für Unbedenklichkeit. Aussagekräftig sind nur Autor-Identität und Datum. Ein HTTP 404 ist ebenfalls ein Warnsignal, dann liegt der Commit gar nicht im angegebenen Repository. Die publizierten SHAs der bösartigen Commits stehen in der StepSecurity-Analyse (Quellen unten), dort können Sie Ihre Werte direkt abgleichen. ☐

#### 03 composer.lock in Git versioniert?

```bash
git ls-files composer.lock
```

**Erwartetes Ergebnis:** Der Befehl gibt `composer.lock` aus. Ohne versionierte Lock-Datei können Sie nach einem Vorfall nicht rekonstruieren, welche Commits Sie ausgeliefert haben. ☐

#### 04 composer.lock-Integrität prüfen

```bash
composer validate && composer install --dry-run
```

**Erwartetes Ergebnis:** Keine Fehler, Lock-Datei passt zur `composer.json`. ☐

#### 05 `autoload.files` aller installierten Pakete sichten

```bash
grep -H '"files"' vendor/*/*/composer.json | head -20
```

Bei sehr großem `vendor`-Baum stattdessen:

```bash
find vendor -mindepth 3 -maxdepth 3 -name composer.json -exec grep -l '"files"' {} +
```

**Erwartetes Ergebnis:** Sie kennen jedes Paket, das PHP-Dateien beim Anwendungsstart automatisch lädt, und haben die Einträge gesichtet.

Zwei Pfadebenen sind Pflicht, weil Composer nach `vendor/<hersteller>/<paket>/composer.json` installiert. Ein Muster mit nur einer Ebene (`vendor/*/composer.json`) trifft in einem normalen Projekt gar nichts und liefert eine falsche Entlastung. `autoload.files` war der Ausführungsvektor des Mai-Vorfalls, deshalb lohnt der genaue Blick. ☐

### Python / pip

#### 06 pip-audit durchführen

```bash
pipx install pip-audit
pip-audit -r requirements.txt
```

**Erwartetes Ergebnis:** Keine Vulnerabilities gemeldet.

`pipx` statt `pip install`, damit das Audit-Tool nicht selbst in Ihre Projekt-Umgebung wandert. Beachten Sie dabei: `pip-audit` prüft ohne Argumente die Umgebung, in der es läuft. Via `pipx` installiert wäre das die isolierte pipx-Umgebung und nicht Ihr Projekt, deshalb hier explizit die Requirements-Datei. Wollen Sie die installierte Umgebung selbst scannen, muss `pip-audit` in dieses venv hinein. ☐

#### 07 requirements.txt mit Hashes?

```bash
grep -c -- '--hash=sha256:' requirements.txt
```

**Erwartetes Ergebnis:** Eine Zahl größer 0. Ergibt der Befehl `0`, gibt es keine Hash-Bindung, dann setzen Sie Punkt 08 um.

Der Wert zählt Zeilen mit Hash-Eintrag und nicht Pakete: `pip-compile --generate-hashes` schreibt pro Paket oft mehrere Hashes, einen je Wheel oder Sdist. Die Zahl liegt also normalerweise über Ihrer Paketanzahl. ☐

#### 08 pip-compile mit Hash-Validierung nutzen

```bash
pipx install pip-tools
pip-compile --generate-hashes requirements.in
```

**Erwartetes Ergebnis:** `requirements.txt` wurde mit `--generate-hashes` erzeugt, `pip install -r requirements.txt` akzeptiert danach nur noch exakt diese Artefakte. ☐

### Node.js / npm

#### 09 npm-Audit durchführen

```bash
npm audit --audit-level=high
```

**Erwartetes Ergebnis:** Keine kritischen oder hohen Vulnerabilities. ☐

#### 10 package-lock.json versioniert?

```bash
git ls-files package-lock.json
```

**Erwartetes Ergebnis:** Der Befehl gibt `package-lock.json` aus. ☐

#### 11 `npm ci` statt `npm install` in CI/CD

```bash
for f in .github/workflows .gitlab-ci.yml Jenkinsfile bitbucket-pipelines.yml; do
  [ -e "$f" ] && grep -rn -e 'npm install' -e 'npm i ' "$f"
done
```

**Erwartetes Ergebnis:** Keine Ausgabe. `npm install` darf Versionen auflösen und die Lock-Datei ändern, `npm ci` installiert strikt aus `package-lock.json`. Die `[ -e ]`-Abfrage verhindert die "No such file"-Meldungen, die ein direktes `grep -r` über nicht vorhandene Pfade erzeugt. ☐

### Docker / Container-Images

#### 12 Base-Images per Digest pinnen (Empfehlung)

```bash
grep -inE '^[[:space:]]*FROM' Dockerfile | grep -v '@sha256'
```

**Erwartetes Ergebnis (Empfehlung, kein Mangel):** Die Ausgabe zeigt alle Base-Images, die über einen Tag statt über einen Digest referenziert sind. `FROM node:20` ist kein Fehler, aber ein veränderlicher Verweis: Derselbe Tag kann morgen auf ein anderes Image zeigen, Ihr Build ist damit nicht reproduzierbar. Für Produktions-Builds ist `FROM node:20@sha256:...` die stärkere Variante, in Entwicklungs-Dockerfiles ist der Tag oft eine bewusste Entscheidung. Bewerten Sie das pro Image, nicht als Pauschalbefund. ☐

#### 13 Container-Images auf einbackene Laravel-Lang-Pakete prüfen

```bash
for img in $(docker image ls --format '{{.Repository}}:{{.Tag}}' | grep -v '<none>'); do
  docker create --name aspis_scan "$img" /bin/true >/dev/null 2>&1 || continue
  printf '%s\t%s\n' "$(docker export aspis_scan | tar -tf - 2>/dev/null | grep -c 'vendor/laravel-lang/')" "$img"
  docker rm -f aspis_scan >/dev/null
done
```

Bei einem Treffer die eingebackene Lock-Datei herausholen und mit Punkt 02 auswerten:

```bash
docker create --name aspis_scan <image> /bin/true && docker cp aspis_scan:/var/www/html/composer.lock ./lock-aus-image.json; docker rm -f aspis_scan
```

**Erwartetes Ergebnis:** Überall `0`. Jede Zahl größer 0 heißt: Dieses Image enthält die Pakete. Prüfen Sie den Commit-SHA in der eingebackenen `composer.lock` nach Punkt 02 und bauen Sie das Image neu.

Der Punkt ist wichtig, weil ein Fix im Repository ein bereits gebautes Image nicht sauber macht: `composer.lock` und `vendor/` liegen im Image-Layer. Ein Container, der noch aus dem alten Image läuft, führt weiter den alten Code aus. `docker create` legt den Container nur an und startet ihn nicht, es wird also kein Code aus dem Image ausgeführt. Bei großen Images dauert `docker export` einige Minuten pro Image. ☐

### CI/CD & Secrets

#### 14 Secrets-Scope in CI/CD beschränkt

```bash
grep -rnE 'secrets\.[A-Za-z0-9_]+|^[[:space:]]*env:' .github/workflows 2>/dev/null
```

**Erwartetes Ergebnis:** Jedes Secret wird nur in dem Job referenziert, der es braucht. Ein `env:`-Block auf Workflow-Ebene, der alle Secrets in jeden Step spiegelt, ist ein Befund: Genau diese Umgebungsvariablen liest ein Stealer wie der aus dem Mai-Vorfall zuerst aus. ☐

#### 15 SSH-Key-Inventar erstellen

```bash
for k in ~/.ssh/id_*; do
  case "$k" in *.pub) continue;; esac
  if ssh-keygen -y -P '' -f "$k" >/dev/null 2>&1; then
    echo "OHNE PASSPHRASE  $k  $(ssh-keygen -lf "$k" 2>/dev/null)"
  else
    echo "passphrase-geschuetzt  $k"
  fi
done
```

Hinterlegte Deploy-Keys eines Repositorys auflisten und gegen dieses Inventar abgleichen:

```bash
gh api repos/OWNER/REPO/keys --jq '.[] | "\(.title)\tread_only=\(.read_only)\t\(.created_at)"'
```

**Erwartetes Ergebnis:** Jeder private Key ist passphrase-geschützt oder liegt in Agent, Smartcard oder HSM. Zu jedem hinterlegten Deploy-Key gibt es einen dokumentierten Zweck und einen Verantwortlichen. Schreibberechtigte Deploy-Keys (`read_only=false`) existieren nur dort, wo sie gebraucht werden.

**Wichtig zur Einordnung:** Normale SSH-Schlüssel haben kein Verfallsdatum, `ssh-keygen -l` zeigt nur einen Fingerprint. Eine Gültigkeitsdauer haben ausschließlich SSH-Zertifikate, die von einer eigenen CA ausgestellt werden (`ssh-keygen -s ca_key -V +12w id_ed25519.pub`). Solange Sie mit reinen Schlüsseln arbeiten, ersetzt kein technisches Ablaufdatum den Kalendereintrag zur Rotation und die regelmäßige Sichtung der hinterlegten Keys. ☐

### Produktionsserver (SSH)

Nur relevant, wenn Sie SSH-Zugriff auf den Produktionsserver haben, und nur mit der Autorisierung aus dem Abschnitt "Bevor Sie anfangen".

> ### Wenn einer der folgenden Punkte anschlägt
>
> Die Punkte 16 bis 20 suchen nach Spuren einer Kompromittierung. Ein Treffer ist ein laufender Sicherheitsvorfall und kein Konfigurationsfehler. Räumen Sie dann nicht auf, sondern gehen Sie so vor:
>
> 1. **System isolieren, nicht putzen.** Wer Dateien löscht, Prozesse killt oder unbekannte Keys entfernt, warnt den Angreifer und vernichtet die Beweise, die Sie später für Meldung, Versicherung und Ursachenanalyse brauchen. Netzwerk trennen oder in ein isoliertes Segment hängen, System möglichst nicht neu starten.
> 2. **Beweise sichern, bevor Sie etwas ändern.** Wenn möglich ein Speicherabbild ziehen, danach Logs, Container-Images, `composer.lock` und relevante Dateisystem-Stände wegkopieren. Zeitstempel und Prüfsummen mitschreiben.
> 3. **Erst danach rotieren**, in der Reihenfolge aus der Schnell-Referenz weiter unten.
> 4. **Meldepflicht prüfen.** Können personenbezogene Daten betroffen sein und besteht dadurch ein Risiko für die betroffenen Personen, ist der Vorfall nach Art. 33 DSGVO binnen 72 Stunden der zuständigen Aufsichtsbehörde zu melden. Die Frist läuft ab Kenntnisnahme, nicht ab Abschluss Ihrer Analyse. Auch die vertraglichen Meldefristen gegenüber Kunden und Versicherer prüfen.
> 5. **Hilfe holen, wenn die Routine fehlt.** Der erste Zugriff nach einem Treffer entscheidet darüber, ob der Vorfall später noch nachvollziehbar ist. Wer diesen Ablauf nicht eingeübt hat, sollte Unterstützung dazuholen, bevor am System gearbeitet wird.

#### 16 IoC-Check auf das Drop-Verzeichnis des Stealers

```bash
ssh user@prodserver 'ls -la "${TMPDIR:-/tmp}/.laravel_locale/" 2>/dev/null || echo "kein Drop-Verzeichnis vorhanden"'
```

**Erwartetes Ergebnis:** Die Ausgabe lautet `kein Drop-Verzeichnis vorhanden`.

Wichtig zur Aussagekraft: Der Loader legte seine Artefakte unter `$TMPDIR/.laravel_locale/` ab (zufällig benannte `.php`-Datei aus 12 Hex-Zeichen) und löschte sie laut Analyse innerhalb weniger Sekunden nach dem Start wieder. Ein leeres Ergebnis ist deshalb keine Entlastung. Der belastbare Nachweis bleibt der Commit-SHA aus Punkt 02. ☐

#### 17 Prozesse mit gelöschtem oder in /tmp liegendem Binary

```bash
ssh user@prodserver 'sudo ls -l /proc/*/exe 2>/dev/null | grep -iE "deleted|/tmp/"'
```

Zusätzlich elternlose PHP-Prozesse suchen:

```bash
ssh user@prodserver 'ps -eo pid,ppid,etime,comm | grep -E "^[[:space:]]*[0-9]+[[:space:]]+1[[:space:]]" | grep -i php'
```

**Erwartetes Ergebnis:** Beide Befehle liefern keine Ausgabe.

Der erste Befehl findet Prozesse, deren ausführbare Datei gelöscht wurde oder aus `/tmp` stammt. Genau so verhielt sich die zweite Stufe des Stealers: eine ELF-Datei unter `/tmp/.<8 Hex-Zeichen>`, die sich nach dem Start selbst entfernte. Der zweite Befehl zeigt PHP-Prozesse mit `ppid=1`, also solche, deren Elternprozess verschwunden ist. Beides sind Auffälligkeiten, die eine Analyse verdienen, und keine automatischen Beweise. ☐

#### 18 SSH `authorized_keys` prüfen

```bash
ssh user@prodserver 'for f in /home/*/.ssh/authorized_keys /root/.ssh/authorized_keys; do [ -f "$f" ] && echo "== $f" && ssh-keygen -lf "$f"; done'
```

**Erwartetes Ergebnis:** Ausschließlich Fingerprints, die Sie aus dem Inventar in Punkt 15 kennen.

Bei einem unbekannten Key: **nicht sofort löschen.** Datei mit Zeitstempeln sichern, `~/.ssh`-Verzeichnis und Login-Historie (`last`, `journalctl -u ssh`) mitsichern, dann nach dem Hinweis oben vorgehen. Das Entfernen ist Teil der koordinierten Reaktion und nicht der erste Schritt. ☐

#### 19 `.env`-Dateien und ihre Ablage prüfen

```bash
find . -name '.env*' -not -path './.git/*' -not -path './vendor/*' -not -path './node_modules/*' -exec ls -l {} +
git ls-files | grep -E '(^|/)\.env'
```

**Erwartetes Ergebnis:** Der zweite Befehl gibt höchstens `.env.example` aus, also eine Vorlage ohne echte Werte. Jeder andere Treffer ist ein Befund.

`.env.local`, `.env.production` und ähnliche Varianten enthalten typischerweise echte lokale Secrets und gehören damit nicht in die Versionskontrolle, nicht in das Web-Verzeichnis und nur mit Rechten `600` auf die Platte. Der erste Befehl zeigt genau diese Rechte mit an. Ob eine Datei zusätzlich über das Web erreichbar ist, prüfen Sie an Ihrer eigenen Domain mit `curl -sI https://ihre-domain.example/.env`, erwartet wird `404` oder `403`. ☐

#### 20 Egress-, DNS- und Webserver-Logs auf den C2-Callout prüfen

```bash
ssh user@prodserver 'grep -ril flipboxstudio /var/log/nginx /var/log/apache2 /var/log/squid /var/log/named /var/log/dnsmasq.log /var/log/unbound.log 2>/dev/null; journalctl -u systemd-resolved --since 2026-05-22 2>/dev/null | grep -i flipboxstudio'
```

**Erwartetes Ergebnis:** Keine Ausgabe.

Der HTTPS-Callout einer PHP-Anwendung an `flipboxstudio.info` landet nicht in `/var/log/auth.log` oder `/var/log/syslog`, dort protokolliert das System Anmeldungen und Dienstmeldungen. Sichtbar wird ausgehender Verkehr nur in den Logs der Stelle, die ihn vermittelt: lokaler Resolver oder DNS-Server, Forward-Proxy, Egress-Firewall, gegebenenfalls Netflow. Passen Sie die Pfade an Ihre Umgebung an.

Wenn keine dieser Quellen existiert, ist das selbst der Befund: Ohne Egress- oder DNS-Protokollierung können Sie einen Datenabfluss weder feststellen noch ausschließen. Das ist die häufigste Lücke in KMU-Umgebungen und die lohnendste Sofortmaßnahme nach diesem Vorfall.

Für den Zugriff auf `169.254.169.254` gilt eine Besonderheit: Auf AWS erfasst VPC Flow Logs den Verkehr zum Instanz-Metadaten-Dienst laut Dokumentation nicht, ein Suchlauf dort ist also wirkungslos. Prüfen Sie stattdessen, ob IMDSv2 erzwungen ist:

```bash
aws ec2 describe-instances --query 'Reservations[].Instances[].{Id:InstanceId,IMDS:MetadataOptions.HttpTokens}' --output table
```

Erwartet wird `required` für jede Instanz. Mit IMDSv1 genügt ein einfacher GET aus dem Anwendungsprozess, um IAM-Credentials abzuholen. ☐

---

## Befundsdokumentation

Für Konfigurationsbefunde aus den Punkten 01 bis 15 dokumentieren Sie strukturiert und rotieren anschließend. Bei Treffern in den Punkten 16 bis 20 gilt zuerst der Hinweis zum Vorgehen bei Kompromittierungs-Spuren: sichern und isolieren, dann dokumentieren, dann rotieren.

### Template

**Befund #[N]:**
- **Was:** [Beschreibung, z. B. "laravel-lang/lang mit SHA aus der Angriffsliste in composer.lock"]
- **Wo:** [Server, Datei, Image]
- **Wann gefunden:** [Datum, Uhrzeit]
- **Auswirkung:** [P0/P1/P2]
- **Kategorie:** [Konfigurationsbefund / Kompromittierungs-Spur]
- **Beweise gesichert:** [Was, wohin, mit welcher Prüfsumme]
- **Rotation-Plan:** [Was wann, in welcher Reihenfolge]
- **Verantwortlich:** [Name, Rolle]
- **Status:** Offen / In Bearbeitung / Abgeschlossen

### Beispiel

**Befund #1:**
- **Was:** `laravel-lang/lang`, `source.reference` entspricht einem der publizierten Angreifer-Commits, Autor `Your Name <you@example.com>`
- **Wo:** Produktionsserver web01 und web02, außerdem Image `app:2026-05-24`
- **Wann gefunden:** 2026-05-23, 10:30
- **Auswirkung:** P0, mögliche Exfiltration von Cloud- und DB-Credentials
- **Kategorie:** Kompromittierungs-Spur
- **Beweise gesichert:** `composer.lock`, `vendor/laravel-lang/`, Webserver- und DNS-Logs, Image-Export, alles mit SHA-256 in `incident-2026-05-23/`
- **Rotation-Plan:**
  1. web01 und web02 aus dem Loadbalancer nehmen, Egress sperren
  2. Beweissicherung abschließen
  3. Cloud-API-Keys und CI/CD-Tokens rotieren
  4. Image aus geprüftem Lockfile neu bauen, in Staging testen
  5. Deployment Off-Peak, danach VCS-Tokens und DB-Passwort rotieren
- **Verantwortlich:** [Name, Rolle]
- **Status:** In Bearbeitung

---

## Schnell-Referenz: Welche Secrets wann rotieren?

Priorisiert nach Blast-Radius und Detection-Window. Bei bestätigter Kompromittierung erst nach der Beweissicherung starten.

**Sofort (innerhalb von 2 Stunden)**
- Cloud-API-Keys (AWS Access Keys, Azure Service Principals, GCP Service Accounts), höchstes Lateral-Movement-Risiko
- CI/CD-Pipeline-Tokens (GitHub Actions, GitLab CI, Jenkins)
- Über IMDS ausgegebene Instanz-Rollen: betroffene Instanzen ersetzen, da temporäre Credentials an der Rolle hängen

**Heute (innerhalb von 8 Stunden)**
- VCS-Tokens (GitHub- und GitLab-PATs, Deploy-SSH-Keys für CI/CD), verhindert Code-Manipulation
- Datenbankpasswörter, vorrangig wenn die DB von außen erreichbar ist
- Laravel `APP_KEY`, Off-Peak, invalidiert aktive Sessions. Sonderfall bei `encrypted`-Casts: `APP_KEY` vor dem DB-Passwort rotieren und Re-Encrypt durchführen, sonst sind verschlüsselte Felder verloren
- Kubernetes-Service-Account-Tokens und Vault-Tokens

**Diese Woche**
- SSH-Keys für Server-Admin-Zugriff
- SaaS-API-Keys und Webhook-Secrets (Stripe, Twilio, SendGrid)
- Passwort-Manager-Master-Passwörter, falls ein Entwicklerrechner betroffen war

---

## Quellen zum Vorfall

- StepSecurity, Analyse mit Zeitleiste und Liste der bösartigen Commit-SHAs: `https://www.stepsecurity.io/blog/laravel-lang-supply-chain-attack`
- Snyk Advisory, Einordnung "Versionsnummern allein reichen nicht": `https://snyk.io/blog/laravel-lang-supply-chain-advisory/`
- Phoenix Security, technische Payload-Analyse und IoC-Liste: `https://phoenix.security/laravel-lang-composer-supply-chain-compromise-rce-backdoor/`
- Aikido Security, Erstmeldung zum Credential-Stealer: `https://www.aikido.dev/blog/supply-chain-attack-targets-laravel-lang-packages-with-credential-stealer`

## Weiterführende Ressourcen

- OWASP Dependency Check: `https://owasp.org/www-project-dependency-check/`
- CISA Known Exploited Vulnerabilities Catalog: `https://www.cisa.gov/known-exploited-vulnerabilities`
- National Vulnerability Database (NVD): `https://nvd.nist.gov/`
- GitHub Advisory Database: `https://github.com/advisories`

---

## Nächste Schritte

Wenn ein Ergebnis unklar bleibt oder Sie Ihre Auswertung gegengeprüft haben möchten, stehen Ihnen offen:

- das eigene Security-Team oder Ihr Dienstleister für den Betrieb
- Cyber Aspis für einen Supply-Chain-Audit. Das erste Gespräch dauert 30 Minuten und kostet nichts, danach entscheiden Sie, ob ein Audit sinnvoll ist
- bei bestätigter Kompromittierung ein Incident-Response-Dienstleister, bevor am System gearbeitet wird

**[Erstgespräch mit Cyber Aspis buchen](https://cal.com/cyber-aspis)**
