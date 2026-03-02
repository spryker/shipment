<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Expander;

use ArrayObject;
use Generated\Shared\Transfer\ShipmentMethodCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodsTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;

class ShipmentMethodExpander implements ShipmentMethodExpanderInterface
{
    /**
     * @var array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodCollectionExpanderPluginInterface>
     */
    protected array $shipmentMethodCollectionExpanderPlugins;

    /**
     * @param array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodCollectionExpanderPluginInterface> $shipmentMethodCollectionExpanderPlugins
     */
    public function __construct(array $shipmentMethodCollectionExpanderPlugins)
    {
        $this->shipmentMethodCollectionExpanderPlugins = $shipmentMethodCollectionExpanderPlugins;
    }

    public function expandShipmentMethodTransfer(ShipmentMethodTransfer $shipmentMethodTransfer): ShipmentMethodTransfer
    {
        $shipmentMethodTransfers = $this->executeShipmentMethodCollectionExpanderPluginsForShipmentMethodTransfers([$shipmentMethodTransfer]);

        return $shipmentMethodTransfers[0];
    }

    /**
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function expandShipmentMethodTransfers(array $shipmentMethodTransfers): array
    {
        return $this->executeShipmentMethodCollectionExpanderPluginsForShipmentMethodTransfers($shipmentMethodTransfers);
    }

    public function expandShipmentMethodCollectionTransfer(ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer): ShipmentMethodCollectionTransfer
    {
        return $this->executeShipmentMethodCollectionExpanderPlugins($shipmentMethodCollectionTransfer);
    }

    public function expandShipmentMethodsCollectionTransfer(
        ShipmentMethodsCollectionTransfer $shipmentMethodsCollectionTransfer
    ): ShipmentMethodsCollectionTransfer {
        $shipmentMethodTransfers = $this->extractShipmentMethodTransfersFromShipmentMethodsCollectionTransfer($shipmentMethodsCollectionTransfer);
        $shipmentMethodTransfers = $this->executeShipmentMethodCollectionExpanderPluginsForShipmentMethodTransfers($shipmentMethodTransfers);
        $indexedShipmentMethodTransfers = $this->getShipmentMethodTransfersIndexedByShipmentMethodIds($shipmentMethodTransfers);

        foreach ($shipmentMethodsCollectionTransfer->getShipmentMethods() as $shipmentMethodsTransfer) {
            $this->addExpandedShipmentMethodTransfersToShipmentMethodsTransfer($shipmentMethodsTransfer, $indexedShipmentMethodTransfers);
        }

        return $shipmentMethodsCollectionTransfer;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    protected function executeShipmentMethodCollectionExpanderPluginsForShipmentMethodTransfers(array $shipmentMethodTransfers): array
    {
        $shipmentMethodCollectionTransfer = (new ShipmentMethodCollectionTransfer())
            ->setShipmentMethods((new ArrayObject($shipmentMethodTransfers)));
        $shipmentMethodCollectionTransfer = $this->executeShipmentMethodCollectionExpanderPlugins($shipmentMethodCollectionTransfer);

        return $shipmentMethodCollectionTransfer->getShipmentMethods()->getArrayCopy();
    }

    protected function executeShipmentMethodCollectionExpanderPlugins(
        ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer
    ): ShipmentMethodCollectionTransfer {
        foreach ($this->shipmentMethodCollectionExpanderPlugins as $shipmentMethodCollectionExpanderPlugin) {
            $shipmentMethodCollectionTransfer = $shipmentMethodCollectionExpanderPlugin->expand($shipmentMethodCollectionTransfer);
        }

        return $shipmentMethodCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer $shipmentMethodsCollectionTransfer
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    protected function extractShipmentMethodTransfersFromShipmentMethodsCollectionTransfer(
        ShipmentMethodsCollectionTransfer $shipmentMethodsCollectionTransfer
    ): array {
        $shipmentMethodTransfers = [];
        foreach ($shipmentMethodsCollectionTransfer->getShipmentMethods() as $shipmentMethodsTransfer) {
            foreach ($shipmentMethodsTransfer->getMethods() as $shipmentMethodTransfer) {
                $shipmentMethodTransfers[] = $shipmentMethodTransfer;
            }
        }

        return $shipmentMethodTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    protected function getShipmentMethodTransfersIndexedByShipmentMethodIds(array $shipmentMethodTransfers): array
    {
        $indexedShipmentMethodTransfers = [];
        foreach ($shipmentMethodTransfers as $shipmentMethodTransfer) {
            $indexedShipmentMethodTransfers[$shipmentMethodTransfer->getIdShipmentMethodOrFail()] = $shipmentMethodTransfer;
        }

        return $indexedShipmentMethodTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodsTransfer $shipmentMethodsTransfer
     * @param array<int, \Generated\Shared\Transfer\ShipmentMethodTransfer> $indexedShipmentMethodTransfers
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodsTransfer
     */
    protected function addExpandedShipmentMethodTransfersToShipmentMethodsTransfer(
        ShipmentMethodsTransfer $shipmentMethodsTransfer,
        array $indexedShipmentMethodTransfers
    ): ShipmentMethodsTransfer {
        foreach ($shipmentMethodsTransfer->getMethods() as $index => $shipmentMethodTransfer) {
            if (!isset($indexedShipmentMethodTransfers[$shipmentMethodTransfer->getIdShipmentMethodOrFail()])) {
                continue;
            }

            $shipmentMethodsTransfer->offsetSet($index, $indexedShipmentMethodTransfers[$shipmentMethodTransfer->getIdShipmentMethodOrFail()]);
        }

        return $shipmentMethodsTransfer;
    }
}
