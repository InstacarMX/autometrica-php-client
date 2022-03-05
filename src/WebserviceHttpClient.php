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

use Doctrine\Common\Annotations\AnnotationReader;
use Instacar\AutometricaWebserviceClient\Exceptions\BadRequestHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnauthorizedHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnknownHttpException;
use Instacar\AutometricaWebserviceClient\Response\CollectionResponseInterface;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
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
     */
    public function __construct(ClientInterface $client)
    {
        $annotationReader = new AnnotationReader();
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader($annotationReader));
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $propertyTypeExtractor = new ReflectionExtractor();
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer($classMetadataFactory, $nameConverter, null, $propertyTypeExtractor),
                new ArrayDenormalizer(),
            ],
            ['json' => new JsonEncoder()],
        );
        $this->client = $client;
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
        array $headers = []
    ): iterable {
        $normalizedHeaders = $this->normalizeHeaders($headers);

        $request = new Request($method, $endpoint, $normalizedHeaders);
        $response = $this->client->sendRequest($request);
        $statusCode = $response->getStatusCode();

        // The status code 400 Bad Request should be 403 Forbidden, but that's how the Autometrica server use them.
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

    /**
     * @phpstan-param array<string, mixed> $headers
     * @param array $headers
     * @phpstan-return  array<string, string>
     * @return array
     */
    private function normalizeHeaders(array $headers): array
    {
        // The Autometrica Webservice does not understand the UTF-8 charset in the headers, so we need to convert the
        // charset to ISO-8859-1.
        foreach ($headers as $header => $value) {
            if (is_array($value)) {
                $value = array_map('utf8_decode', $value);
            } else {
                $value = utf8_decode($value);
            }

            $headers[$header] = $value;
        }

        return $headers;
    }
}
