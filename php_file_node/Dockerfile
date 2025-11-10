FROM docker.getcompass.ru/service/php-8:8.4.12-3

RUN apk update && apk add --no-cache vips imagemagick \
ffmpeg ffmpeg-libavformat ffmpeg-libavfilter ffmpeg-libavcodec ffmpeg-libswscale ffmpeg-libavutil ffmpeg-libavdevice ffmpeg-libpostproc ffmpeg-libswresample

COPY . /app

RUN cd /app \
	&& mkdir -p /etc/ImageMagick-7 && mkdir -p /usr/local/lib/php/extensions/no-debug-non-zts-20240924 \
	&& cp dev/configs/etc/ImageMagick-7/policy.xml /etc/ImageMagick-7/policy.xml \
	&& cd /app && sh install.sh && chown -R www-data:www-data . && chmod +x . \
	&& cp dev/extensions/* /usr/local/lib/php/extensions/no-debug-non-zts-20240924/

RUN docker-php-ext-enable vips imagick cpp_extension_file

RUN rm -rf /tmp/* && mkdir /tmp/files

RUN ["chmod", "777", "/tmp/files"]
RUN ["chmod", "+x", "/app/entrypoint.sh"]
WORKDIR /app
ENTRYPOINT ["bash", "/app/entrypoint.sh"]
