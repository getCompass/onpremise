#!/bin/sh

sh /wait-for-it.sh $PROSODY_MAIN_HOST:$PROSODY_MAIN_PORT -t 60
sh /wait-for-it.sh $PROSODY_V0_HOST:$PROSODY_V0_PORT -t 60
sh /wait-for-it.sh $PROSODY_V1_HOST:$PROSODY_V1_PORT -t 60
sh /wait-for-it.sh $PROSODY_V2_HOST:$PROSODY_V2_PORT -t 60
sh /wait-for-it.sh $JICOFO_HOST:$JICOFO_PORT -t 60

echo "Дождались всех сервисов"
