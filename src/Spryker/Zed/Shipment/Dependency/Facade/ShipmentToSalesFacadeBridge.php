<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency\Facade;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderFilterTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\SalesExpenseCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\SalesExpenseCollectionResponseTransfer;

class ShipmentToSalesFacadeBridge implements ShipmentToSalesFacadeInterface
{
    /**
     * @var \Spryker\Zed\Sales\Business\SalesFacadeInterface
     */
    protected $salesFacade;

    /**
     * @param \Spryker\Zed\Sales\Business\SalesFacadeInterface $salesFacade
     */
    public function __construct($salesFacade)
    {
        $this->salesFacade = $salesFacade;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeBridge::getOrder()} instead.
     *
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function getOrderByIdSalesOrder(int $idSalesOrder): OrderTransfer
    {
        return $this->salesFacade->getOrderByIdSalesOrder($idSalesOrder);
    }

    public function getOrder(OrderFilterTransfer $orderFilterTransfer): OrderTransfer
    {
        return $this->salesFacade->getOrder($orderFilterTransfer);
    }

    public function createOrderAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $this->salesFacade->createOrderAddress($addressTransfer);
    }

    public function createSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        return $this->salesFacade->createSalesExpense($expenseTransfer);
    }

    public function updateSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        return $this->salesFacade->updateSalesExpense($expenseTransfer);
    }

    public function expandWithCustomerOrSalesAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $this->salesFacade->expandWithCustomerOrSalesAddress($addressTransfer);
    }

    public function deleteSalesExpenseCollection(
        SalesExpenseCollectionDeleteCriteriaTransfer $salesExpenseCollectionDeleteCriteriaTransfer
    ): SalesExpenseCollectionResponseTransfer {
        return $this->salesFacade->deleteSalesExpenseCollection($salesExpenseCollectionDeleteCriteriaTransfer);
    }
}
