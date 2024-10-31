FROM php:apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable MySQLi extension
RUN docker-php-ext-enable mysqli