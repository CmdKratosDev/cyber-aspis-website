# Cyber Aspis IT-Security – Landing Page

Landing Page für [cyber-aspis.de](https://cyber-aspis.de) — Plain HTML/CSS + Nginx in Docker.

## Lokaler Dev-Start

```bash
docker compose up --build
# → http://localhost
```

## Deploy auf Hostinger VPS

**Option A – Hostinger übernimmt SSL:**
```bash
docker compose up -d
# Hostinger hPanel → SSL-Zertifikat aktivieren
```

**Option B – Caddy (automatisches HTTPS via Let's Encrypt):**
```bash
# docker-compose.yml: Caddy-Block einkommentieren, web-Port von "80:80" auf expose wechseln
docker compose up -d
```

## Vor Go-Live Pflicht

- [ ] Impressum (§5 TMG) befüllen
- [ ] Datenschutzerklärung (DSGVO) befüllen
- [ ] Telefonnummer in `index.html` eintragen (Kontakt-Sektion + TODO-Kommentar)
- [ ] LinkedIn-URL in `index.html` eintragen
- [ ] Google Fonts lokal einbinden (`assets/fonts/`) für DSGVO-Konformität
- [ ] HSTS-Header in `nginx.conf` einkommentieren (nach SSL)
- [ ] DNS: A-Record `cyber-aspis.de` → VPS-IP setzen
