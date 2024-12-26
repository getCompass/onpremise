FROM docker.getcompass.ru/service/prosody:stable-9364-1

COPY prosody-custom-plugins/ /prosody-custom-plugins
COPY prosody-plugins/mod_persistent_lobby.lua /prosody-plugins/mod_persistent_lobby.lua

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]