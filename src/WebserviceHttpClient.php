<?php

/*
 * Copyright (c) Instacar 2021.
 * This file is part of AutometricaWebserviceClient.
 *
 * AutometricaWebserviceClient is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AutometricaWebserviceClient is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU  Lesser General Public License
 * along with AutometricaWebserviceClient.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Instacar\AutometricaWebserviceClient;

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WebserviceHttpClient
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param ClientInterface $client
     * @param SerializerInterface $serializer
     */
    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @param string $responseClass
     * @param string $endpoint
     * @param string$method
     * @param string[] $headers
     * @param mixed $body
     * @return mixed
     * @throws ClientExceptionInterface
     */
    public function request(
        string $responseClass,
        string $endpoint,
        string $method = 'GET',
        array $headers = [],
        $body = null
    ) {
        if ($body !== null) {
            $body = $this->serializer->serialize($body, 'json');
        }

        $request = new Request($method, $endpoint, $headers, $body);
        $response = $this->client->sendRequest($request)->getBody()->getContents();
        $dataResponse = $this->serializer->deserialize($response, $responseClass, 'json');

        return $dataResponse->getData();
    }
}
