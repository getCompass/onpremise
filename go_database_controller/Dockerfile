FROM docker.getcompass.ru/service/go:1.22.0

RUN apk add mysql mysql-client docker-cli

COPY . /app
RUN cd /app && sh install.sh && mkdir -p logs && go build -o database_controller -mod vendor main.go

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]
