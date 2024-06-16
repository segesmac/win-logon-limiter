ARG DOCKER_REGISTRY=registry.segesman.us/
FROM ${DOCKER_REGISTRY}php:8.3.6-apache-bookworm as php-apache
# Enable mysqli
RUN docker-php-ext-install mysqli
#RUN sed -i "s/;extension=mysqli/extension=mysqli/g" /usr/local/etc/php/php.ini-production
#RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
#RUN service apache2 restart
# Install unzip, because composer depends on this
RUN apt update && apt install -y unzip && rm -rf /var/lib/apt/lists/*
# Install composer
WORKDIR /usr/local/bin
COPY get_composer.sh .
RUN chmod +x get_composer.sh
RUN ./get_composer.sh
RUN rm -f get_composer.sh
# copy db files to /tools/dbupdater
WORKDIR /tools/dbupdater
COPY ./sqlobjects/. .
RUN chmod +x update_db.php
WORKDIR /var/www/html
COPY html .
# Copy entrypoint
COPY docker-php-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-entrypoint
# copy app files to /var/www/
WORKDIR /var/www
COPY password.php .
COPY composer.json .
# Install dependencies
RUN composer install
ENTRYPOINT ["docker-php-entrypoint"]

# https://httpd.apache.org/docs/2.4/stopping.html#gracefulstop
STOPSIGNAL SIGWINCH

WORKDIR /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
