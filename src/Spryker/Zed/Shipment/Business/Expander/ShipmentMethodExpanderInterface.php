<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Expander;

use Generated\Shared\Transfer\ShipmentMethodCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;

interface ShipmentMethodExpanderInterface
{
    public function expandShipmentMethodTransfer(ShipmentMethodTransfer $shipmentMethodTransfer): ShipmentMethodTransfer;

    /**
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function expandShipmentMethodTransfers(array $shipmentMethodTransfers): array;

    public function expandShipmentMethodCollectionTransfer(
        ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer
    ): ShipmentMethodCollectionTransfer;

    public function expandShipmentMethodsCollectionTransfer(
        ShipmentMethodsCollectionTransfer $shipmentMethodsCollectionTransfer
    ): ShipmentMethodsCollectionTransfer;
}
