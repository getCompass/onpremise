FROM docker.getcompass.ru/service/packages:0.1.7-devonpremise AS packages

FROM docker.getcompass.ru/service/php-8:8.0.30-1-devonpremise

COPY --from=packages /tmp/nginx-1.19.2.tar.gz /tmp/nginx-1.19.2.tar.gz
COPY --from=packages /tmp/nginx-upload-module.tar.gz /tmp/nginx-upload-module.tar.gz
RUN tar -zxf /tmp/nginx-upload-module.tar.gz -C /tmp/
COPY --from=packages /tmp/openssl-1.1.1g.tar.gz /tmp/openssl-1.1.1g.tar.gz
COPY --from=packages /tmp/pcre-8.45.tar.gz /tmp/pcre-8.45.tar.gz
COPY --from=packages /tmp/zlib-1.2.11.tar.gz /tmp/zlib-1.2.11.tar.gz
COPY --from=packages /tmp/migrate /usr/local/bin/migrate
COPY --from=packages /tmp/git-2.25.1.tar.gz /tmp/git-2.25.1.tar.gz

RUN apt-get update --allow-releaseinfo-change && apt-get install -y wget git libxslt1.1 libxslt1-dev \
	&& cd /tmp && tar -zxf pcre-8.45.tar.gz && cd pcre-8.45 && ./configure && make && make install \
        && cd /tmp && tar -zxf zlib-1.2.11.tar.gz && cd zlib-1.2.11 && ./configure && make && make install \
        && cd /tmp && tar -zxf openssl-1.1.1g.tar.gz \
        && cd /tmp && tar zxf nginx-1.19.2.tar.gz \
        && cd /tmp/nginx-1.19.2 && \
            ./configure \
            --sbin-path=/usr/sbin/nginx --conf-path=/etc/nginx/nginx.conf \
            --error-log-path=/var/log/nginx/error.log \
            --http-log-path=/var/log/nginx/access.log \
            --pid-path=/var/run/nginx.pid \
            --lock-path=/var/run/nginx.lock \
            --with-openssl=/tmp/openssl-1.1.1g \
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
            --with-mail_ssl_module --with-threads --with-http_mp4_module --with-pcre=../pcre-8.45 \
            --with-zlib=../zlib-1.2.11 --with-mail=dynamic --add-module=/tmp/nginx-upload-module && make && make install \
        && ln -sf /dev/stdout /var/log/nginx/access.log \
        && ln -sf /dev/stderr /var/log/nginx/error.log \
	&& mkdir -p /var/cache/nginx/ && mkdir -p /etc/nginx/sites-enabled/

COPY --chown=billy:billy . /app

RUN cd /app \
	&& cp dev/configs/etc/nginx/nginx.conf /etc/nginx/nginx.conf \
	&& cp dev/configs/etc/nginx/onpremise_join_web.nginx /etc/nginx/sites-enabled/onpremise_join_web.nginx \
	&& chmod +x /app/entrypoint.sh

WORKDIR /app

RUN cd /app && runuser -l billy -c "sh install.sh"

ENTRYPOINT ["nginx", "-g", "daemon off;"]