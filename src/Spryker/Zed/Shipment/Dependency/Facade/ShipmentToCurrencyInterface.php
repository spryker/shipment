<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency\Facade;

use Generated\Shared\Transfer\CurrencyTransfer;

interface ShipmentToCurrencyInterface
{
    /**
     * @param string $isoCode
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    public function fromIsoCode(string $isoCode): CurrencyTransfer;

    /**
     * @param int $idCurrency
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    public function getByIdCurrency(int $idCurrency): CurrencyTransfer;
}
