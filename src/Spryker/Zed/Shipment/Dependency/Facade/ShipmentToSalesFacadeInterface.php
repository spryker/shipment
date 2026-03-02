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

interface ShipmentToSalesFacadeInterface
{
    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface::getOrder()} instead.
     *
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function getOrderByIdSalesOrder(int $idSalesOrder): OrderTransfer;

    public function getOrder(OrderFilterTransfer $orderFilterTransfer): OrderTransfer;

    public function createOrderAddress(AddressTransfer $addressTransfer): AddressTransfer;

    public function createSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    public function updateSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    public function expandWithCustomerOrSalesAddress(AddressTransfer $addressTransfer): AddressTransfer;

    public function deleteSalesExpenseCollection(
        SalesExpenseCollectionDeleteCriteriaTransfer $salesExpenseCollectionDeleteCriteriaTransfer
    ): SalesExpenseCollectionResponseTransfer;
}
