<?php

require_once 'vendor/autoload.php';

use Doctrine\ORM\Tools\SchemaTool;

$entityManager = require __DIR__ . '/src/database.php';

$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

if (!empty($metadata)) {
    $schemaTool = new SchemaTool($entityManager);
    $schemaTool->updateSchema($metadata, true); // `true` avoids dropping existing tables
    echo "Database schema updated successfully!";
} else {
    echo "No metadata found. Make sure you have defined your entities correctly.";
}
