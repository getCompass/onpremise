FROM docker.getcompass.ru/service/packages:0.1.7-devonpremise AS packages
FROM docker.getcompass.ru/service/php-8:8.1.25-devonpremise

RUN apk update && apk add --no-cache curl-dev

COPY --chown=billy:billy . /app

COPY /dev/conf/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R billy:billy .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]