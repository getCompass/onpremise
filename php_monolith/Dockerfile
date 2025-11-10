FROM docker.getcompass.ru/service/php-8:8.4.12-3

COPY --chown=www-data:www-data . /app

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]