ARG GO_VERSION=1.26.4-2

###BUILDER###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.dev as builder

WORKDIR /app

COPY . .

RUN CGO_ENABLED=0 go build -o api_gateway -mod vendor main.go

###PRODUCTION###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.prod as production

RUN apk add curl

WORKDIR /app

COPY --from=builder /app/api_gateway .
COPY --from=builder /app/*.sh .

RUN mkdir logs \
&& chmod +x ./entrypoint.sh

CMD ["sh", "/app/entrypoint.sh"]