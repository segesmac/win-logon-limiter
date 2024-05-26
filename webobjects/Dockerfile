ARG DOCKER_REGISTRY=registry.segesman.us
FROM ${DOCKER_REGISTRY}php:8.3.6-apache-bookworm as php-apache
# Enable mysqli
RUN docker-php-ext-install mysqli
#RUN sed -i "s/;extension=mysqli/extension=mysqli/g" /usr/local/etc/php/php.ini-production
#RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
#RUN service apache2 restart
# copy db files to /tools/dbupdater
WORKDIR /tools/dbupdater
COPY ./sqlobjects/. .
RUN chmod +x update_db.php
# copy app files to /var/www/
WORKDIR /var/www
COPY password.php .
WORKDIR /var/www/html
COPY html .
# Copy entrypoint
COPY docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint
ENTRYPOINT ["docker-php-entrypoint"]

# https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

WORKDIR /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
