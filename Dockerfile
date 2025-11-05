# Use official PHP image with Apache
FROM php:8.1-apache

# Install required PHP extensions (pdo and pdo_sqlite are already included in PHP 8.1)
# RUN docker-php-ext-install pdo pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Create database directory and set permissions
RUN mkdir -p /var/www/html/data
RUN chown -R www-data:www-data /var/www/html/data
RUN chmod -R 755 /var/www/html/data

# Configure Apache
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
