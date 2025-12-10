FROM php:7.4-apache
RUN echo "phar.readonly=0" > /usr/local/etc/php/conf.d/phar.ini
COPY index.php sploit.php /var/www/html/

RUN sed -i 's/Listen 80/Listen 5555/' /etc/apache2/ports.conf \
 && sed -i 's/*:80/*:5555/' /etc/apache2/sites-available/000-default.conf

EXPOSE 5555

CMD ["apache2-foreground"]
