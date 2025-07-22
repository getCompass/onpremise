FROM docker.getcompass.ru/service/jicofo:stable-9753

RUN apt-get update && apt-get install -y --no-install-recommends netcat-openbsd

COPY defaults/jicofo.conf /defaults/jicofo.conf

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
COPY wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh
COPY wait-services.sh /wait-services.sh
RUN chmod +x /wait-services.sh

ENTRYPOINT ["/entrypoint.sh"]