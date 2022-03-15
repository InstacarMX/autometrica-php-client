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
use Instacar\AutometricaWebserviceClient\Model\AddOnPrice;
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use Instacar\AutometricaWebserviceClient\Model\AutometricaPrice;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use Nyholm\Psr7\Factory\Psr17Factory;
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
                        {
                            "index": 1001,
                            "brand": "Audi",
                            "subbrand": "A1",
                            "year": 2020,
                            "version": "Deluxe Edition"
                        },
                        { 
                            "index": 1002,
                            "brand": "Audi",
                            "subbrand": "A1",
                            "year": 2019,
                            "version": "NOTA: En este año, el vehículo no se comercializó en México"
                        },
                        {
                            "index": 2001,
                            "brand": "Volkswagen",
                            "subbrand": "Beetle",
                            "year": 2018,
                            "version": "Sport"
                        }
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
                            "version": "Agregar A/C",
                            "km_group": "A",
                            "sale": 200,
                            "purchase": 300
                        },
                        {
                            "brand": "Audi",
                            "subbrand": "A1",
                            "year": 2020,
                            "version": "Agregar TA",
                            "km_group": "A",
                            "sale": 100,
                            "purchase": 200
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
                        },
                        {
                            "brand": "Volkswagen",
                            "subbrand": "Beetle",
                            "year": 2018,
                            "version": "Agregar TA",
                            "km_group": 0,
                            "sale": 150,
                            "purchase": ""
                        }
                    ]
                }'
            ),
            new MockResponse('{}', ['http_code' => 400]),
            new MockResponse('{}', ['http_code' => 401]),
            new MockResponse('{}', ['http_code' => 500]),
        ], 'https://example.com/');
        $psr17Factory = new Psr17Factory();
        $psr18Client = new Psr18Client($httpClient);

        self::$autometricaClient = new AutometricaClient($psr18Client, $psr17Factory, 'test', 'test');
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

    public function testVehiclePrice(): void
    {
        $vehiclePrice = self::$autometricaClient->getPrice('Audi', 'A1', 2020, 'Deluxe Edition');
        $mileagePrice = $vehiclePrice->getMileagePrice();
        $addOnPrices = [ ...$vehiclePrice->getAddOnPrices() ];

        $this->assertContainsOnlyInstancesOf(AddOnPrice::class, $addOnPrices);
        $this->assertCount(2, $addOnPrices);

        $brand = $vehiclePrice->getBrand();
        $model = $vehiclePrice->getModel();
        $year = $vehiclePrice->getYear();
        $trim = $vehiclePrice->getTrim();
        $vehicleSalePrice = $vehiclePrice->getSalePrice();
        $vehiclePurchasePrice = $vehiclePrice->getPurchasePrice();
        $this->assertIsString($brand);
        $this->assertEquals('Audi', $brand);
        $this->assertIsString($model);
        $this->assertEquals('A1', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2020, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Deluxe Edition', $trim);
        $this->assertIsInt($vehicleSalePrice);
        $this->assertEquals(1000, $vehicleSalePrice);
        $this->assertIsInt($vehiclePurchasePrice);
        $this->assertEquals(1250, $vehiclePurchasePrice);

        $mileageGroup = $mileagePrice->getGroup();
        $mileageValue = $mileagePrice->getValue();
        $this->assertIsString($mileageGroup);
        $this->assertEquals('A', $mileageGroup);
        $this->assertIsInt($mileageValue);
        $this->assertEquals(0, $mileageValue);

        /** @var AddOnPrice $addOnPrice */
        $addOnPrice = $addOnPrices[0];
        $addOnName = $addOnPrice->getName();
        $addOnSalePrice = $addOnPrice->getSalePrice();
        $addOnPurchasePrice = $addOnPrice->getPurchasePrice();
        $this->assertIsString($addOnName);
        $this->assertEquals('Agregar A/C', $addOnName);
        $this->assertIsInt($addOnSalePrice);
        $this->assertEquals(200, $addOnSalePrice);
        $this->assertIsInt($addOnPurchasePrice);
        $this->assertEquals(300, $addOnPurchasePrice);

        /** @var AddOnPrice $addOnPrice */
        $addOnPrice = $addOnPrices[1];
        $addOnName = $addOnPrice->getName();
        $addOnSalePrice = $addOnPrice->getSalePrice();
        $addOnPurchasePrice = $addOnPrice->getPurchasePrice();
        $this->assertIsString($addOnName);
        $this->assertEquals('Agregar TA', $addOnName);
        $this->assertIsInt($addOnSalePrice);
        $this->assertEquals(100, $addOnSalePrice);
        $this->assertIsInt($addOnPurchasePrice);
        $this->assertEquals(200, $addOnPurchasePrice);
    }

    public function testRecentVehiclePrice(): void
    {
        $vehiclePrice = self::$autometricaClient->getPrice('Volkswagen', 'Beetle', 2018, 'Sport');
        $mileagePrice = $vehiclePrice->getMileagePrice();
        $addOnPrices = [ ...$vehiclePrice->getAddOnPrices() ];

        $this->assertNull($mileagePrice);
        $this->assertCount(1, $addOnPrices);

        $brand = $vehiclePrice->getBrand();
        $model = $vehiclePrice->getModel();
        $year = $vehiclePrice->getYear();
        $trim = $vehiclePrice->getTrim();
        $vehicleSalePrice = $vehiclePrice->getSalePrice();
        $vehiclePurchasePrice = $vehiclePrice->getPurchasePrice();
        $this->assertIsString($brand);
        $this->assertEquals('Volkswagen', $brand);
        $this->assertIsString($model);
        $this->assertEquals('Beetle', $model);
        $this->assertIsInt($year);
        $this->assertEquals(2018, $year);
        $this->assertIsString($trim);
        $this->assertEquals('Sport', $trim);
        $this->assertIsInt($vehicleSalePrice);
        $this->assertEquals(2000, $vehicleSalePrice);
        $this->assertNull($vehiclePurchasePrice);

        /** @var AddOnPrice $addOnPrice */
        $addOnPrice = $addOnPrices[0];
        $addOnName = $addOnPrice->getName();
        $addOnSalePrice = $addOnPrice->getSalePrice();
        $addOnPurchasePrice = $addOnPrice->getPurchasePrice();
        $this->assertIsString($addOnName);
        $this->assertEquals('Agregar TA', $addOnName);
        $this->assertIsInt($addOnSalePrice);
        $this->assertEquals(150, $addOnSalePrice);
        $this->assertNull($addOnPurchasePrice);
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
