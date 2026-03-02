<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentExpense;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Service\Shipment\ShipmentServiceInterface;

class MultiShipmentExpenseFilter implements MultiShipmentExpenseFilterInterface
{
    /**
     * @var \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    protected $shipmentService;

    /**
     * @var \Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCollectionRemoverInterface
     */
    protected $shipmentExpenseCollectionRemover;

    public function __construct(
        ShipmentServiceInterface $shipmentService,
        ShipmentExpenseCollectionRemoverInterface $shipmentExpenseCollectionRemover
    ) {
        $this->shipmentService = $shipmentService;
        $this->shipmentExpenseCollectionRemover = $shipmentExpenseCollectionRemover;
    }

    public function filterObsoleteShipmentExpenses(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $quoteTransfer = $calculableObjectTransfer->getOriginalQuote();
        if ($quoteTransfer === null) {
            return;
        }

        $shipmentGroupTransfers = $this->shipmentService->groupItemsByShipment($quoteTransfer->getItems());
        $calculableObjectExpenseTransfers = $calculableObjectTransfer->getExpenses();

        foreach ($shipmentGroupTransfers as $shipmentGroupTransfer) {
            $shipmentTransfer = $shipmentGroupTransfer->requireShipment()->getShipment();
            if ($this->isItemShipmentMethodSet($shipmentTransfer)) {
                continue;
            }

            $calculableObjectExpenseTransfers = $this->shipmentExpenseCollectionRemover->removeExpenseByShipmentHash(
                $calculableObjectExpenseTransfers,
                $shipmentGroupTransfer->getHash(),
            );
        }

        $calculableObjectTransfer->setExpenses($calculableObjectExpenseTransfers);
    }

    protected function isItemShipmentMethodSet(ShipmentTransfer $shipmentTransfer): bool
    {
        $shipmentMethodTransfer = $shipmentTransfer->getMethod();

        return $shipmentMethodTransfer !== null && $shipmentMethodTransfer->getIdShipmentMethod() !== null;
    }
}
