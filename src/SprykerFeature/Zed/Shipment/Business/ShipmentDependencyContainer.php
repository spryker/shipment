<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Shipment\Business;

use SprykerEngine\Zed\Kernel\Business\AbstractBusinessDependencyContainer;
use SprykerFeature\Zed\Shipment\Business\Model\Carrier;
use SprykerFeature\Zed\Shipment\Business\Model\Method;
use SprykerFeature\Zed\Shipment\Persistence\ShipmentQueryContainerInterface;
use SprykerFeature\Zed\Shipment\ShipmentDependencyProvider;

/**
 * @method ShipmentQueryContainerInterface getQueryContainer()
 */
class ShipmentDependencyContainer extends AbstractBusinessDependencyContainer
{

    /**
     * @return Carrier
     */
    public function createCarrier()
    {
        return new Carrier();
    }

    /**
     * @return Method
     */
    public function createMethod()
    {
        return new Method(
                $this->getQueryContainer(),
                $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS)
            );
    }

}
