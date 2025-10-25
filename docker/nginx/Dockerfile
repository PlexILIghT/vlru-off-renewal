FROM nginx:alpine as production-stage

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /var/www/html

WORKDIR /var/www/html
