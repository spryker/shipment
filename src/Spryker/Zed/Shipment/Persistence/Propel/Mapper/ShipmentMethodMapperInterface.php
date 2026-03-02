<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\ShipmentMethodCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentPriceTransfer;
use Orm\Zed\Currency\Persistence\SpyCurrency;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethod;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPrice;
use Propel\Runtime\Collection\Collection;

interface ShipmentMethodMapperInterface
{
    public function mapShipmentMethodTransferToShipmentMethodEntity(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        SpyShipmentMethod $salesShipmentMethodEntity
    ): SpyShipmentMethod;

    public function mapShipmentMethodEntityToShipmentMethodTransfer(
        SpyShipmentMethod $salesShipmentMethodEntity,
        ShipmentMethodTransfer $shipmentMethodTransfer
    ): ShipmentMethodTransfer;

    public function mapShipmentMethodEntityToShipmentMethodTransferWithPrices(
        SpyShipmentMethod $salesShipmentMethodEntity,
        ShipmentMethodTransfer $shipmentMethodTransfer
    ): ShipmentMethodTransfer;

    public function mapCurrencyEntityToCurrencyTransfer(
        SpyCurrency $currencyEntity,
        CurrencyTransfer $currencyTransfer
    ): CurrencyTransfer;

    public function mapShipmentMethodPriceEntityToShipmentPriceTransfer(
        SpyShipmentMethodPrice $shipmentMethodPrice,
        ShipmentPriceTransfer $shipmentPriceTransfer
    ): ShipmentPriceTransfer;

    public function mapShipmentMethodPriceEntityToMoneyValueTransfer(
        SpyShipmentMethodPrice $shipmentMethodPriceEntity,
        MoneyValueTransfer $moneyValueTransfer
    ): MoneyValueTransfer;

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Shipment\Persistence\SpyShipmentMethod>|iterable $shipmentMethodsEntities
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function mapShipmentMethodEntitiesToShipmentMethodTransfers(
        iterable $shipmentMethodsEntities,
        array $shipmentMethodTransfers
    ): array;

    public function mapShipmentMethodEntitiesToShipmentMethodCollectionTransfer(
        Collection $shipmentMethodEntities,
        ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer
    ): ShipmentMethodCollectionTransfer;
}
