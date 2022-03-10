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
use Instacar\AutometricaWebserviceClient\Model\AddOnPrice;
use Instacar\AutometricaWebserviceClient\Model\AutometricaPrice;
use Instacar\AutometricaWebserviceClient\Model\MileagePrice;
use Instacar\AutometricaWebserviceClient\Model\VehiclePrice;
use LogicException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @phpstan-implements CollectionResponseInterface<AutometricaPrice>
 * @phpstan-implements ItemResponseInterface<VehiclePrice>
 * @internal
 */
class VehiclePricesResponse implements CollectionResponseInterface, ItemResponseInterface
{
    /**
     * @SerializedName("lineal")
     * @phpstan-var Collection<int, AutometricaPrice>
     * @var Collection|AutometricaPrice[]
     */
    private Collection $data;

    /**
     * @Groups({"ignore"})
     */
    private ?VehiclePrice $item = null;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    public function getItem(): VehiclePrice
    {
        if ($this->item === null) {
            $vehiclePrice = $this->data->first();
            $this->item = new VehiclePrice(
                $vehiclePrice->getBrand(),
                $vehiclePrice->getModel(),
                $vehiclePrice->getYear(),
                $vehiclePrice->getTrim(),
                $vehiclePrice->getSalePrice(),
                $vehiclePrice->getPurchasePrice(),
            );

            while ($this->data->next()) {
                $price = $this->data->current();

                if ($price->getTrim() !== 'Valor kilometraje') {
                    $this->item->addAddOnPrice(new AddOnPrice(
                        $price->getTrim(),
                        $price->getSalePrice(),
                        $price->getPurchasePrice(),
                    ));
                } else {
                    $this->item->setMileagePrice(new MileagePrice(
                        $price->getKilometerGroup(),
                        $price->getSalePrice(),
                        $price->getPurchasePrice(),
                    ));
                }
            }
        }

        return $this->item;
    }

    /**
     * @phpstan-return iterable<AutometricaPrice>
     * @return iterable|AutometricaPrice[]
     */
    public function getData(): iterable
    {
        return $this->data;
    }

    /**
     * @param AutometricaPrice $data
     * @return self
     */
    public function addData(AutometricaPrice $data): self
    {
        $this->data->add($data);

        return $this;
    }

    /**
     * @param AutometricaPrice $datum
     * @return self
     */
    public function addDatum(AutometricaPrice $datum): self
    {
        return $this->addData($datum);
    }

    /**
     * @param AutometricaPrice $data
     * @return self
     */
    public function removeData(AutometricaPrice $data): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }

    /**
     * @param AutometricaPrice $datum
     * @return self
     */
    public function removeDatum(AutometricaPrice $datum): self
    {
        return $this->removeData($datum);
    }
}
