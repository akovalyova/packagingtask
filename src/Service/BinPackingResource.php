<?php

namespace App\Service;

use App\Entity\Packaging;
use App\Interface\ApiResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BinPackingResource implements ApiResourceInterface
{
    private const API_URL = 'https://eu.api.3dbinpacking.com/packer/packIntoMany';
    private string $apiKey;
    private string $userName;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Client $httpClient
    ) {
        $this->apiKey = getenv('BIN_API_KEY') ?: '';
        $this->userName = getenv('BIN_USER_NAME') ?: '';
    }

    /**
     * @throws GuzzleException
     */
    public function sendApiPost(array $productData): string
    {
        $payload = $this->getApiPayload($productData);

        $response = $this->httpClient->post(self::API_URL, ['json' => $payload]);
        return $response->getBody()->getContents();
    }

    private function getApiPayload(array $productData): array
    {
        $payload = [];
        $payload['username'] = $this->userName;
        $payload['api_key'] = $this->apiKey;

        $payload['items'] = array_map(static function ($product) {
            return [
                'id' => $product['id'],
                'q' => 1,
                'w' => $product['width'],
                'h' => $product['height'],
                'd' => $product['length'],
                'wg' => $product['weight'],
            ];
        }, $productData);

        $payload['bins'] = array_map(static function ($pack) {
            return [
                'id' => $pack->getId(),
                'w' => $pack->getWidth(),
                'h' => $pack->getHeight(),
                'd' => $pack->getLength(),
                'max_wg' => $pack->getMaxWeight(),
            ];
        }, $this->getAvailablePackaging());

        $payload['params'] = ['optimization_mode' => 'bins_number'];

        return $payload;
    }
    /**
     * This might better belong to separate service that has access to DB and will get the data for packaging
     * @return Packaging[]|null An array of Packaging entities or null if none found
     */
    private function getAvailablePackaging(): ?array
    {
        return  $this->entityManager->getRepository(Packaging::class)->findAll();
    }
}
