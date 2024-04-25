FROM php:8.3.6-apache-bookworm as php-apache
# copy db files to /tools/dbupdater
WORKDIR /tools/dbupdater
COPY ./sqlobjects/. .
RUN chmod +x update_db.php
# copy app files to /var/www/
WORKDIR /var/www
COPY password.php .
RUN ls -al
WORKDIR /var/www/html
COPY html .
RUN ls -al
ENTRYPOINT ["docker-php-entrypoint"]
