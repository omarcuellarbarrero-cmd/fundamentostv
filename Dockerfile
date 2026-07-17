FROM php:8.4-fpm-alpine

# Instalamos Nginx, curl del sistema y las herramientas de desarrollo para PHP
RUN apk add --no-cache nginx curl libcurl

# ¡IMPORTANTE! Instalamos y activamos la extensión cURL dentro de PHP
RUN docker-php-ext-install curl

# Copiamos los archivos de la app
COPY . /usr/share/nginx/html

# Aseguramos que Nginx y PHP puedan leer/escribir los archivos correctamente
RUN chown -R www-data:www-data /usr/share/nginx/html

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
