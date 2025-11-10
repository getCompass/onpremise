FROM docker.getcompass.ru/service/packages:0.1.15 AS packages

FROM docker.getcompass.ru/service/php-8:8.2.27

COPY --from=packages /tmp/nginx-1.27.2.tar.gz /tmp/nginx-1.27.2.tar.gz
COPY --from=packages /tmp/nginx-upload-module.tar.gz /tmp/nginx-upload-module.tar.gz
RUN tar -zxf /tmp/nginx-upload-module.tar.gz -C /tmp/
COPY --from=packages /tmp/openssl-3.4.0.tar.gz /tmp/openssl-3.4.0.tar.gz
COPY --from=packages /tmp/pcre2-10.44.tar.gz /tmp/pcre2-10.44.tar.gz
COPY --from=packages /tmp/zlib-1.3.1.tar.gz /tmp/zlib-1.3.1.tar.gz
COPY --from=packages /tmp/migrate /usr/local/bin/migrate
COPY --from=packages /tmp/git-2.25.1.tar.gz /tmp/git-2.25.1.tar.gz

RUN apk update && apk add --no-cache wget git libxslt-dev gnupg build-base linux-headers libxslt perl \
    && cd /tmp && tar -zxf pcre2-10.44.tar.gz && cd pcre2-10.44 && ./configure && make && make install \
    && cd /tmp && tar -zxf zlib-1.3.1.tar.gz && cd zlib-1.3.1 && ./configure && make && make install \
    && cd /tmp && tar -zxf openssl-3.4.0.tar.gz \
    && cd /tmp/openssl-3.4.0 && if [ -f Makefile ]; then make clean; fi \
    && ./config --prefix=/usr/local/ssl --openssldir=/usr/local/ssl no-shared no-threads \
    && make && make install_sw LIBDIR=lib \
    && cd /tmp && tar zxf nginx-1.27.2.tar.gz \
    && cd /tmp/nginx-1.27.2 && \
        ./configure \
        --sbin-path=/usr/sbin/nginx --conf-path=/etc/nginx/nginx.conf \
        --error-log-path=/var/log/nginx/error.log \
        --http-log-path=/var/log/nginx/access.log \
        --pid-path=/var/run/nginx.pid \
        --lock-path=/var/run/nginx.lock \
        --with-openssl=/tmp/openssl-3.4.0 \
        --http-client-body-temp-path=/var/cache/nginx/client_temp \
        --http-proxy-temp-path=/var/cache/nginx/proxy_temp \
        --http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
        --http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
        --http-scgi-temp-path=/var/cache/nginx/scgi_temp \
        --with-pcre-jit --with-ipv6 --with-http_ssl_module \
        --with-http_stub_status_module \
        --with-http_realip_module --with-http_auth_request_module --with-http_addition_module \
        --with-http_dav_module --with-http_gunzip_module \
        --with-http_gzip_static_module --with-http_v2_module --with-http_sub_module \
        --with-http_xslt_module --with-stream --with-stream_ssl_module --with-mail \
        --with-mail_ssl_module --with-threads --with-http_mp4_module --with-pcre=../pcre2-10.44 \
        --with-zlib=../zlib-1.3.1 --with-mail=dynamic --add-module=/tmp/nginx-upload-module && make && make install \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && mkdir -p /var/cache/nginx/ && mkdir -p /etc/nginx/sites-enabled/

# ставим pnpm
RUN apk add --no-cache nodejs npm \
    && npm install -g pnpm@8.15.7 \
    && mkdir -p /root/.ssh \
    && ssh-keyscan github.com >> /root/.ssh/known_hosts

COPY --chown=www-data:www-data . /app

RUN cd /app \
    && chmod +x /app/entrypoint.sh

RUN cd /app/web \
    && pnpm install --force

WORKDIR /app
ENTRYPOINT ["sh", "/app/entrypoint.sh"]
