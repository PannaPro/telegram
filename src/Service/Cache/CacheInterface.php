<?php

namespace App\Service\Cache;

interface CacheInterface
{
    public function set(string $key, mixed $value): void;
    public function setEx(string $key, mixed $value, ?int $ttl = null): void;
    public function get(string $key): mixed;
    public function delete(string $key): void;
    public function exists(string $key): bool;

    public function hSet(string $hash, string $field, mixed $value): void;
    public function hGet(string $hash, string $field): mixed;
    public function hDel(string $hash, string $field): void;
}
