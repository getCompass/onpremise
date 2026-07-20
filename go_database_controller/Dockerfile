ARG MIGRATE_VERSION=4.19.1-4
ARG GO_VERSION=1.26.4-2

###MIGRATE_BUILDER###
FROM docker.getcompass.ru/service/migrate:${MIGRATE_VERSION} AS migrate_builder

###BUILDER###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.dev as builder

WORKDIR /app

COPY . .

RUN CGO_ENABLED=0 go build -o database_controller -mod vendor main.go

###PRODUCTION###
FROM docker.getcompass.ru/service/go:${GO_VERSION}.prod as production

WORKDIR /app

RUN apk add mysql mysql-client docker-cli
COPY --from=builder /app/database_controller .
COPY --from=migrate_builder /usr/local/bin/migrate /usr/local/bin/migrate

COPY --from=builder /app/api/conf ./api/conf
COPY --from=builder /app/sql ./sql
COPY --from=builder /app/*.sh .
COPY --from=builder /app/etc ./etc

RUN mkdir logs \
&& chmod +x ./entrypoint.sh

CMD ["sh", "/app/entrypoint.sh"]