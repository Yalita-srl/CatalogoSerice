FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u 1000 -d /home/sail sail
RUN mkdir -p /home/sail/.composer && \
    chown -R sail:sail /home/sail

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Set permissions
RUN chown -R sail:sail /var/www

USER sail

# Expose port 8000 for PHP built-in server
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]