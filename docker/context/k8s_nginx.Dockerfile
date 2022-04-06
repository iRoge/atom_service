FROM nginx:latest
COPY public /var/www/p5s/
RUN rm /etc/nginx/conf.d/default.conf
WORKDIR /var/www/p5s/
