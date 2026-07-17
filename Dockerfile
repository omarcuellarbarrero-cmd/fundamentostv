FROM php:8.4-fpm-alpine

# Instalamos dependencias del sistema (añadimos sqlite-dev)
RUN apk add --no-cache nginx curl libcurl curl-dev sqlite-dev

# Instalamos las extensiones cURL y PDO SQLite dentro de PHP
RUN docker-php-ext-install curl pdo pdo_sqlite

# Copiamos los archivos de la app
COPY . /usr/share/nginx/html

# ¡CRUCIAL! Damos permisos completos al usuario de Nginx/PHP para leer y escribir bases de datos
RUN chown -R www-data:www-data /usr/share/nginx/html && \
    chmod -R 775 /usr/share/nginx/html

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

