FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_sqlite

RUN a2enmod rewrite headers expires deflate

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN echo "upload_max_filesize=10M\npost_max_size=12M\nmax_execution_time=60" \
    > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . .
RUN rm -f data/shop.db

RUN chown -R www-data:www-data /var/www/html

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
