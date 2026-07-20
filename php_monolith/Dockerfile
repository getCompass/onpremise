ARG MIGRATE_VERSION=4.19.1-4
ARG PHP_VERSION=8.4.21-3

###MIGRATE_BUILDER###
FROM docker.getcompass.ru/service/migrate:${MIGRATE_VERSION} AS migrate_builder

# runtime
FROM docker.getcompass.ru/service/php-8:${PHP_VERSION} as runtime

WORKDIR /app

COPY --chown=www-data:www-data . .
COPY --from=migrate_builder /usr/local/bin/migrate /usr/local/bin/migrate

RUN chown www-data:www-data /app \
&& chmod +x ./entrypoint.sh \
&& su -c "sh /app/install.sh" www-data

ENTRYPOINT ["bash", "/app/entrypoint.sh"]
