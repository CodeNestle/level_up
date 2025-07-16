# Use official PHP-Apache image
FROM php:8.1-apache

# Install mysqli and enable it
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all project files into Apache server's root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/
