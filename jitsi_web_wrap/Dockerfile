FROM docker.getcompass.ru/service/web:web-1.0.8187-1

RUN apt-get update && apt-get install -y --no-install-recommends netcat-openbsd

COPY defaults/default /defaults/default
COPY defaults/ffdhe2048.txt /defaults/ffdhe2048.txt
COPY defaults/meet.conf /defaults/meet.conf
COPY defaults/nginx.conf /defaults/nginx.conf
COPY defaults/settings-config.js /defaults/settings-config.js
COPY defaults/ssl.conf /defaults/ssl.conf
COPY defaults/system-config.js /defaults/system-config.js

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
COPY wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh
COPY wait-services.sh /wait-services.sh
RUN chmod +x /wait-services.sh

ENTRYPOINT ["/entrypoint.sh"]