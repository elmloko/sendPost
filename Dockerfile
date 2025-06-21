FROM laravelsail/php82-composer:latest

# Instala extensiones requeridas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Establece el directorio de trabajo
WORKDIR /var/www/html
