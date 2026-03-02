<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;

/**
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentPersistenceFactory getFactory()
 */
interface ShipmentEntityManagerInterface
{
    public function saveSalesShipment(
        ShipmentTransfer $shipmentTransfer,
        OrderTransfer $orderTransfer,
        ?ExpenseTransfer $expenseTransfer = null
    ): ShipmentTransfer;

    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Persistence\ShipmentEntityManager::updateFkShipmentForOrderItems()} instead.
     *
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return void
     */
    public function updateFkShipmentForOrderItem(ItemTransfer $itemTransfer, ShipmentTransfer $shipmentTransfer): void;

    public function saveSalesShipmentMethod(ShipmentMethodTransfer $shipmentMethodTransfer): ShipmentMethodTransfer;

    public function updateShipmentMethod(ShipmentMethodTransfer $shipmentMethodTransfer): ShipmentMethodTransfer;

    public function deleteMethodByIdMethod(int $idShipmentMethod): void;

    public function deleteShipmentMethodStoreRelationsByIdShipmentMethod(int $idShipmentMethod): void;

    public function deleteShipmentMethodPricesByIdShipmentMethod(int $idShipmentMethod): void;

    public function saveSalesExpense(ExpenseTransfer $expenseTransfer, OrderTransfer $orderTransfer): ExpenseTransfer;

    public function removeShipmentMethodStoreRelationsForStores(array $idStores, int $idShipmentMethod): void;

    public function addShipmentMethodStoreRelationsForStores(array $idStores, int $idShipmentMethod): void;

    /**
     * @param iterable<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return void
     */
    public function updateFkShipmentForOrderItems(iterable $itemTransfers, ShipmentTransfer $shipmentTransfer): void;

    /**
     * @param iterable<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param int|null $idSalesShipment
     *
     * @return void
     */
    public function updateFkSalesShipmentForSalesOrderItems(iterable $itemTransfers, ?int $idSalesShipment): void;

    public function deleteSalesShipmentsByIdSalesOrder(int $idSalesOrder): void;
}
