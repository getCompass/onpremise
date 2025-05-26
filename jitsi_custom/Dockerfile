FROM alpine:3.21

RUN apk add --no-cache --virtual .build-deps npm build-base git

# устанавливаем зависимости и собираем проект
COPY . .
RUN \
	--mount=type=cache,target=/tmp/v8-compile-cache-0,sharing=locked \
	--mount=type=cache,target=/root/.npm,sharing=locked \
    cd jitsi-meet-source \
    && npm install \
    && make clean \
    && make source-package

# копируем исходники и удаляем зависимости билда
RUN tar -xvf jitsi-meet-source/jitsi-meet.tar.bz2 -C jitsi-meet-dist/ \
    && rm -rf /jitsi-meet-source \
    && apk del .build-deps \
    && rm -rf /lib-jitsi-meet-3.4.0.tgz \
    && rm -rf /.git
