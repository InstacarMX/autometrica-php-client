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

/**
 * @internal
 */
class AutometricaPrice
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
    private ?string $mileageGroup;

    /**
     * @SerializedName("sale")
     */
    private ?int $salePrice;

    /**
     * @SerializedName("purchase")
     */
    private ?int $purchasePrice;

    /**
     * @param string $brand
     * @param string $model
     * @param int $year
     * @param string $trim
     * @param string|int $mileageGroup
     * @param string|int $salePrice
     * @param string|int $purchasePrice
     */
    public function __construct(
        string $brand,
        string $model,
        int $year,
        string $trim,
        $mileageGroup,
        $salePrice,
        $purchasePrice
    ) {
        $this->brand = $brand;
        $this->model = $model;
        $this->year = $year;
        $this->trim = $trim;
        // When the mileage group is unknown, Autométrica return int(0)
        $this->mileageGroup = is_string($mileageGroup) ? $mileageGroup : null;
        // When a addOn price is unknown, Autométrica return string("")
        $this->salePrice = is_numeric($salePrice) && $salePrice !== 0 ? $salePrice : null;
        $this->purchasePrice = is_numeric($purchasePrice) && $purchasePrice !== 0 ? $purchasePrice : null;
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

    public function getMileageGroup(): ?string
    {
        return $this->mileageGroup;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function getPurchasePrice(): ?int
    {
        return $this->purchasePrice;
    }
}
