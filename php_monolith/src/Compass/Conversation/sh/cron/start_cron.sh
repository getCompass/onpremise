#!/bin/sh

rm /app/cache/*.lock > /dev/null 2>&1

crontab -u www-data - < /app/dev/configs/crontab.txt
/etc/init.d/cron start

tmp_crontab=$(crontab -l | grep -v '^#' | cut -f 6- -d ' ')

echo "$tmp_crontab" | while read -r CRON; do {
  sleep 0.25
  su -c "$CRON > /dev/null 2>&1 &" www-data
}; done
