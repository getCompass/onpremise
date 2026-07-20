version: '3.8'

configs:
  mysql-conf:
     name: "mysql-conf{{ if ne $.ServiceLabel ""}}-{{$.ServiceLabel}}{{end}}-{{.DominoId}}-{{.MysqlConfHash}}"
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
    command: >
      mysqld
        --innodb-buffer-pool-size={{$mysql_conf_block.InnodbBufferPoolSizeMb}}M
        --innodb-thread-concurrency={{$mysql_conf_block.InnodbThreadConcurrency}}
        --table-open-cache={{$mysql_conf_block.TableOpenCache}}
    {{ if gt $.MysqlServerId 0 }}
        --log_bin=/var/lib/mysql/log-bin.log
        --binlog-format=ROW
        --log-replica-updates=1
        --expire-logs-days=20
        --max-binlog-size=500M
        --sync-binlog=1
        --server-id={{$.MysqlServerId}}
        --replicate-ignore-table=mysql.user
        --replicate-ignore-table=mysql.db
        --replicate-ignore-table=mysql.tables_priv
        --gtid-mode=ON
        --enforce-gtid-consistency=ON
        --log-slave-updates=ON
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
