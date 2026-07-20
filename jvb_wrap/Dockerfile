FROM docker.getcompass.ru/service/jvb:jvb-2.3-260-gea11cd36c-1

RUN rm /etc/apt/sources.list.d/jitsi.list \
    && apt-get update && apt-get install -y --no-install-recommends netcat-openbsd

COPY defaults/jvb.conf /defaults/jvb.conf