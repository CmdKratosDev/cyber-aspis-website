FROM nginx:alpine

# Dateien kopieren
COPY index.html      /usr/share/nginx/html/index.html
COPY ueber-uns.html   /usr/share/nginx/html/ueber-uns.html
COPY leistungen.html  /usr/share/nginx/html/leistungen.html
COPY assets/          /usr/share/nginx/html/assets/
# services/ NICHT als ganzer Ordner kopieren: gebaeudetechnik.html (1d,
# SERVICES.md:80) ist arbeitsrechtlich zurueckgestellt und darf nicht
# ausgeliefert werden. Bewusst einzeln aufgefuehrt statt Ordner-Copy plus
# nachtraeglichem rm, damit die Datei erst gar nicht ins Image gelangt.
COPY services/compliance-audit.html         /usr/share/nginx/html/services/compliance-audit.html
COPY services/penetrationstest.html         /usr/share/nginx/html/services/penetrationstest.html
COPY services/vulnerability-assessment.html /usr/share/nginx/html/services/vulnerability-assessment.html
COPY analysen/        /usr/share/nginx/html/analysen/
COPY lernen/          /usr/share/nginx/html/lernen/
COPY nginx.conf       /etc/nginx/conf.d/default.conf

# Standard-Nginx-Config entfernen (wird durch unsere ersetzt)
RUN rm -f /etc/nginx/conf.d/default.conf.bak

# Korrekte Berechtigungen
RUN chown -R nginx:nginx /usr/share/nginx/html \
    && chmod -R 755 /usr/share/nginx/html

EXPOSE 80
