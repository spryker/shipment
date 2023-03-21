<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency\Facade;

class ShipmentToPriceFacadeBridge implements ShipmentToPriceFacadeInterface
{
    /**
     * @var \Spryker\Zed\Price\Business\PriceFacadeInterface
     */
    protected $priceFacade;

    /**
     * @param \Spryker\Zed\Price\Business\PriceFacadeInterface $priceFacade
     */
    public function __construct($priceFacade)
    {
        $this->priceFacade = $priceFacade;
    }

    /**
     * @return string
     */
    public function getNetPriceModeIdentifier(): string
    {
        return $this->priceFacade->getNetPriceModeIdentifier();
    }
}
