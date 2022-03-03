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

use Instacar\AutometricaWebserviceClient\Exceptions\BadRequestHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnauthorizedHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnknownHttpException;
use Instacar\AutometricaWebserviceClient\Response\CollectionResponseInterface;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
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
     * @phpstan-template T
     * @phpstan-template TResponse of CollectionResponseInterface<T>
     * @phpstan-param class-string<TResponse> $responseClass
     * @param string $responseClass
     * @param string $endpoint
     * @param string $method
     * @phpstan-param array<string, mixed> $headers
     * @param array $headers
     * @param mixed $body
     * @phpstan-return iterable<T>
     * @return iterable
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    public function requestCollection(
        string $responseClass,
        string $endpoint,
        string $method = 'GET',
        array $headers = [],
        $body = null
    ): iterable {
        if ($body !== null) {
            $body = $this->serializer->serialize($body, 'json');
        }

        $request = new Request($method, $endpoint, $headers, $body);
        $response = $this->client->sendRequest($request);
        $statusCode = $response->getStatusCode();

        // The use of the status codes 400 and 401 is inverted, but that's how the Autometrica server use them.
        if ($statusCode === 400) {
            throw new BadRequestHttpException('The username or password is incorrect');
        }
        if ($statusCode === 401) {
            throw new UnauthorizedHttpException('The username or password is required');
        }

        if ($statusCode < 200 || $statusCode > 299) {
            throw new UnknownHttpException($response->getReasonPhrase());
        }

        $responseBody = $response->getBody()->getContents();
        /** @phpstan-var TResponse $dataResponse */
        $dataResponse = $this->serializer->deserialize($responseBody, $responseClass, 'json', [
            // We need to disable the type enforcement to manage the unknown kmGroup 0 in some prices
            // With the typed properties for PHP 7.4, this should not be a problem
            'disable_type_enforcement' => true,
        ]);

        return $dataResponse->getData();
    }
}
