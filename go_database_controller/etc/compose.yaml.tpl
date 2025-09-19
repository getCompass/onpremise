version: '3.8'

configs:
  mysql-conf:
     name: "mysql-conf{{ if ne $.ServiceLabel ""}}-{{$.ServiceLabel}}{{end}}-{{.DominoId}}"
     file: {{.ConfPath}}
services:

{{ range $mysql_conf_block := .MysqlConfBlockList}}
  mysql-{{$mysql_conf_block.Port}}:
    image: "{{$.RegistryServicePath}}/mysql:8.0.28"
    deploy:
      restart_policy:
        condition: "any"
        window: "10s"
      {{- if gt $.MysqlServerId 0 }}
      placement:
        constraints:
          - "node.labels.role=={{$.ServiceLabel}}"
      {{ end }}
    environment:
      MYSQL_TCP_PORT: {{$mysql_conf_block.Port}}
    {{- if gt $.MysqlServerId 0 }}
    command: >
      mysqld
        --log_bin=/var/lib/mysql/log-bin.log
        --binlog-format=ROW
        --expire-logs-days=5
        --max-binlog-size=300M
        --sync-binlog=1
        --server-id={{$.MysqlServerId}}
        --replicate-ignore-table=mysql.user
        --replicate-ignore-table=mysql.db
        --replicate-ignore-table=mysql.tables_priv
        --gtid-mode=ON
        --enforce-gtid-consistency=ON
        --log-slave-updates=ON
        --slave_skip_errors=1007,1032,1050,1054,1060,1061,1062,1091,1396
        --max_binlog_cache_size=10485760
        --ssl=ON
        --ssl-ca="/etc/mysql/ssl/mysqlRootCA.crt"
        --ssl-cert="/etc/mysql/ssl/mysql-{{$.MysqlSslPrefix}}-cert.pem"
        --ssl-key="/etc/mysql/ssl/mysql-{{$.MysqlSslPrefix}}-key.pem"
        --tls-version="TLSv1.2,TLSv1.3"
    {{ end }}
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
      {{- if gt $.MysqlServerId 0 }}
      - "{{$.MysqlSslPath}}:/etc/mysql/ssl"
      {{ end }}
    networks:
      domino-shared:
        aliases:
         - "{{$mysql_conf_block.Host}}"
      {{- if gt $.MysqlServerId 0 }}
      monolith-mysql-shared:
        aliases:
         - "{{$mysql_conf_block.Host}}"
      {{- end}}

{{end}}
networks:
  domino-shared:
    external:
      name: "{{.DominoNetwork}}"
  {{- if gt $.MysqlServerId 0 }}
  monolith-mysql-shared:
    external:
      name: "{{.MonolithMysqlNetwork}}"
  {{- end}}