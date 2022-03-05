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

namespace Instacar\AutometricaWebserviceClient\Test\Unit;

use Instacar\AutometricaWebserviceClient\Exceptions\BadRequestHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnauthorizedHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnknownHttpException;
use Instacar\AutometricaWebserviceClient\WebserviceHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @group unit
 */
class WebserviceHttpClientTest extends TestCase
{
    private static WebserviceHttpClient $webserviceClient;

    public static function setUpBeforeClass(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(
                '{
                    "data": [
                        { "name": "Test", "age": 10, "price": 100.55 },
                        { "name": "Trial", "age": 5, "price": 120.34 }
                    ]
                }',
                []
            ),
            new MockResponse('{}', ['http_code' => 400]),
            new MockResponse('{}', ['http_code' => 401]),
            new MockResponse('{}', ['http_code' => 500]),
        ], 'https://example.com/');
        $psr18Client = new Psr18Client($httpClient);

        self::$webserviceClient = new WebserviceHttpClient($psr18Client, 'test', 'test');
    }

    public function testRequestCollection(): void
    {
        $collection = self::$webserviceClient->requestCollection(MockedCollection::class, '/test');
        $this->assertIsIterable($collection);
        $this->assertContainsOnlyInstancesOf(MockedItem::class, $collection);

        $array = [ ...$collection ];
        $this->assertCount(2, $array);

        /** @var MockedItem $item */
        $item = $array[0];
        $name = $item->getName();
        $age = $item->getAge();
        $price = $item->getPrice();
        $this->assertIsString($name);
        $this->assertEquals('Test', $name);
        $this->assertIsInt($age);
        $this->assertEquals(10, $age);
        $this->assertIsFloat($price);
        $this->assertEquals(100.55, $price);

        /** @var MockedItem $item */
        $item = $array[1];
        $name = $item->getName();
        $age = $item->getAge();
        $price = $item->getPrice();
        $this->assertIsString($name);
        $this->assertEquals('Trial', $name);
        $this->assertIsInt($age);
        $this->assertEquals(5, $age);
        $this->assertIsFloat($price);
        $this->assertEquals(120.34, $price);
    }

    public function testBadRequestException(): void
    {
        $this->expectException(BadRequestHttpException::class);

        self::$webserviceClient->requestCollection(MockedCollection::class, '/test');
    }

    public function testUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        self::$webserviceClient->requestCollection(MockedCollection::class, '/test');
    }

    public function testServerErrorException(): void
    {
        $this->expectException(UnknownHttpException::class);

        self::$webserviceClient->requestCollection(MockedCollection::class, '/test');
    }
}
