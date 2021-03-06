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

class AddOnPrice
{
    private string $name;

    private int $salePrice;

    private ?int $purchasePrice;

    public function __construct(string $name, int $salePrice, ?int $purchasePrice)
    {
        $this->name = $name;
        $this->salePrice = $salePrice;
        $this->purchasePrice = $purchasePrice;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSalePrice(): int
    {
        return $this->salePrice;
    }

    public function getPurchasePrice(): ?int
    {
        return $this->purchasePrice;
    }
}
