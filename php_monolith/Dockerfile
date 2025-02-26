FROM docker.getcompass.ru/service/php-8:8.2.25-3
ARG BRANCH

RUN apk update && apk add --no-cache curl-dev

COPY --chown=www-data:www-data . /app

RUN ["chmod", "+x", "/app/entrypoint.sh"]

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]