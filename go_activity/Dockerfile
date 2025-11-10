FROM docker.getcompass.ru/service/go:1.24.6-1

COPY . /app
RUN cd /app && mkdir -p logs && go build -o activity -mod vendor main.go && chmod +x ./entrypoint.sh

WORKDIR /app

# запускаем
CMD ["sh", "/app/entrypoint.sh"]