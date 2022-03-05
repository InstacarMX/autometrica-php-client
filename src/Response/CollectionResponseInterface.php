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

/**
 * @phpstan-template T
 * @method self addData($data) Add the data to the internal collection
 * @method self addDatum($datum) addData() compatible method for Symfony 4.4
 * @method self removeData($data) Remove the data to the internal collection
 * @method self removeDatum($datum) removeData() compatible method for Symfony 4.4
 * @internal
 */
interface CollectionResponseInterface
{
    /**
     * @phpstan-return iterable<T>
     * @return iterable
     */
    public function getData(): iterable;
}
