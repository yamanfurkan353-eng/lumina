FROM php:8.2-fpm-alpine

# Install PHP extensions
RUN apk add --no-cache \
    sqlite \
    sqlite-libs \
    && docker-php-ext-install pdo_sqlite

# Install additional tools
RUN apk add --no-cache \
    bash \
    curl \
    git

# Set working directory
WORKDIR /var/www/hotel-master

# Copy application files
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p storage/logs storage/exports storage/backups storage/uploads && \
    chmod -R 755 storage && \
    chmod -R 644 public && \
    chmod +x install.sh

# Create database directory
RUN mkdir -p database && chmod 755 database

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/auth/user || exit 1

CMD ["php-fpm"]
