<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Mapper;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;

interface ShipmentMapperInterface
{
    public function mapShipmentMethodTransferToExpenseTransfer(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        ExpenseTransfer $expenseTransfer
    ): ExpenseTransfer;

    public function mapShipmentMethodTransferToShipmentExpenseTransfer(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        ExpenseTransfer $shipmentExpenseTransfer
    ): ExpenseTransfer;
}
