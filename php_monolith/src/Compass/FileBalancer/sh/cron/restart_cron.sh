#!/bin/sh

# просто тупо убиваем кроны
echo "stopping cron..."
ps -ef | grep cron | awk '{print $2}' | xargs kill -9

echo "starting cron..."
sh start_cron.sh

echo "done..."