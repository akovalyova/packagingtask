<?php

namespace App\Service;

use App\Entity\Cache;
use App\Interface\CachingInterface;
use Doctrine\ORM\EntityManagerInterface;

class CacheService implements CachingInterface
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \JsonException
     */
    public function getCacheKey(array $products): string
    {
        usort($products, fn($a, $b) => $a['id'] <=> $b['id']);
        $normalizedData = json_encode($products, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', $normalizedData);
    }

    public function getCachedResponse(string $cacheKey): ?array
    {
        $cacheRepository = $this->entityManager->getRepository(Cache::class);
        /** @var Cache|null $cache */
        $cache = $cacheRepository->findOneBy(['requestHash' => $cacheKey]);

        if ($cache && !$cache->isExpired()) {
            return $cache->getResponse();
        }

        return null;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function storeResponse(string $cacheKey, array $response, ?int $ttlSeconds = 86400): void
    {
        $ttl = $ttlSeconds ? (new \DateTime())->modify("+{$ttlSeconds} seconds") : null;

        $cache = new Cache($cacheKey, $response, $ttl);
        $this->entityManager->persist($cache);
        $this->entityManager->flush();
    }
}
