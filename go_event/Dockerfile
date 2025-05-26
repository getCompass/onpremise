FROM docker.getcompass.ru/service/packages:0.1.15 AS packages
FROM docker.getcompass.ru/service/go:1.23.7-1
COPY --from=packages /tmp/migrate /usr/local/bin/migrate

COPY . /app
RUN cd /app && mkdir -p logs && go build -o event -mod vendor main.go && chmod +x ./entrypoint.sh

WORKDIR /app

# запускаем
CMD ["sh", "/app/entrypoint.sh"]