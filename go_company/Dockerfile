FROM docker.getcompass.ru/service/go:devonpremise

RUN apk add mysql mysql-client

COPY . /app
RUN cd /app && sh install.sh && mkdir -p logs && go build -o company -mod vendor main.go

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]