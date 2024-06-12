ARG DOCKER_REGISTRY=registry.segesman.us/
FROM ${DOCKER_REGISTRY}php:bookworm as php-bookworm
# Enable mysqli
RUN docker-php-ext-install mysqli
# Install cron and ssh client
RUN apt update && apt install -y cron openssh-client && rm -rf /var/lib/apt/lists/*
RUN mkdir -p ~/.ssh
RUN chmod 700 ~/.ssh
# copy password to /app
WORKDIR /app
COPY password.php .
# copy app files to /app/cron
WORKDIR /app/cron
COPY cron/logout.php .
COPY cron/reset_timelimit.php .
COPY cron/manage_internet.php .
COPY cron/is_enabled.txt .
COPY cron/crontab.txt .
RUN chmod +x logout.php reset_timelimit.php manage_internet.php
# copy connect to /app/cron/db
WORKDIR /app/cron/db
COPY html/api/connect.php .
# Copy entrypoint
COPY cron/docker-php-cron-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-php-cron-entrypoint
ENTRYPOINT ["docker-php-cron-entrypoint"]

CMD ["cron","-f"]
