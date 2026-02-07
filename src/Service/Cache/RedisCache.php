<?php

namespace App\Service\Cache;

use App\Service\ExceptionHandler\CacheException;
use Redis;
use RedisException;

final readonly class RedisCache implements CacheInterface
{
    public function __construct(
        private Redis $redis
    ) {
    }

    public function set(string $key, mixed $value): void
    {
        try {
            $this->redis->set($key, $value);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function setEx(string $key, mixed $value, ?int $ttl = null): void
    {
        try {
            $this->redis->setex($key, $ttl, $value);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function get(string $key): mixed
    {
        try {
            return $this->redis->get($key);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function delete(string $key): void
    {
        try {
            $this->redis->del($key);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function exists(string $key): bool
    {
        try {
            return (bool)$this->redis->exists($key);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function hSet(string $hash, string $field, mixed $value): void
    {
        try {
            $this->redis->hSet($hash, $field, $value);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }

    public function hGet(string $hash, string $field): mixed
    {
        try {
            $value = $this->redis->hGet($hash, $field);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
        return $value !== false ? $value : null;
    }

    public function hDel(string $hash, string $field): void
    {
        try {
            $this->redis->hDel($hash, $field);
        } catch (RedisException) {
            throw CacheException::messageToClient();
        }
    }
}
