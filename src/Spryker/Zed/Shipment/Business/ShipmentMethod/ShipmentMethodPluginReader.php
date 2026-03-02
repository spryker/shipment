<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentMethod;

use Generated\Shared\Transfer\ShipmentMethodPluginCollectionTransfer;
use Spryker\Zed\Shipment\ShipmentDependencyProvider;

class ShipmentMethodPluginReader implements ShipmentMethodPluginReaderInterface
{
    /**
     * @var array
     */
    protected $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function getShipmentMethodPlugins(): ShipmentMethodPluginCollectionTransfer
    {
        $shipmentMethodPluginCollectionTransfer = new ShipmentMethodPluginCollectionTransfer();
        $shipmentMethodPluginCollectionTransfer = $this->addAvailabilityPlugins($shipmentMethodPluginCollectionTransfer);
        $shipmentMethodPluginCollectionTransfer = $this->addPricePlugins($shipmentMethodPluginCollectionTransfer);
        $shipmentMethodPluginCollectionTransfer = $this->addDeliveryTimePlugins($shipmentMethodPluginCollectionTransfer);

        return $shipmentMethodPluginCollectionTransfer;
    }

    protected function addAvailabilityPlugins(
        ShipmentMethodPluginCollectionTransfer $shipmentMethodPluginCollectionTransfer
    ): ShipmentMethodPluginCollectionTransfer {
        foreach ($this->plugins[ShipmentDependencyProvider::AVAILABILITY_PLUGINS] as $name => $plugin) {
            $shipmentMethodPluginCollectionTransfer->addAvailabilityPluginOption($name);
        }

        return $shipmentMethodPluginCollectionTransfer;
    }

    protected function addPricePlugins(ShipmentMethodPluginCollectionTransfer $shipmentMethodPluginCollectionTransfer): ShipmentMethodPluginCollectionTransfer
    {
        foreach ($this->plugins[ShipmentDependencyProvider::PRICE_PLUGINS] as $name => $plugin) {
            $shipmentMethodPluginCollectionTransfer->addPricePluginOption($name);
        }

        return $shipmentMethodPluginCollectionTransfer;
    }

    protected function addDeliveryTimePlugins(
        ShipmentMethodPluginCollectionTransfer $shipmentMethodPluginCollectionTransfer
    ): ShipmentMethodPluginCollectionTransfer {
        foreach ($this->plugins[ShipmentDependencyProvider::DELIVERY_TIME_PLUGINS] as $name => $plugin) {
            $shipmentMethodPluginCollectionTransfer->addDeliveryTimePluginOption($name);
        }

        return $shipmentMethodPluginCollectionTransfer;
    }
}
