FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    curl \
    git \
    gnupg2 \
    apt-transport-https \
    libzip-dev \
    zip unzip libicu-dev procps cron \
    openssh-client \
    sshpass

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip intl exif pcntl

RUN pecl install redis && docker-php-ext-enable redis

# Microsoft SQL Server (ODBC) + PHP sqlsrv/pdo_sqlsrv
# Нужно для подключения DB::connection('vista') (driver sqlsrv)
RUN mkdir -p /etc/apt/keyrings \
    && curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /etc/apt/keyrings/microsoft.gpg \
    && echo "deb [arch=amd64,arm64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv


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
