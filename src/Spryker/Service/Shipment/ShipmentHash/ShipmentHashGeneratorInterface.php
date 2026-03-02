<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Shipment\ShipmentHash;

use Generated\Shared\Transfer\ShipmentTransfer;

interface ShipmentHashGeneratorInterface
{
    public function getShipmentHashKey(ShipmentTransfer $shipmentTransfer): string;
}
