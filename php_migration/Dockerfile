FROM docker.getcompass.ru/service/packages:0.1.15 AS packages
FROM docker.getcompass.ru/service/php-8:8.2.28-1

RUN apk update && apk add --no-cache build-base yaml openssl-dev curl-dev expat-dev zlib-dev

COPY --chown=www-data:www-data . /app

RUN ["chmod", "+x", "/app/entrypoint.sh"]

COPY --from=packages /tmp/git-2.25.1.tar.gz /tmp/git-2.25.1.tar.gz
RUN cd /tmp && tar -zxf git-2.25.1.tar.gz && cd git-2.25.1 && ./configure && make && make install

RUN cd /app && chown -R www-data:www-data .

WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]