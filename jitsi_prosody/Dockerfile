FROM docker.getcompass.ru/service/prosody:stable-9753

RUN set -eux; \
    # игнорируем неуспешный update, нам нужны только старые списки пакетов
    apt-get update || true; \
    apt-get install -y --no-install-recommends wget gnupg; \
    wget -O- https://prosody.im/files/prosody-debian-packages.key | apt-key add -; \
    apt-get update; \
    apt-get install -y --no-install-recommends netcat-openbsd; \
    rm -rf /var/lib/apt/lists/*

COPY defaults/prosody.cfg.lua /defaults/prosody.cfg.lua
COPY defaults/conf.d/visitors.cfg.lua /defaults/conf.d/visitors.cfg.lua
COPY prosody-custom-plugins/ /prosody-custom-plugins
COPY prosody-plugins-visitors/ /prosody-plugins-visitors
COPY prosody-plugins/mod_av_moderation_component.lua /prosody-plugins/mod_av_moderation_component.lua
COPY prosody-plugins/mod_muc_domain_mapper.lua /prosody-plugins/mod_muc_domain_mapper.lua
COPY prosody-plugins/mod_persistent_lobby.lua /prosody-plugins/mod_persistent_lobby.lua
COPY prosody-plugins/mod_polls.lua /prosody-plugins/mod_polls.lua
COPY prosody-plugins/mod_visitors_component.lua /prosody-plugins/mod_visitors_component.lua
COPY prosody-plugins/mod_visitors.lua /prosody-plugins/mod_visitors.lua

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]