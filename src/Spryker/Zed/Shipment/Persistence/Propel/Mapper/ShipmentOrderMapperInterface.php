<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesShipment;

interface ShipmentOrderMapperInterface
{
    public function mapSalesOrderEntityToOrderTransfer(
        SpySalesOrder $salesOrderEntity,
        OrderTransfer $orderTransfer
    ): OrderTransfer;

    public function mapOrderTransferToShipmentEntity(
        OrderTransfer $orderTransfer,
        SpySalesShipment $salesShipmentEntity
    ): SpySalesShipment;
}
