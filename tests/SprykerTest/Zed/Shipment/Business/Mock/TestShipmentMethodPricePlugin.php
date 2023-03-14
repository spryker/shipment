<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Shipment\Business\Mock;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodPricePluginInterface;

class TestShipmentMethodPricePlugin implements ShipmentMethodPricePluginInterface
{
    /**
     * @var string
     */
    public const TEST_PRICE_PLUGIN_DEPENDENCY_KEY = 'TEST_PRICE_PLUGIN';

    /**
     * @var int
     */
    public const TEST_SHIPMENT_METHOD_PRICE_PLUGIN_PRICE = 100;

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return int
     */
    public function getPrice(ShipmentGroupTransfer $shipmentGroupTransfer, QuoteTransfer $quoteTransfer): int
    {
        return static::TEST_SHIPMENT_METHOD_PRICE_PLUGIN_PRICE;
    }
}
