version: '3.8'

configs:
  mysql-conf:
     name: "mysql-conf-{{.DominoId}}"
     file: {{.ConfPath}}
services:

{{ range $mysql_conf_block := .MysqlConfBlockList}}
  mysql-{{$mysql_conf_block.Port}}:
    image: "{{$.RegistryServicePath}}/mysql:8.0.28"
    deploy:
      restart_policy:
        condition: "any"
        window: "10s"
    environment:
      MYSQL_TCP_PORT: {{$mysql_conf_block.Port}}
    logging:
      driver: "json-file"
      options:
        max-size: "15m"
        max-file: "3"
    configs:
      - source: "mysql-conf"
        target: "/etc/mysql/conf.d/my.cnf"
    volumes:
      - {{$mysql_conf_block.DbPath}}:/var/lib/mysql
    networks:
      domino-shared:
        aliases:
         - "{{$mysql_conf_block.Host}}"

{{end}}
networks:
  domino-shared:
    external:
      name: "{{.DominoNetwork}}"