FROM alpine:3.20

RUN apk add --no-cache --virtual .build-deps npm build-base git

# Устанавливаем зависимости и собираем проект
COPY . .
RUN cd jitsi-meet-source && npm install && make all && make source-package

# Копируем исходники
RUN tar -xvf jitsi-meet-source/jitsi-meet.tar.bz2 -C jitsi-meet-dist/

# Сносим исходники
RUN rm -rf jitsi-meet-source/

# Удаляем зависимости билда
RUN apk del .build-deps
