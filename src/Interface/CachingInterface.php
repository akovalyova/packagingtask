<?php

namespace App\Interface;

interface CachingInterface
{
    /**
     * Generate a cache key based on the given product data.
     *
     * @param array $products
     * @return string
     * @throws \JsonException
     */
    public function getCacheKey(array $products): string;

    /**
     * Retrieve a cached response based on the cache key.
     *
     * @param string $cacheKey
     * @return array|null
     */
    public function getCachedResponse(string $cacheKey): ?array;

    /**
     * Store a response in the cache with a specific TTL (time-to-live).
     *
     * @param string $cacheKey
     * @param array $response
     * @param int|null $ttlSeconds
     * @return void
     */
    public function storeResponse(string $cacheKey, array $response, ?int $ttlSeconds = 86400): void;
}
