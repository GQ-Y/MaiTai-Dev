# Backend build stage
FROM hyperf/hyperf:8.1-alpine-v3.18-swoole
LABEL maintainer="mineadmin" version="1.0" license="MIT" name="mineadmin"
WORKDIR /app

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    #  ---------- some config ----------
    && cd /etc/php81 \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man

RUN set -ex \
    && sed -i 's#https\?://dl-cdn.alpinelinux.org/alpine#https://mirrors.tuna.tsinghua.edu.cn/alpine#g' /etc/apk/repositories \
    && apk update \
    && apk add --no-cache libstdc++ openssl git bash autoconf pcre2-dev zlib-dev re2c gcc g++ make vim curl libzip libpng libjpeg-turbo readline libgcc icu-libs libsodium libpq sqlite-libs zstd-libs dos2unix \
    # php81-pear php81-dev php81-tokenizer php81-fileinfo php81-simplexml php81-xmlwriter \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS zlib-dev libaio-dev openssl-dev curl-dev  c-ares-dev \
    # && pecl channel-update pecl.php.net \
    # && pecl install --configureoptions 'enable-reader="yes"' xlswriter \
    # && echo "extension=xlswriter.so" >> /etc/php81/conf.d/60-xlswriter.ini \
    # && php -m \
    # && php -v \
    # && php --ri swoole \
    && mkdir -p /app-src \
    # ---------- clear works ----------
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :)"

ENV LD_PRELOAD=/usr/lib/preloadable_libiconv.so

WORKDIR /opt/www

# Copy application code
COPY . /opt/www

# Copy swoole-cli based on architecture and move to system path
RUN set -ex \
    && if [ "$(uname -m)" = "x86_64" ]; then \
        cp /opt/www/docker/x64/swoole-cli /opt/www/swoole-cli; \
    elif [ "$(uname -m)" = "aarch64" ] || [ "$(uname -m)" = "arm64" ]; then \
        cp /opt/www/docker/arm64/swoole-cli /opt/www/swoole-cli; \
    else \
        cp /opt/www/docker/macos/swoole-cli /opt/www/swoole-cli; \
    fi \
    && chmod +x /opt/www/swoole-cli \
    && cp /opt/www/swoole-cli /usr/local/bin/swoole-cli \
    && chmod +x /usr/local/bin/swoole-cli

# Install composer dependencies
RUN composer config repo.packagist composer https://mirrors.aliyun.com/composer/ \
    && cp .env.example .env \
    && dos2unix docker/install.sh docker/kill.sh docker/restart.sh docker/uninstall.sh docker/start.sh docker/plugin-manager.sh \
    && chmod +x docker/*.sh \
    # Create necessary directories with proper permissions \
    && mkdir -p /opt/www/runtime/container/proxy \
    && chmod -R 777 /opt/www/runtime/

# 暴露端口http websocket
EXPOSE 9501 9502 9509

# Default command that does nothing (will be overridden in docker-compose)
CMD ["sh", "-c", "echo 'Container started. Use docker-compose to run the application.'"]