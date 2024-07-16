# Используем официальный образ Node.js в качестве базового образа
FROM --platform=linux/amd64 mirror.gcr.io/library/node:18

# Устанавливаем зависимости и собираем проект
COPY . .
RUN cd jitsi-meet-source && npm install && make all && make source-package

# Копируем исходники
RUN tar -xvf jitsi-meet-source/jitsi-meet.tar.bz2 -C jitsi-meet-dist/

# Сносим исходники
RUN rm -rf jitsi-meet-source/