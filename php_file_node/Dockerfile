FROM docker.getcompass.ru/service/packages:0.1.13 as packages
FROM docker.getcompass.ru/service/php-8:8.2.25-3

COPY --from=packages /tmp/migrate /usr/local/bin/migrate

RUN apk update && apk add --no-cache wget libxslt-dev curl-dev vips vips-dev memcached php82-pecl-vips imagemagick imagemagick-dev php82-pecl-imagick libtool \
ffmpeg ffmpeg-libavformat ffmpeg-libavfilter ffmpeg-libavcodec ffmpeg-libswscale ffmpeg-libavutil ffmpeg-libavdevice ffmpeg-libpostproc ffmpeg-libswresample

COPY . /app

RUN cd /app \
	&& mkdir -p /etc/ImageMagick-7 && mkdir -p /usr/local/lib/php/extensions/no-debug-non-zts-20220829 \
	&& cp dev/configs/etc/ImageMagick-7/policy.xml /etc/ImageMagick-7/policy.xml \
	&& cp dev/configs/etc/ffmpeg/libffmpeg-extension.so /usr/local/lib/libffmpeg-extension.so \
	&& cd /app && sh install.sh && chown -R www-data:www-data . && chmod +x . \
	&& cp dev/extensions/* /usr/local/lib/php/extensions/no-debug-non-zts-20220829/ \
	&& cp /usr/lib/php82/modules/* /usr/local/lib/php/extensions/no-debug-non-zts-20220829/

RUN docker-php-ext-enable vips imagick

RUN	echo "" >> /usr/local/etc/php/php.ini \
	&& echo "extension=cpp_extension_file.so" >> /usr/local/etc/php/php.ini \
	&& echo "" >> /usr/local/etc/php/php.ini

RUN rm -rf /tmp/* && mkdir /tmp/files

RUN ["chmod", "777", "/tmp/files"]
RUN ["chmod", "+x", "/app/entrypoint.sh"]
WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]
