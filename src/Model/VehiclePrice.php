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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class VehiclePrice
{
    private string $brand;

    private string $model;

    private int $year;

    private string $trim;

    private ?int $salePrice;

    private ?int $purchasePrice;

    private ?MileagePrice $mileagePrice = null;

    /**
     * @phpstan-var Collection<int, AddOnPrice>
     * @var Collection
     */
    private Collection $addOnPrices;

    /**
     * @param string $brand
     * @param string $model
     * @param int $year
     * @param string $trim
     * @param int|null $salePrice
     * @param int|null $purchasePrice
     */
    public function __construct(
        string $brand,
        string $model,
        int $year,
        string $trim,
        ?int $salePrice,
        ?int $purchasePrice
    ) {
        $this->brand = $brand;
        $this->model = $model;
        $this->year = $year;
        $this->trim = $trim;
        $this->salePrice = $salePrice;
        $this->purchasePrice = $purchasePrice;
        $this->addOnPrices = new ArrayCollection();
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

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function getPurchasePrice(): ?int
    {
        return $this->purchasePrice;
    }

    public function getMileagePrice(): ?MileagePrice
    {
        return $this->mileagePrice;
    }

    public function setMileagePrice(MileagePrice $mileagePrice): self
    {
        $this->mileagePrice = $mileagePrice;

        return $this;
    }

    /**
     * @phpstan-return iterable<AddOnPrice>
     * @return iterable
     */
    public function getAddOnPrices(): iterable
    {
        return $this->addOnPrices;
    }

    public function addAddOnPrice(AddOnPrice $addOnPrice): self
    {
        $this->addOnPrices->add($addOnPrice);

        return $this;
    }
}
