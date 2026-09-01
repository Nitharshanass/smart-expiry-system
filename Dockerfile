FROM php:8.2-apache

# Install MySQL PHP extension
RUN docker-php-ext-install mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files to Apache web directory
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Give Apache ownership
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80