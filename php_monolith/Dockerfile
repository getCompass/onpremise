FROM docker.getcompass.ru/service/packages:0.1.7-devonpremise AS packages
FROM docker.getcompass.ru/service/php-8:8.2.22
ARG BRANCH

RUN apk update && apk add --no-cache curl-dev
ARG COUNT=127
COPY --chown=www-data:www-data . /app

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]