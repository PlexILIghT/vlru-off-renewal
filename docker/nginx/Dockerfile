FROM node:current-alpine as build-stage

COPY frontend/package*.json ./
# COPY ui-kit/package*.json ./ui-kit/

RUN npm install

# RUN cd ui-kit && npm install

COPY ./frontend .

RUN npm run build

FROM nginx:alpine as production-stage

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /var/www/html

WORKDIR /var/www/html
