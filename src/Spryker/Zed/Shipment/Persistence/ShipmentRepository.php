<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence;

use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentPersistenceFactory getFactory()
 */
class ShipmentRepository extends AbstractRepository implements ShipmentRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $shipmentMethodTransfer
     *
     * @return bool
     */
    public function isShipmentMethodUniqueForCarrier(ShipmentMethodTransfer $shipmentMethodTransfer): bool
    {
        $shipmentMethodTransfer->requireName()
            ->requireFkShipmentCarrier();

        return !$this->getFactory()
            ->createShipmentMethodQuery()
            ->filterByName($shipmentMethodTransfer->getName())
            ->filterByIdShipmentMethod($shipmentMethodTransfer->getIdShipmentMethod(), Criteria::NOT_EQUAL)
            ->filterByFkShipmentCarrier($shipmentMethodTransfer->getFkShipmentCarrier())
            ->exists();
    }

    /**
     * @param string $shipmentMethodName
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findShipmentMethodByName(string $shipmentMethodName): ?ShipmentMethodTransfer
    {
        $salesShipmentMethodEntity = $this->queryMethodsWithMethodPricesAndCarrier()
            ->filterByName($shipmentMethodName)
            ->find()
            ->getFirst();

        if ($salesShipmentMethodEntity === null) {
            return null;
        }

        return $this->getFactory()
            ->createShipmentMethodMapper()
            ->mapShipmentMethodEntityToShipmentMethodTransferWithPrices(
                $salesShipmentMethodEntity,
                new ShipmentMethodTransfer()
            );
    }

    /**
     * @module Currency
     *
     * @return \Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery
     */
    protected function queryMethodsWithMethodPricesAndCarrier(): SpyShipmentMethodQuery
    {
        return $this->queryMethods()
            ->joinWithShipmentMethodPrice()
                ->useShipmentMethodPriceQuery()
                    ->joinWithCurrency()
                ->endUse()
            ->leftJoinWithShipmentCarrier();
    }

    /**
     * @return \Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery
     */
    protected function queryMethods(): SpyShipmentMethodQuery
    {
        return $this->getFactory()->createShipmentMethodQuery();
    }
}
