<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Shipment;

use Generated\Shared\Transfer\ShipmentTransfer;

/**
 * @deprecated Will be removed without replacement.
 */
interface ShipmentReaderInterface
{
    public function findShipmentById(int $idSalesShipment): ?ShipmentTransfer;
}
