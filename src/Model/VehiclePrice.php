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

namespace Instacar\AutometricaWebserviceClient\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;

class VehiclePrice
{
    private string $brand;

    /**
     * @SerializedName("subbrand")
     */
    private string $model;

    private int $year;

    /**
     * @SerializedName("version")
     */
    private string $trim;

    /**
     * @SerializedName("km_group")
     */
    private ?string $kilometerGroup;

    /**
     * @SerializedName("sale")
     */
    private int $salePrice;

    /**
     * @SerializedName("purchase")
     */
    private int $purchasePrice;

    /**
     * @param string $brand
     * @param string $model
     * @param int $year
     * @param string $trim
     * @param string|int $kilometerGroup
     * @param int $salePrice
     * @param int $purchasePrice
     */
    public function __construct(
        string $brand,
        string $model,
        int $year,
        string $trim,
        $kilometerGroup,
        int $salePrice,
        int $purchasePrice
    ) {
        $this->brand = $brand;
        $this->model = $model;
        $this->year = $year;
        $this->trim = $trim;
        $this->kilometerGroup = is_string($kilometerGroup) ? $kilometerGroup : null;
        $this->salePrice = $salePrice;
        $this->purchasePrice = $purchasePrice;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getTrim(): string
    {
        return $this->trim;
    }

    public function getKilometerGroup(): ?string
    {
        return $this->kilometerGroup;
    }

    public function getSalePrice(): int
    {
        return $this->salePrice;
    }

    public function getPurchasePrice(): int
    {
        return $this->purchasePrice;
    }
}
