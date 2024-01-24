FROM docker.getcompass.ru/service/packages:0.1.7-devonpremise AS packages

FROM docker.getcompass.ru/service/php-8:8.1.25-devonpremise

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

COPY --chown=www-data:www-data . /app

COPY /dev/conf/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]