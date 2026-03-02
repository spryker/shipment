<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Dependency;

use Generated\Shared\Transfer\TaxSetCollectionTransfer;

class ShipmentToTaxBridge implements ShipmentToTaxInterface
{
    /**
     * @var \Spryker\Zed\Tax\Business\TaxFacadeInterface
     */
    protected $taxFacade;

    /**
     * @param \Spryker\Zed\Tax\Business\TaxFacadeInterface $taxFacade
     */
    public function __construct($taxFacade)
    {
        $this->taxFacade = $taxFacade;
    }

    public function getDefaultTaxCountryIso2Code(): string
    {
        return $this->taxFacade->getDefaultTaxCountryIso2Code();
    }

    public function getDefaultTaxRate(): float
    {
        return $this->taxFacade->getDefaultTaxRate();
    }

    public function getTaxSets(): TaxSetCollectionTransfer
    {
        return $this->taxFacade->getTaxSets();
    }
}
