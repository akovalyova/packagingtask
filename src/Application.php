<?php

namespace App;

use App\Interface\ApiServiceInterface;
use App\Service\CacheService;
use App\Service\ExtractorService;
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
    private ExtractorService $extractorService;
    private ApiServiceInterface $apiService;
    private const INPUT_SCHEMA = __DIR__ . '/Config/schema.json';
    private const API_SCHEMA = __DIR__ . '/Config/api_schema.json';
    public function __construct(
        CacheService $cacheService,
        ValidatorService $validatorService,
        ExtractorService $extractorService,
        ApiServiceInterface $apiService,
    ) {
        $this->cacheService = $cacheService;
        $this->validatorService = $validatorService;
        $this->extractorService = $extractorService;
        $this->apiService = $apiService;
    }

    public function run(RequestInterface $request): MessageInterface
    {
        try {
            $contents = $request->getBody()->getContents();
            $products = json_decode($contents, true, 52, JSON_THROW_ON_ERROR);

            if (!($this->validatorService)($products, self::INPUT_SCHEMA)) {
                throw new UnexpectedValueException();
            }
            $cacheKey = $this->cacheService->getCacheKey($products);

            if ($cachedResponse = $this->cacheService->getCachedResponse($cacheKey)) {
                if ($response = ($this->extractorService)($cachedResponse, 'cache')) {
                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode($response, JSON_THROW_ON_ERROR)
                    );
                }
            }
            $apiResponseJson = $this->apiService->sendApiPost($products['products']);

            $apiResponse = json_decode($apiResponseJson, true, 52, JSON_THROW_ON_ERROR);

            if (!($this->validatorService)($products, self::API_SCHEMA)) {
                throw new UnexpectedValueException();
            }

            if ($response = ($this->extractorService)($apiResponse, 'api')) {
                if ($response['status'] !== -1) {
                    $this->cacheService->storeResponse($cacheKey, $apiResponse);
                } else {
                    //add logging here, since there is some problem with API (unauthorized or other)
                }
                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode($response, JSON_THROW_ON_ERROR)
                );
            }
        } catch (Exception $e) {
             $result = ['data' => null,
                 'message' => 'Error occurred, trying to get data for submitted items',
                 'errors' => [$e->getMessage()]];
             return new Response(
                 500,
                 ['Content-Type' => 'application/json'],
                 json_encode($result, JSON_THROW_ON_ERROR)
             );
        }
    }
}
