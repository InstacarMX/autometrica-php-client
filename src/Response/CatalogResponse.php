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
use Instacar\AutometricaWebserviceClient\Model\Vehicle;
use LogicException;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @phpstan-implements CollectionResponseInterface<Vehicle>
 * @internal
 */
class CatalogResponse implements CollectionResponseInterface
{
    /**
     * @SerializedName("catalogo_lineal")
     * @phpstan-var Collection<int, Vehicle>
     * @var Collection|Vehicle[]
     */
    #[SerializedName('catalogo_lineal')]
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    /**
     * @phpstan-return iterable<Vehicle>
     * @return iterable|Vehicle[]
     */
    public function getData(): iterable
    {
        return $this->data;
    }

    /**
     * @param Vehicle $data
     * @return self
     */
    public function addData(Vehicle $data): self
    {
        $this->data->add($data);

        return $this;
    }

    /**
     * @param Vehicle $datum
     * @return self
     */
    public function addDatum(Vehicle $datum): self
    {
        $this->data->add($datum);

        return $this;
    }

    /**
     * @param Vehicle $data
     * @return self
     */
    public function removeData(Vehicle $data): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }

    /**
     * @param Vehicle $datum
     * @return self
     */
    public function removeDatum(Vehicle $datum): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }
}
