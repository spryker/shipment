<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\TaxSetTransfer;
use Orm\Zed\Tax\Persistence\SpyTaxSet;

class ShipmentTaxSetMapper implements ShipmentTaxSetMapperInterface
{
    public function mapTaxSetEntityToTaxSetTransfer(SpyTaxSet $taxSetEntity, TaxSetTransfer $taxSetTransfer): TaxSetTransfer
    {
        $taxSetTransfer->fromArray($taxSetEntity->toArray(), true);

        return $taxSetTransfer;
    }
}
