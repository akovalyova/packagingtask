<?php

namespace App\Service;

use _PHPStan_e6dc705b2\Symfony\Component\String\Exception\RuntimeException;
use Exception;
use JsonSchema\Validator;

class ValidatorService
{
    public function __invoke(array $jsonData, string $schemaPath): bool
    {
        try {
            if (!file_exists($schemaPath) || !is_readable($schemaPath)) {
                throw new RuntimeException("File not found or inaccessible: $schemaPath");
            }
            $schemaJson = @file_get_contents($schemaPath);

            if ($schemaJson === false) {
                throw new RuntimeException(error_get_last()['message']);
            }
            $schema = json_decode($schemaJson, true, 52, JSON_THROW_ON_ERROR);

            $validator = new Validator();
            $validator->validate($jsonData, $schema);

            foreach ($validator->getErrors() as $error) {
                echo sprintf("Validation Error: %s\n", $error['message']);
            }
            return $validator->isValid();
        } catch (Exception $e) {
            echo sprintf("Exceptions during validation: %s\n", $e->getMessage());
            return false;
        }
    }
}
