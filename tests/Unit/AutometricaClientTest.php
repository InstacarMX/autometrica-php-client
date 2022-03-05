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

use Instacar\AutometricaWebserviceClient\AutometricaClient;
use Instacar\AutometricaWebserviceClient\Exceptions\BadRequestHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnauthorizedHttpException;
use Instacar\AutometricaWebserviceClient\Exceptions\UnknownHttpException;
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @group unit
 */
class AutometricaClientTest extends TestCase
{
    private static AutometricaClient $autometricaClient;

    public static function setUpBeforeClass(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(
                '{
                    "catalogo_lineal": [
                        { "index": 1001, "brand": "Audi", "subbrand": "A1", "year": 2020, "version": "Deluxe Edition" },
                        { "index": 2001, "brand": "Volkswagen", "subbrand": "Beetle", "year": 2018, "version": "Sport" }
                    ]
                }'
            ),
            new MockResponse(
                '{
                    "lineal": [
                        {
                            "brand": "Audi",
                            "subbrand": "A1",
                            "year": 2020,
                            "version": "Deluxe Edition",
                            "km_group": "A",
                            "sale": 1000,
                            "purchase": 1250
                        },
                        {
                            "brand": "Audi",
                            "subbrand": "A1",
                            "year": 2020,
                            "version": "Valor kilometraje",
                            "km_group": "A",
                            "sale": 0,
                            "purchase": 0
                        }
                    ]
                }'
            ),
            new MockResponse(
                '{
                    "lineal": [
                        {
                            "brand": "Volkswagen",
                            "subbrand": "Beetle",
                            "year": 2018,
                            "version": "Sport",
                            "km_group": 0,
                            "sale": 2000,
                            "purchase": 0
                        }
                    ]
                }'
            ),
            new MockResponse('{}', ['http_code' => 400]),
            new MockResponse('{}', ['http_code' => 401]),
            new MockResponse('{}', ['http_code' => 500]),
        ], 'https://example.com/');
        $psr18Client = new Psr18Client($httpClient);

        self::$autometricaClient = new AutometricaClient($psr18Client, 'test', 'test');
    }

    public function testCatalog(): void
    {
        $collection = self::$autometricaClient->getCatalog();
        $this->assertIsIterable($collection);

        $array = [ ...$collection ];
        $this->assertContainsOnlyInstancesOf(Vehicle::class, $array);
        $this->assertCount(2, $array);

        /** @var Vehicle $item */
        $item = $array[0];
        $index = $item->getIndex();
        $brand = $item->getBrand();
        $model = $item->getModel();
        $year = $item->getYear();
        $trim = $item->getTrim();
        $this->assertIsInt($index);
        $this->assertEquals(1001, $index);
        $this->assertIsString($brand);
        $this->assertEquals('Audi', $brand);
        $this->assertIsString($model);
        $this->assertEquals('A1', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2020, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Deluxe Edition', $trim);

        /** @var Vehicle $item */
        $item = $array[1];
        $index = $item->getIndex();
        $brand = $item->getBrand();
        $model = $item->getModel();
        $year = $item->getYear();
        $trim = $item->getTrim();
        $this->assertIsInt($index);
        $this->assertEquals(2001, $index);
        $this->assertIsString($brand);
        $this->assertEquals('Volkswagen', $brand);
        $this->assertIsString($model);
        $this->assertEquals('Beetle', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2018, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Sport', $trim);
    }

    public function testPricesWithKilometerGroup(): void
    {
        $collection = self::$autometricaClient->getPrices('Audi', 'A1', 2020, 'Deluxe Edition');
        $this->assertIsIterable($collection);

        $array = [ ...$collection ];
        $this->assertContainsOnlyInstancesOf(VehiclePrice::class, $array);
        $this->assertCount(2, $array);

        /** @var VehiclePrice $item */
        $item = $array[0];
        $brand = $item->getBrand();
        $model = $item->getModel();
        $year = $item->getYear();
        $trim = $item->getTrim();
        $kilometerGroup = $item->getKilometerGroup();
        $salePrice = $item->getSalePrice();
        $purchasePrice = $item->getPurchasePrice();
        $this->assertIsString($brand);
        $this->assertEquals('Audi', $brand);
        $this->assertIsString($model);
        $this->assertEquals('A1', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2020, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Deluxe Edition', $trim);
        $this->assertIsString($kilometerGroup);
        $this->assertEquals('A', $kilometerGroup);
        $this->assertIsInt($salePrice);
        $this->assertEquals(1000, $salePrice);
        $this->assertIsInt($purchasePrice);
        $this->assertEquals(1250, $purchasePrice);

        /** @var VehiclePrice $item */
        $item = $array[1];
        $brand = $item->getBrand();
        $model = $item->getModel();
        $year = $item->getYear();
        $trim = $item->getTrim();
        $kilometerGroup = $item->getKilometerGroup();
        $salePrice = $item->getSalePrice();
        $purchasePrice = $item->getPurchasePrice();
        $this->assertIsString($brand);
        $this->assertEquals('Audi', $brand);
        $this->assertIsString($model);
        $this->assertEquals('A1', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2020, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Valor kilometraje', $trim);
        $this->assertIsString($kilometerGroup);
        $this->assertEquals('A', $kilometerGroup);
        $this->assertIsInt($salePrice);
        $this->assertEquals(0, $salePrice);
        $this->assertIsInt($purchasePrice);
        $this->assertEquals(0, $purchasePrice);
    }

    public function testPricesWithoutKilometerGroup(): void
    {
        $collection = self::$autometricaClient->getPrices('Volkswagen', 'Beetle', 2018, 'Sport');
        $this->assertIsIterable($collection);

        $array = [ ...$collection ];
        $this->assertContainsOnlyInstancesOf(VehiclePrice::class, $array);
        $this->assertCount(1, $array);

        /** @var VehiclePrice $item */
        $item = $array[0];
        $brand = $item->getBrand();
        $model = $item->getModel();
        $year = $item->getYear();
        $trim = $item->getTrim();
        $kilometerGroup = $item->getKilometerGroup();
        $salePrice = $item->getSalePrice();
        $purchasePrice = $item->getPurchasePrice();
        $this->assertIsString($brand);
        $this->assertEquals('Volkswagen', $brand);
        $this->assertIsString($model);
        $this->assertEquals('Beetle', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2018, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Sport', $trim);
        $this->assertNull($kilometerGroup);
        $this->assertIsInt($salePrice);
        $this->assertEquals(2000, $salePrice);
        $this->assertIsInt($purchasePrice);
        $this->assertEquals(0, $purchasePrice);
    }

    public function testBadRequestException(): void
    {
        $this->expectException(BadRequestHttpException::class);

        self::$autometricaClient->getCatalog();
    }

    public function testUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedHttpException::class);

        self::$autometricaClient->getCatalog();
    }

    public function testServerErrorException(): void
    {
        $this->expectException(UnknownHttpException::class);

        self::$autometricaClient->getCatalog();
    }
}
