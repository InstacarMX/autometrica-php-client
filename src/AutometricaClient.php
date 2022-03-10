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
use Instacar\AutometricaWebserviceClient\Response\ItemResponseInterface;
use Instacar\AutometricaWebserviceClient\Response\VehiclePricesResponse;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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

    private RequestFactoryInterface $requestFactory;

    private SerializerInterface $serializer;

    private string $username;

    private string $password;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $username,
        string $password
    ) {
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
        $this->requestFactory = $requestFactory;
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
     * @return VehiclePrice
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    public function getPrice(string $brand, string $model, int $year, string $trim, int $mileage = 0): VehiclePrice
    {
        return $this->requestItem(VehiclePricesResponse::class, 'lineal.php', 'POST', [
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
        if (!class_exists(Request::class)) {
            throw new LogicException(
                'You must install the Nyholm PSR-7 implementation.' . PHP_EOL .
                'Please, execute "composer require nyholm/psr7" in your project root'
            );
        }

        $httpClient = HttpClient::create();
        $httpFactory = new Psr17Factory();
        $psr18Client = new Psr18Client($httpClient);

        return new self($psr18Client, $httpFactory, $username, $password);
    }

    /**
     * @phpstan-template T of object
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
        return $this->makeRequest($responseClass, $endpoint, $method, $headers)->getData();
    }

    /**
     * @phpstan-template T of object
     * @phpstan-template TResponse of ItemResponseInterface<T>
     * @phpstan-param class-string<TResponse> $responseClass
     * @param string $responseClass
     * @param string $endpoint
     * @param string $method
     * @phpstan-param array<string, mixed> $headers
     * @param array $headers
     * @phpstan-return T
     * @return object
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    private function requestItem(
        string $responseClass,
        string $endpoint,
        string $method = 'GET',
        array $headers = []
    ) {
        return $this->makeRequest($responseClass, $endpoint, $method, $headers)->getItem();
    }

    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $responseClass
     * @param string $responseClass
     * @param string $endpoint
     * @param string $method
     * @phpstan-param array<string, mixed> $headers
     * @param array $headers
     * @phpstan-return T
     * @return object
     * @throws ClientExceptionInterface
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws UnknownHttpException
     */
    private function makeRequest(
        string $responseClass,
        string $endpoint,
        string $method = 'GET',
        array $headers = []
    ) {
        $request = $this->requestFactory->createRequest($method, self::BASE_URL . $endpoint);
        $request = $this->addHeaders($request, $headers);
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

        return $this->deserializeResponse($response, $responseClass);
    }

    /**
     * @param RequestInterface $request
     * @phpstan-param array<string, mixed> $headers
     * @param array $headers
     * @return RequestInterface
     */
    private function addHeaders(RequestInterface $request, array $headers): RequestInterface
    {
        // The Autometrica Webservice does not understand the UTF-8 charset in the headers, so we need to convert the
        // charset to ISO-8859-1.
        foreach ($headers as $header => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $request = $request->withAddedHeader($header, utf8_decode($item));
                }
            } else {
                $request = $request->withHeader($header, utf8_decode($value));
            }
        }

        // Set default headers
        return $request
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withHeader('Username', $this->username)
            ->withHeader('Password', $this->password)
        ;
    }

    /**
     * @phpstan-template T of object
     * @param ResponseInterface $response
     * @phpstan-param class-string<T> $responseClass
     * @param string $responseClass
     * @phpstan-return T
     * @return object
     */
    private function deserializeResponse(ResponseInterface $response, string $responseClass): object
    {
        $responseBody = $response->getBody()->getContents();

        /** @phpstan-var T */
        return $this->serializer->deserialize(
            $responseBody,
            $responseClass,
            'json',
            [
                // We need to disable the type enforcement to manage the unknown kmGroup 0 in some prices
                // With the typed properties for PHP 7.4, this should not be a problem
                'disable_type_enforcement' => true,
            ],
        );
    }
}
