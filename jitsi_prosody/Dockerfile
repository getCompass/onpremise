FROM docker.getcompass.ru/service/prosody:stable-9753

COPY prosody-custom-plugins/ /prosody-custom-plugins
COPY prosody-plugins/mod_av_moderation_component.lua /prosody-plugins/mod_av_moderation_component.lua
COPY prosody-plugins/mod_persistent_lobby.lua /prosody-plugins/mod_persistent_lobby.lua
COPY prosody-plugins/mod_polls.lua /prosody-plugins/mod_polls.lua

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]