# KigaliNet ISP CMS — Dockerfile | Student ID: 26524
FROM php:8.1-apache

# Enable mysqli extension
RUN docker-php-ext-install mysqli

# Copy application files
COPY . /var/www/html/

# Set document root to public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
