[Unit]
Description=MySQL Multi Server for {{.ServerName}} instance %i
After=syslog.target
After=network.target

[Service]
User=mysql
Group=mysql
Type=forking
ExecStart=/usr/bin/mysqld_multi --defaults-file={{.MysqlConfPath}}{{.ServerName}}.cnf start %i
ExecStop=/usr/bin/mysqld_multi --defaults-file={{.MysqlConfPath}}{{.ServerName}}.cnf stop %i
PrivateTmp=true

[Install]
WantedBy=multi-user.target