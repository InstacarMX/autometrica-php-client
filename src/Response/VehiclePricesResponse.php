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

namespace Instacar\AutometricaWebserviceClient\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use LogicException;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @phpstan-implements CollectionResponseInterface<VehiclePrice>
 * @internal
 */
class VehiclePricesResponse implements CollectionResponseInterface
{
    /**
     * @SerializedName("lineal")
     * @phpstan-var Collection<int, VehiclePrice>
     * @var Collection|VehiclePrice[]
     */
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    /**
     * @phpstan-return iterable<VehiclePrice>
     * @return iterable|VehiclePrice[]
     */
    public function getData(): iterable
    {
        return $this->data;
    }

    /**
     * @param VehiclePrice $data
     * @return self
     */
    public function addData(VehiclePrice $data): self
    {
        $this->data->add($data);

        return $this;
    }

    /**
     * @param VehiclePrice $datum
     * @return self
     */
    public function addDatum(VehiclePrice $datum): self
    {
        $this->data->add($datum);

        return $this;
    }

    /**
     * @param VehiclePrice $data
     * @return self
     */
    public function removeData(VehiclePrice $data): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }

    /**
     * @param VehiclePrice $datum
     * @return self
     */
    public function removeDatum(VehiclePrice $datum): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }
}
