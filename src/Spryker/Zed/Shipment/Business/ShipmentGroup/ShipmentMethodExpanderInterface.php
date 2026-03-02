<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentGroup;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;

interface ShipmentMethodExpanderInterface
{
    public function expand(ShipmentMethodTransfer $shipmentMethodTransfer, OrderTransfer $orderTransfer): ShipmentMethodTransfer;
}
