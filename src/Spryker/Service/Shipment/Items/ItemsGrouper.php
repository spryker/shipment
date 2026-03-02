<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Shipment\Items;

use ArrayObject;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Spryker\Service\Shipment\ShipmentHash\ShipmentHashGeneratorInterface;

class ItemsGrouper implements ItemsGrouperInterface
{
    /**
     * @var string
     */
    protected const SHIPMENT_TRANSFER_KEY_PATTERN = '%s-%s-%s';

    /**
     * @var \Spryker\Service\Shipment\ShipmentHash\ShipmentHashGeneratorInterface
     */
    protected $shipmentHashGenerator;

    public function __construct(ShipmentHashGeneratorInterface $shipmentHashGenerator)
    {
        $this->shipmentHashGenerator = $shipmentHashGenerator;
    }

    /**
     * @param iterable<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ShipmentGroupTransfer>
     */
    public function groupByShipment(iterable $itemTransfers): ArrayObject
    {
        /** @var array<\Generated\Shared\Transfer\ShipmentGroupTransfer> $shipmentGroupTransfers */
        $shipmentGroupTransfers = [];

        foreach ($itemTransfers as $itemTransfer) {
            $this->assertRequiredShipment($itemTransfer);

            $shipmentHashKey = $this->shipmentHashGenerator->getShipmentHashKey($itemTransfer->getShipment());
            if (!isset($shipmentGroupTransfers[$shipmentHashKey])) {
                $shipmentGroupTransfers[$shipmentHashKey] = $this->createShipmentGroupTransferWithListedItems(
                    $itemTransfer,
                    $shipmentHashKey,
                );
            }

            $shipmentGroupTransfers[$shipmentHashKey]->addItem($itemTransfer);
        }

        return new ArrayObject(array_values($shipmentGroupTransfers));
    }

    protected function assertRequiredShipment(ItemTransfer $itemTransfer): void
    {
        $itemTransfer->requireShipment();
    }

    protected function createShipmentGroupTransferWithListedItems(
        ItemTransfer $itemTransfer,
        string $shipmentHashKey
    ): ShipmentGroupTransfer {
        return (new ShipmentGroupTransfer())
            ->setShipment($itemTransfer->getShipment())
            ->setHash($shipmentHashKey);
    }
}
