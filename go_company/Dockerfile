FROM docker.getcompass.ru/service/go:1.23.6

RUN apk add mysql mysql-client

COPY . /app
RUN cd /app && mkdir -p logs && ln -sf /dev/stdout /app/logs/main.log && go build -o company -mod vendor main.go && chmod +x ./entrypoint.sh
RUN apk del .build-deps

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]