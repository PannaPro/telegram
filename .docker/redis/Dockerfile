FROM redis:8.0.1-bookworm

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
      procps \
      iproute2 \
      iputils-ping \
      netcat-openbsd \
      curl \
      dnsutils \
      less \
    && rm -rf /var/lib/apt/lists/*

COPY --chown=redis:redis conf.d/users.acl /usr/local/etc/redis/users.acl
COPY --chown=redis:redis conf.d/redis.conf /usr/local/etc/redis/redis.conf

USER redis

CMD ["redis-server", "/usr/local/etc/redis/redis.conf"]