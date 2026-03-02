<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentExpense;

use Generated\Shared\Transfer\CalculableObjectTransfer;

interface MultiShipmentExpenseFilterInterface
{
    public function filterObsoleteShipmentExpenses(CalculableObjectTransfer $calculableObjectTransfer): void;
}
