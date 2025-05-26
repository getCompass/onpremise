FROM docker.getcompass.ru/service/go:1.23.7-1

COPY . /app
RUN cd /app && mkdir -p logs && go build -o company -mod vendor main.go && chmod +x ./entrypoint.sh

WORKDIR /app

# удаляем все файлы, кроме исполняемого и конфига
#RUN cd /app && find . -not \( -path "./api/conf/*" -o -path "./logs/*" -o -path "./company" -o -path "./*.sh" \) -type f -delete

# запускаем
CMD ["sh", "/app/entrypoint.sh"]