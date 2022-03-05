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

namespace Instacar\AutometricaWebserviceClient\Test\Integration;

use Instacar\AutometricaWebserviceClient\AutometricaClient;
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class AutometricaClientTest extends TestCase
{
    private AutometricaClient $client;

    protected function setUp(): void
    {
        $this->client = AutometricaClient::createDefault(
            $_SERVER['WEBSERVICE_USERNAME'],
            $_SERVER['WEBSERVICE_PASSWORD'],
        );
    }

    public function testCatalog(): Vehicle
    {
        $catalog = $this->client->getCatalog();

        return $this->assertCollection(Vehicle::class, $catalog, [$this, 'assertVehicle']);
    }

    /**
     * @depends testCatalog
     */
    public function testPrices(Vehicle $vehicle): void
    {
        $prices = $this->client->getPrices(
            $vehicle->getBrand(),
            $vehicle->getModel(),
            $vehicle->getYear(),
            $vehicle->getTrim(),
        );

        $this->assertCollection(VehiclePrice::class, $prices, [$this, 'assertPrice']);
    }

    /**
     * @param string $className
     * @param mixed $collection
     * @param callable $extraAssertions
     * @return mixed
     */
    private function assertCollection(
        string $className,
        $collection,
        callable $extraAssertions
    ) {
        $this->assertIsIterable($collection);

        $item = null;
        foreach ($collection as $item) {
            $this->assertItem($className, $item, $extraAssertions);
        }

        $this->assertNotNull($item, 'The collection must have at least one item');
        return $item;
    }

    /**
     * @param string $className
     * @param mixed $item
     * @param callable $extraAssertions
     * @return void
     */
    private function assertItem(string $className, $item, callable $extraAssertions): void
    {
        $this->assertNotNull($item);
        $this->assertInstanceOf($className, $item);
        $extraAssertions($item);
    }

    private function assertVehicle(Vehicle $vehicle): void
    {
        $index = $vehicle->getIndex();
        $brand = $vehicle->getBrand();
        $model = $vehicle->getModel();
        $year = $vehicle->getYear();
        $trim = $vehicle->getTrim();

        $this->assertNotNull($index);
        $this->assertIsInt($index);
        $this->assertNotNull($brand);
        $this->assertIsString($brand);
        $this->assertNotNull($model);
        $this->assertIsString($model);
        $this->assertNotNull($year);
        $this->assertIsInt($year);
        $this->assertNotNull($trim);
        $this->assertIsString($trim);
    }

    private function assertPrice(VehiclePrice $vehiclePrice): void
    {
        $brand = $vehiclePrice->getBrand();
        $model = $vehiclePrice->getModel();
        $year = $vehiclePrice->getYear();
        $trim = $vehiclePrice->getTrim();
        $kilometerGroup = $vehiclePrice->getKilometerGroup();
        $salePrice = $vehiclePrice->getSalePrice();
        $purchasePrice = $vehiclePrice->getPurchasePrice();

        $this->assertNotNull($brand);
        $this->assertIsString($brand);
        $this->assertNotNull($model);
        $this->assertIsString($model);
        $this->assertNotNull($year);
        $this->assertIsInt($year);
        $this->assertNotNull($trim);
        $this->assertIsString($trim);
        $this->assertNotNull($kilometerGroup);
        $this->assertIsString($kilometerGroup);
        $this->assertNotNull($salePrice);
        $this->assertIsInt($salePrice);
        $this->assertNotNull($purchasePrice);
        $this->assertIsInt($purchasePrice);
    }
}
