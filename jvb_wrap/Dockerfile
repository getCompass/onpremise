FROM docker.getcompass.ru/service/jvb:stable-9753

RUN apt-get update && apt-get install -y --no-install-recommends netcat-openbsd

COPY defaults/jvb.conf /defaults/jvb.conf