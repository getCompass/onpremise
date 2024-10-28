FROM docker.getcompass.ru/service/php-8:8.2.22 AS php

FROM docker.getcompass.ru/service/packages:devonpremise AS packages
FROM docker.getcompass.ru/service/ffmpeg:devonpremise
ENV LC_ALL=C.UTF-8

RUN apt-get update && apt-get install -y software-properties-common && add-apt-repository -y ppa:ondrej/php

RUN apt-get update && \
	apt-get install -y php8.2-fpm php8.2-dev && \
	apt-get install -y php8.2-bcmath php8.2-intl php8.2-gd php8.2-imap php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-xml php8.2-mcrypt memcached && \
    	apt-get install -y php-pear php8.2-memcache

RUN mkdir -p /tmp/prebuild/php

COPY --from=packages /tmp/openssl-1.1.1g.tar.gz /tmp/openssl-1.1.1g.tar.gz
COPY --from=packages /tmp/pcre-8.45.tar.gz /tmp/pcre-8.45.tar.gz
COPY --from=packages /tmp/zlib-1.2.11.tar.gz /tmp/zlib-1.2.11.tar.gz
COPY --from=packages /tmp/libspng-0.7.0.tar.gz /tmp/libspng-0.7.0.tar.gz
COPY --from=packages /tmp/highway-0.15.0.tar.gz /tmp/highway-0.15.0.tar.gz
COPY --from=packages /tmp/openjpeg-2.4.0.tar.gz /tmp/openjpeg-2.4.0.tar.gz
COPY --from=packages /tmp/cgif-0.0.2.tar.gz /tmp/cgif-0.0.2.tar.gz
COPY --from=packages /tmp/vips-8.12.0.tar.gz /tmp/vips-8.12.0.tar.gz

COPY --from=packages /tmp/prebuild/libhwy0_0.17.0-11_amd64.deb /tmp/prebuild/libhwy0_0.17.0-11_amd64.deb
COPY --from=packages /tmp/prebuild/libhwy-dev_0.17.0-11_amd64.deb /tmp/prebuild/libhwy-dev_0.17.0-11_amd64.deb

COPY --from=packages /tmp/prebuild/libjxl_0.8_amd64.deb /tmp/prebuild/libjxl_0.8_amd64.deb
COPY --from=packages /tmp/prebuild/libjxl-dev_0.8_amd64.deb /tmp/prebuild/libjxl-dev_0.8_amd64.deb
COPY --from=packages /tmp/prebuild/libjxl-gdk-pixbuf_0.8_amd64.deb /tmp/prebuild/libjxl-gdk-pixbuf_0.8_amd64.deb

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

COPY --from=packages /tmp/prebuild/php/grpc_1.42.0-1_amd64.deb /tmp/prebuild/php/grpc_1.42.0-1_amd64.deb
COPY --from=packages /tmp/prebuild/php/imagick_3.7.0-1_amd64.deb /tmp/prebuild/php/imagick_3.7.0-1_amd64.deb
COPY --from=packages /tmp/prebuild/php/protobuf_3.19.1-1_amd64.deb /tmp/prebuild/php/protobuf_3.19.1-1_amd64.deb
COPY --from=packages /tmp/prebuild/php/vips_1.0.13-1_amd64.deb /tmp/prebuild/php/vips_1.0.13-1_amd64.deb
COPY --from=packages /tmp/prebuild/php/zip_1.22.2-1_amd64.deb /tmp/prebuild/php/zip_1.22.2-1_amd64.deb

RUN apt-get update && apt-get install -y wget git perl libperl-dev libgd3 libgd-dev libgeoip1 libgeoip-dev geoip-bin libxml2 libxml2-dev libxslt1.1 libxslt1-dev \
	libjpeg-dev libheif-dev libpng-dev librsvg2-dev libwebp-dev libmagickwand-dev libfftw3-dev liborc-0.4-dev libopenslide-dev libgsf-1-dev build-essential cmake meson

RUN apt-get update && apt-get install -y libmemcached-dev gettext default-mysql-client zlib1g-dev netcat cron
RUN apt-get update && apt-get install -y git
RUN apt-get update && apt-get install -y php8.2-vips
RUN apt-get update && apt-get install -y libzip-dev php8.2-zip

RUN apt install /tmp/prebuild/libhwy0_0.17.0-11_amd64.deb && apt install /tmp/prebuild/libhwy-dev_0.17.0-11_amd64.deb && apt install /tmp/prebuild/php/zip_1.22.2-1_amd64.deb
RUN apt install /tmp/prebuild/libjxl_0.8_amd64.deb && apt install /tmp/prebuild/libjxl-dev_0.8_amd64.deb && apt install /tmp/prebuild/libjxl-gdk-pixbuf_0.8_amd64.deb
RUN cd /tmp && tar -zxf pcre-8.45.tar.gz && cd pcre-8.45 && ./configure && make && make install \
        && cd /tmp && tar -zxf zlib-1.2.11.tar.gz && cd zlib-1.2.11 && ./configure && make && make install \
        && cd /tmp && tar -zxf openssl-1.1.1g.tar.gz \
        && cd /tmp && tar -zxf libspng-0.7.0.tar.gz && cd libspng-0.7.0 && mkdir cbuild && cd cbuild && cmake -DENABLE_OPT:SPNG_SSE=4 .. && make && make install && make clean \
        && cd /tmp && tar -zxf openjpeg-2.4.0.tar.gz && cd openjpeg-2.4.0 && mkdir cbuild && cd cbuild && cmake .. -DCMAKE_BUILD_TYPE=Release && make && make install && make clean \
        && cd /tmp && tar -zxf cgif-0.0.2.tar.gz && cd cgif-0.0.2 && meson build --buildtype=release && cd build && ninja && ninja install \
        && cd /tmp && tar -zxf vips-8.12.0.tar.gz && cd vips-8.12.0 && ./configure && make && make install && make clean \
        && ldconfig

COPY . /app

RUN groupadd -g 1101 billy
RUN useradd -M -s /bin/bash -g billy -u 1101 billy

RUN dpkg -iR /tmp/prebuild/php/

RUN cd /app \
	&& mkdir -p /etc/ImageMagick-6 && mkdir -p /usr/lib/php/20220829/ \
	&& cp dev/configs/etc/ImageMagick-6/policy.xml /etc/ImageMagick-6/policy.xml \
	&& cp dev/configs/etc/ffmpeg/libffmpeg-extension.so /usr/local/lib/libffmpeg-extension.so \
	&& ldconfig && cd /app && sh install.sh && chown -R www-data:www-data . && chmod +x . \
	&& cp dev/extensions/* /usr/lib/php/20220829/

RUN rm /etc/php/8.2/cli/php.ini && mv /app/dev/configs/etc/php/www.conf "/etc/php/8.2/fpm/pool.d/www.conf" \
	&& mv /app/dev/configs/etc/php/php.ini /etc/php/8.2/fpm/php.ini && ln -s /etc/php/8.2/fpm/php.ini /etc/php/8.2/cli/ && mkdir -p /run/php/

RUN	echo "" >> /etc/php/8.2/fpm/php.ini \
	&& echo "extension=cpp_extension_file.so" >> /etc/php/8.2/cli/php.ini \
	&& echo "" >> /etc/php/8.2/fpm/php.ini

RUN 	echo "" >> /etc/php/8.2/fpm/php.ini \
	&& echo "extension=imagick.so" >> /etc/php/8.2/cli/php.ini \
	&& echo "" >> /etc/php/8.2/fpm/php.ini

RUN 	echo "" >> /etc/php/8.2/fpm/php.ini \
	&& echo "extension=memcache.so" >> /etc/php/8.2/cli/php.ini \
	&& echo "" >> /etc/php/8.2/fpm/php.ini

RUN 	echo "" >> /etc/php/8.2/fpm/php.ini \
	&& echo "extension=protobuf.so" >> /etc/php/8.2/cli/php.ini \
	&& echo "" >> /etc/php/8.2/fpm/php.ini

RUN  	echo "" >> /etc/php/8.2/fpm/php.ini \
	&& echo "extension=grpc.so" >> /etc/php/8.2/cli/php.ini \
	&& echo "" >> /etc/php/8.2/fpm/php.ini

RUN rm -rf /tmp/* && mkdir /tmp/files

RUN ["chmod", "777", "/tmp/files"]
RUN ["chmod", "+x", "/app/entrypoint.sh"]
WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]
