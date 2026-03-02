<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Shipment;

use Spryker\Service\Kernel\AbstractServiceFactory;
use Spryker\Service\Shipment\Dependency\Service\ShipmentToCustomerServiceInterface;
use Spryker\Service\Shipment\Dependency\Service\ShipmentToUtilEncodingServiceInterface;
use Spryker\Service\Shipment\Items\ItemsGrouper;
use Spryker\Service\Shipment\Items\ItemsGrouperInterface;
use Spryker\Service\Shipment\ShipmentHash\ShipmentHashGenerator;
use Spryker\Service\Shipment\ShipmentHash\ShipmentHashGeneratorInterface;

/**
 * @method \Spryker\Service\Shipment\ShipmentConfig getConfig()
 */
class ShipmentServiceFactory extends AbstractServiceFactory
{
    public function createItemsGrouper(): ItemsGrouperInterface
    {
        return new ItemsGrouper($this->createShipmentHashGenerator());
    }

    public function createShipmentHashGenerator(): ShipmentHashGeneratorInterface
    {
        return new ShipmentHashGenerator(
            $this->getCustomerService(),
            $this->getConfig(),
            $this->getUtilEncodingService(),
        );
    }

    public function getCustomerService(): ShipmentToCustomerServiceInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SERVICE_CUSTOMER);
    }

    public function getUtilEncodingService(): ShipmentToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
