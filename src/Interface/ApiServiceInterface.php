<?php

namespace App\Interface;

interface ApiServiceInterface
{
    /**
     * Sends a POST request with product data to the API.
     *
     * @param array $productData The product data to send to the API.
     * @return string
     */
    public function sendApiPost(array $productData): string;
}
