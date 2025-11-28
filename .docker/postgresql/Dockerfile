FROM postgres:18.1-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends locales \
    && sed -i 's/# \(ru_RU.UTF-8 UTF-8\)/\1/' /etc/locale.gen \
    && locale-gen ru_RU.UTF-8 \
    && update-locale LANG=ru_RU.UTF-8 \
    && rm -rf /var/lib/apt/lists/*

ENV LANG=ru_RU.UTF-8 \
    LANGUAGE=ru_RU:ru \
    LC_ALL=ru_RU.UTF-8 \
    POSTGRES_INITDB_ARGS="--encoding=UTF8 --locale=ru_RU.UTF-8"

# потом добавить скрипт для создания разных ролей и юзеров
COPY ./initdb.d/ /docker-entrypoint-initdb.d/

EXPOSE 5432
