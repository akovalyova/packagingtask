<?php

namespace App\Service;

class ExtractorService
{
    public function __invoke(array $responseArray, string $context = 'api'): array
    {
        if (empty($responseArray) || !isset($responseArray['response'])) {
            return [
                'status' => 0,
                'data' => null,
                'message' => 'No data found',
                'errors' => []
            ];
        }

        if (
            $responseArray['response']['status'] === 0
             || count($responseArray['response']['not_packed_items'])
             || count($responseArray['response']['bins_packed']) !== 1
        ) {
            return [
                'status' => $responseArray['response']['status'],
                'data' => null,
                'message' => 'Could not find single box for submitted items',
                'errors' => $responseArray['response']['errors'],
                'context' =>  $context
            ];
        }

        $boxData = $responseArray['response']['bins_packed'][0]['bin_data'];
        $result['id'] = $boxData['id'];
        $result['width'] = $boxData['w'];
        $result['height'] = $boxData['h'];
        $result['length'] = $boxData['d'];
        $result['max_weight'] = $boxData['gross_weight'];

        return [
              'status' => $responseArray['response']['status'],
              'data' => $result,
              'message' => 'You items can be packed in this box',
              'errors' => $responseArray['response']['errors'],
              'context' =>  $context
        ];
    }
}
