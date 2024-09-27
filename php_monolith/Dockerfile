FROM docker.getcompass.ru/service/packages:0.1.7-devonpremise AS packages
FROM docker.getcompass.ru/service/php-8:8.1.25-15-devonpremise
ARG BRANCH

RUN apk update && apk add --no-cache curl-dev
ARG COUNT=107
COPY --chown=www-data:www-data . /app

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]