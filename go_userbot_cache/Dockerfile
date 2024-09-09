FROM docker.getcompass.ru/service/go:4.2.1

COPY . /app
RUN cd /app && sh install.sh && mkdir -p logs && go build -o userbot_cache -mod vendor main.go

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]
