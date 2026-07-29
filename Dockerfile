FROM nginx:alpine

# Dateien kopieren
COPY index.html    /usr/share/nginx/html/index.html
COPY ueber-uns.html /usr/share/nginx/html/ueber-uns.html
COPY assets/       /usr/share/nginx/html/assets/
COPY services/     /usr/share/nginx/html/services/
COPY analysen/     /usr/share/nginx/html/analysen/
COPY lernen/       /usr/share/nginx/html/lernen/
COPY nginx.conf    /etc/nginx/conf.d/default.conf

# Standard-Nginx-Config entfernen (wird durch unsere ersetzt)
RUN rm -f /etc/nginx/conf.d/default.conf.bak

# Korrekte Berechtigungen
RUN chown -R nginx:nginx /usr/share/nginx/html \
    && chmod -R 755 /usr/share/nginx/html

EXPOSE 80
