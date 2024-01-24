FROM docker.getcompass.ru/service/go:init-devonpremise

COPY . /app
RUN cd /app && sh install.sh && mkdir -p logs && go build -o sender_balancer -mod vendor main.go

WORKDIR /app

CMD ["sh", "/app/entrypoint.sh"]