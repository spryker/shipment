<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency;

use Generated\Shared\Transfer\TaxSetCollectionTransfer;

interface ShipmentToTaxInterface
{
    public function getDefaultTaxCountryIso2Code(): string;

    public function getDefaultTaxRate(): float;

    public function getTaxSets(): TaxSetCollectionTransfer;
}
