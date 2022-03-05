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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Instacar\AutometricaWebserviceClient\Response\CollectionResponseInterface;
use LogicException;

/**
 * @phpstan-implements CollectionResponseInterface<MockedItem>
 */
class MockedCollection implements CollectionResponseInterface
{
    /**
     * @phpstan-var Collection<int, MockedItem> $data
     */
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    public function getData(): iterable
    {
        return $this->data;
    }

    public function addData(MockedItem $data): self
    {
        $this->data->add($data);

        return $this;
    }

    public function addDatum(MockedItem $datum): self
    {
        $this->data->add($datum);

        return $this;
    }

    public function removeData(MockedItem $data): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }

    public function removeDatum(MockedItem $datum): self
    {
        throw new LogicException("This is a stub method, it should not be used");
    }
}
