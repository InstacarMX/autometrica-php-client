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
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use Instacar\AutometricaWebserviceClient\Response\CatalogResponse;
use Instacar\AutometricaWebserviceClient\Response\VehiclePricesResponse;
use LogicException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class AutometricaClient
{
    private WebserviceHttpClient $webserviceClient;

    public function __construct(ClientInterface $client, string $username, string $password)
    {
        $this->webserviceClient = new WebserviceHttpClient($client, $username, $password);
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
        return $this->webserviceClient->requestCollection(CatalogResponse::class, 'catalogo.php');
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
        return $this->webserviceClient->requestCollection(VehiclePricesResponse::class, 'lineal.php', 'POST', [
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
}
