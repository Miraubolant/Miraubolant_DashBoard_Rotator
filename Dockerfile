FROM php:8.2-apache

# Activer mod_rewrite
RUN a2enmod rewrite

# Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Config Apache pour AllowOverride
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Définir le DocumentRoot sur /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copier les fichiers
COPY . /var/www/html/

# Créer le dossier de données
RUN mkdir -p /var/www/html/data

# Permissions
RUN chown -R www-data:www-data /var/www/html/data
RUN chmod -R 755 /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
