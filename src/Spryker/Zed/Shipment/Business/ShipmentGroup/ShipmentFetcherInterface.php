<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentGroup;

use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentPriceTransfer;
use Generated\Shared\Transfer\StoreTransfer;

interface ShipmentFetcherInterface
{
    public function findActiveShipmentMethodWithPricesAndCarrierById(int $shipmentMethodId): ?ShipmentMethodTransfer;

    public function findMethodPriceByShipmentMethodAndCurrentStoreCurrency(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        StoreTransfer $storeTransfer
    ): ?ShipmentPriceTransfer;
}
