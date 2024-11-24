FROM docker.getcompass.ru/service/packages:0.1.12 AS packages
FROM docker.getcompass.ru/service/go:1.23.2-1

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

RUN apk add mysql mysql-client

COPY . /app
RUN cd /app && mkdir -p logs && ln -sf /dev/stdout /app/logs/main.log && go build -o event -mod vendor main.go && chmod +x ./entrypoint.sh
RUN apk del .build-deps

WORKDIR /app

# запускаем
CMD ["sh", "/app/entrypoint.sh"]