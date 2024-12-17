<?php

namespace App\Service;

class ResponseTransformer
{
    public function __invoke(array $responseArray, string $context = 'api'): array
    {
        if (empty($responseArray['response'])) {
            return $this->createResponse(0, null, 'No data found', [], $context);
        }

        $response = $responseArray['response'];
        if (
            $response['status'] === 0
            || !empty($response['not_packed_items'])
            || count($response['bins_packed'] ?? []) !== 1
        ) {
            return $this->createResponse(
                $response['status'],
                null,
                'Could not find single box for submitted items',
                $response['errors'] ?? [],
                $context
            );
        }
        $boxData = $response['bins_packed'][0]['bin_data'] ?? [];
        $result = [
            'id' => $boxData['id'] ?? null,
            'width' => $boxData['w'] ?? null,
            'height' => $boxData['h'] ?? null,
            'length' => $boxData['d'] ?? null,
            'max_weight' => $boxData['gross_weight'] ?? null,
        ];

        return $this->createResponse(
            $response['status'],
            $result,
            'Your items can be packed in this box',
            $response['errors'] ?? [],
            $context
        );
    }
    private function createResponse(int $status, $data, string $message, array $errors, string $context): array
    {
        return [
            'status' => $status,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
            'context' => $context,
        ];
    }
}
