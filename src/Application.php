<?php

namespace App;

use App\Interface\ApiResourceInterface;
use App\Interface\CachingInterface;
use App\Service\CacheService;
use App\Service\ResponseTransformer;
use App\Service\ValidatorService;
use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

class Application
{
    private CacheService $cacheService;
    private ValidatorService $validatorService;
    private ResponseTransformer $transformerService;
    private ApiResourceInterface $apiService;
    private const INPUT_SCHEMA = __DIR__ . '/Config/schema.json';
    private const API_SCHEMA = __DIR__ . '/Config/api_schema.json';
    public function __construct(
        CachingInterface $cacheService,
        ValidatorService $validatorService,
        ResponseTransformer $transformerService,
        ApiResourceInterface $apiService,
    ) {
        $this->cacheService = $cacheService;
        $this->validatorService = $validatorService;
        $this->transformerService = $transformerService;
        $this->apiService = $apiService;
    }

    public function run(RequestInterface $request): MessageInterface
    {
        try {
            $products = $this->getRequestData($request);

            $this->validateInput($products, self::INPUT_SCHEMA);

            $cacheKey = $this->cacheService->getCacheKey($products);

            $cachedResponse = $this->getCachedResponse($cacheKey);
            if ($cachedResponse) {
                return $this->createResponse($cachedResponse);
            }

            $apiResponse = $this->fetchApiResponse($products);
            $this->validateInput($apiResponse, self::API_SCHEMA);

            $extractedResponse = ($this->transformerService)($apiResponse, 'api');

            $this->cacheResponseIfNeeded($apiResponse, $extractedResponse, $cacheKey);

            return $this->createResponse($extractedResponse);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    private function getRequestData(RequestInterface $request): array
    {
        $contents = $request->getBody()->getContents();
        return json_decode($contents, true, 52, JSON_THROW_ON_ERROR);
    }

    private function validateInput(array $data, string $schema): void
    {
        if (!($this->validatorService)($data, $schema)) {
            throw new UnexpectedValueException('Invalid input data');
        }
    }

    private function getCachedResponse(string $cacheKey): ?array
    {
        $cachedResponse = $this->cacheService->getCachedResponse($cacheKey);
        return $cachedResponse ? ($this->transformerService)($cachedResponse, 'cache') : null;
    }

    private function fetchApiResponse(array $products): array
    {
        $apiResponseJson = $this->apiService->sendApiPost($products['products']);
        return json_decode($apiResponseJson, true, 52, JSON_THROW_ON_ERROR);
    }

    private function cacheResponseIfNeeded(array $apiResponse, array $extractedResponse, string $cacheKey): void
    {
        if (isset($extractedResponse['status']) && $extractedResponse['status'] !== -1) {
            $this->cacheService->storeResponse($cacheKey, $apiResponse);
        } else {
            // Add logging here for unauthorized or API issues
        }
    }

    private function createResponse(array $data): MessageInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    private function handleError(Exception $e): MessageInterface
    {
        $result = [
            'data' => null,
            'message' => 'Error occurred, trying to get data for submitted items',
            'errors' => [$e->getMessage()],
        ];
        return new Response(
            500,
            ['Content-Type' => 'application/json'],
            json_encode($result, JSON_THROW_ON_ERROR)
        );
    }
}
