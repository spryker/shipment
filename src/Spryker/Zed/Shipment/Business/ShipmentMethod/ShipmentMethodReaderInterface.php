<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentMethod;

use Generated\Shared\Transfer\ShipmentMethodCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodCriteriaTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;

interface ShipmentMethodReaderInterface
{
    public function findShipmentMethodById(int $idShipmentMethod): ?ShipmentMethodTransfer;

    public function findShipmentMethodByName(string $shipmentMethodName): ?ShipmentMethodTransfer;

    public function findShipmentMethodByKey(string $shipmentMethodKey): ?ShipmentMethodTransfer;

    /**
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function getActiveShipmentMethods(): array;

    public function getShipmentMethodCollection(
        ShipmentMethodCriteriaTransfer $shipmentMethodCriteriaTransfer
    ): ShipmentMethodCollectionTransfer;
}
