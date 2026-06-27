FROM nginx:alpine

# Copiar archivos del proyecto a la carpeta de Nginx
COPY . /usr/share/nginx/html

# Exponer puerto 80
EXPOSE 80

# Nginx ya inicia automáticamente
CMD ["nginx", "-g", "daemon off;"]