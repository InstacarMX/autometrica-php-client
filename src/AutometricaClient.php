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
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use Instacar\AutometricaWebserviceClient\Response\CatalogResponse;
use Instacar\AutometricaWebserviceClient\Response\CollectionResponseInterface;
use Instacar\AutometricaWebserviceClient\Response\VehiclePricesResponse;
use LogicException;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AutometricaClient
{
    private const BASE_URL = 'https://ws.autometrica.mx/';

    private ClientInterface $client;

    private SerializerInterface $serializer;

    private string $username;

    private string $password;

    public function __construct(ClientInterface $client, string $username, string $password)
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
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return Vehicle[]
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    public function getCatalog(): iterable
    {
        return $this->requestCollection(CatalogResponse::class, 'catalogo.php');
    }

    /**
     * @param string $brand
     * @param string $model
     * @param int $year
     * @param string $trim
     * @param int $mileage
     * @return VehiclePrice[]
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    public function getPrices(string $brand, string $model, int $year, string $trim, int $mileage = 0): iterable
    {
        return $this->requestCollection(VehiclePricesResponse::class, 'lineal.php', 'POST', [
            'brand' => $brand,
            'subbrand' => $model,
            'year' => $year,
            'version' => $trim,
            'kilometraje' => $mileage,
        ]);
    }

    public static function createDefault(string $username, string $password): self
    {
        if (!class_exists(HttpClient::class)) {
            throw new LogicException(
                'You must install the Symfony HTTP Client component.' . PHP_EOL .
                'Please, execute "composer require symfony/http-client" in your project root'
            );
        }

        $httpClient = HttpClient::create();
        $psr18Client = new Psr18Client($httpClient);

        return new self($psr18Client, $username, $password);
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
    private function requestCollection(
        string $responseClass,
        string $endpoint,
        string $method = 'GET',
        array $headers = []
    ): iterable {
        $normalizedHeaders = $this->normalizeHeaders($headers);

        $request = new Request($method, self::BASE_URL . $endpoint, $normalizedHeaders);
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
     * @phpstan-return array<string, string>
     * @return array
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalizedHeaders = [];

        // The Autometrica Webservice does not understand the UTF-8 charset in the headers, so we need to convert the
        // charset to ISO-8859-1.
        foreach ($headers as $header => $value) {
            if (is_array($value)) {
                $normalizedValue = array_map('utf8_decode', $value);
            } else {
                $normalizedValue = utf8_decode($value);
            }

            $normalizedHeaders[$header] = $normalizedValue;
        }

        // Set default headers
        $normalizedHeaders['Content-Type'] = 'application/json; charset=UTF-8';
        $normalizedHeaders['Username'] = $this->username;
        $normalizedHeaders['Password'] = $this->password;

        return $normalizedHeaders;
    }
}
