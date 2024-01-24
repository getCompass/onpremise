FROM docker.getcompass.ru/service/go:init-devonpremise

RUN apk add mysql mysql-client

COPY . /app
RUN cd /app && sh install.sh && mkdir -p logs && go build -o event -mod vendor main.go

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]