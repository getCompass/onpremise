FROM docker.getcompass.ru/service/go:1.24.6-1

# curl нужен для healthcheck, чтобы проверить https сервер (nc генерит ошибки сертификата в логах)
RUN apk add curl

COPY . /app
RUN cd /app && mkdir -p logs && go build -o api_gateway -mod vendor main.go && chmod +x ./entrypoint.sh

WORKDIR /app

# запускаем
CMD ["sh", "/app/entrypoint.sh"]
