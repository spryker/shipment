<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\ItemTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;

class ShipmentSalesOrderItemMapper implements ShipmentSalesOrderItemMapperInterface
{
    public function mapSalesOrderItemEntityToItemTransfer(
        SpySalesOrderItem $spySalesOrderItemEntity,
        ItemTransfer $itemTransfer
    ): ItemTransfer {
        return $itemTransfer->fromArray($spySalesOrderItemEntity->toArray(), true);
    }
}
