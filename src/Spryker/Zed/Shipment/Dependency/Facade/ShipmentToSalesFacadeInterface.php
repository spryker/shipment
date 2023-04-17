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

    /**
     * @param \Generated\Shared\Transfer\OrderFilterTransfer $orderFilterTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function getOrder(OrderFilterTransfer $orderFilterTransfer): OrderTransfer;

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    public function createOrderAddress(AddressTransfer $addressTransfer): AddressTransfer;

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    public function createSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    public function updateSalesExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer;

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    public function expandWithCustomerOrSalesAddress(AddressTransfer $addressTransfer): AddressTransfer;
}
