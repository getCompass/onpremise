ARG GO_VERSION=1.26.4-2

###BUILDER###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.dev as builder

WORKDIR /app

COPY . .

RUN CGO_ENABLED=0 go build -o userbot_cache -mod vendor main.go

###PRODUCTION###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.prod as production

WORKDIR /app

COPY --from=builder /app/userbot_cache .
COPY --from=builder /app/api/conf ./api/conf
COPY --from=builder /app/*.sh .

RUN mkdir logs \
&& chmod +x ./entrypoint.sh

CMD ["sh", "/app/entrypoint.sh"]