<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency\Facade;

use Generated\Shared\Transfer\CurrencyTransfer;

interface ShipmentToCurrencyInterface
{
    public function fromIsoCode(string $isoCode): CurrencyTransfer;

    public function getByIdCurrency(int $idCurrency): CurrencyTransfer;
}
