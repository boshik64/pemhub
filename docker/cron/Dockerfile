FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    curl \
    git \
    libzip-dev \
    zip unzip libicu-dev procps cron \
    openssh-client \
    sshpass

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip intl exif pcntl

RUN pecl install redis && docker-php-ext-enable redis


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./conf/cron /etc/cron.d/cron
RUN chmod 0644 /etc/cron.d/cron
RUN crontab /etc/cron.d/cron
RUN touch /var/log/cron.log

# Set working directory
WORKDIR /app

CMD ["sh", "-c", "cron && tail -f /var/log/cron.log"]
