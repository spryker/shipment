<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Shipment;

use Spryker\Service\Kernel\AbstractBundleConfig;

class ShipmentConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @return array
     */
    public function getShipmentHashFields(): array
    {
        return [];
    }
}