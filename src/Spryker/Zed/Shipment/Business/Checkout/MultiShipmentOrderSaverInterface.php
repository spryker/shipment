<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Checkout;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;

interface MultiShipmentOrderSaverInterface extends ShipmentOrderSaverInterface
{
    public function saveOrderShipmentByShipmentGroup(
        OrderTransfer $orderTransfer,
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): ShipmentGroupTransfer;

    public function saveSalesOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void;
}
