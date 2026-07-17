FROM php:8.4-fpm-alpine

# Instalamos dependencias del sistema y los paquetes -dev necesarios para compilar
RUN apk add --no-cache nginx curl libcurl curl-dev

# Ahora PHP sí encontrará las librerías necesarias para compilar la extensión
RUN docker-php-ext-install curl

# Copiamos los archivos de la app al directorio de Nginx
COPY . /usr/share/nginx/html

# Aseguramos los permisos correctos para Nginx y PHP
RUN chown -R www-data:www-data /usr/share/nginx/html

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
