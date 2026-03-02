<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\ShipmentMethodCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentPriceTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Currency\Persistence\SpyCurrency;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethod;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPrice;
use Propel\Runtime\Collection\Collection;

class ShipmentMethodMapper implements ShipmentMethodMapperInterface
{
    /**
     * @var \Spryker\Zed\Shipment\Persistence\Propel\Mapper\StoreRelationMapper
     */
    protected $storeRelationMapper;

    public function __construct(StoreRelationMapper $storeRelationMapper)
    {
        $this->storeRelationMapper = $storeRelationMapper;
    }

    public function mapShipmentMethodTransferToShipmentMethodEntity(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        SpyShipmentMethod $salesShipmentMethodEntity
    ): SpyShipmentMethod {
        $salesShipmentMethodEntity->fromArray($shipmentMethodTransfer->modifiedToArray());

        return $salesShipmentMethodEntity;
    }

    public function mapShipmentMethodEntityToShipmentMethodTransfer(
        SpyShipmentMethod $salesShipmentMethodEntity,
        ShipmentMethodTransfer $shipmentMethodTransfer
    ): ShipmentMethodTransfer {
        $shipmentMethodTransfer = $shipmentMethodTransfer->fromArray($salesShipmentMethodEntity->toArray(), true);

        return $shipmentMethodTransfer;
    }

    public function mapShipmentMethodEntityToShipmentMethodTransferWithPrices(
        SpyShipmentMethod $salesShipmentMethodEntity,
        ShipmentMethodTransfer $shipmentMethodTransfer
    ): ShipmentMethodTransfer {
        $shipmentMethodTransfer = $shipmentMethodTransfer->fromArray($salesShipmentMethodEntity->toArray(), true);
        $shipmentMethodTransfer->setCarrierName($salesShipmentMethodEntity->getShipmentCarrier()->getName());
        $shipmentMethodTransfer->setPrices($this->getPriceCollection($salesShipmentMethodEntity));
        $storeRelationTransfer = new StoreRelationTransfer();
        $storeRelationTransfer->setIdEntity($salesShipmentMethodEntity->getIdShipmentMethod());
        $shipmentMethodTransfer->setStoreRelation(
            $this->storeRelationMapper->mapShipmentMethodStoreEntitiesToStoreRelationTransfer(
                $salesShipmentMethodEntity->getShipmentMethodStores(),
                $storeRelationTransfer,
            ),
        );

        return $shipmentMethodTransfer;
    }

    public function mapCurrencyEntityToCurrencyTransfer(
        SpyCurrency $currencyEntity,
        CurrencyTransfer $currencyTransfer
    ): CurrencyTransfer {
        return $currencyTransfer->fromArray($currencyEntity->toArray(), true);
    }

    public function mapShipmentMethodPriceEntityToShipmentPriceTransfer(
        SpyShipmentMethodPrice $shipmentMethodPrice,
        ShipmentPriceTransfer $shipmentPriceTransfer
    ): ShipmentPriceTransfer {
        return $shipmentPriceTransfer->fromArray($shipmentMethodPrice->toArray(), true);
    }

    public function mapShipmentMethodPriceEntityToMoneyValueTransfer(
        SpyShipmentMethodPrice $shipmentMethodPriceEntity,
        MoneyValueTransfer $moneyValueTransfer
    ): MoneyValueTransfer {
        $moneyValueTransfer = $moneyValueTransfer->fromArray($shipmentMethodPriceEntity->toArray(), true);
        $moneyValueTransfer
            ->setIdEntity($shipmentMethodPriceEntity->getIdShipmentMethodPrice())
            ->setNetAmount($shipmentMethodPriceEntity->getDefaultNetPrice())
            ->setGrossAmount($shipmentMethodPriceEntity->getDefaultGrossPrice());

        $currencyTransfer = $this->mapCurrencyEntityToCurrencyTransfer(
            $shipmentMethodPriceEntity->getCurrency(),
            new CurrencyTransfer(),
        );
        $moneyValueTransfer->setCurrency($currencyTransfer);

        $storeTransfer = $this->storeRelationMapper->mapStoreEntityToStoreTransfer(
            $shipmentMethodPriceEntity->getStore(),
            new StoreTransfer(),
        );
        $moneyValueTransfer->setStore($storeTransfer);

        return $moneyValueTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Shipment\Persistence\SpyShipmentMethod>|iterable $shipmentMethodsEntities
     * @param array<\Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function mapShipmentMethodEntitiesToShipmentMethodTransfers(
        iterable $shipmentMethodsEntities,
        array $shipmentMethodTransfers
    ): array {
        foreach ($shipmentMethodsEntities as $salesShipmentMethodEntity) {
            $shipmentMethodTransfers[] = $this->mapShipmentMethodEntityToShipmentMethodTransfer(
                $salesShipmentMethodEntity,
                new ShipmentMethodTransfer(),
            );
        }

        return $shipmentMethodTransfers;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Shipment\Persistence\SpyShipmentMethod> $shipmentMethodEntities
     * @param \Generated\Shared\Transfer\ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodCollectionTransfer
     */
    public function mapShipmentMethodEntitiesToShipmentMethodCollectionTransfer(
        Collection $shipmentMethodEntities,
        ShipmentMethodCollectionTransfer $shipmentMethodCollectionTransfer
    ): ShipmentMethodCollectionTransfer {
        foreach ($shipmentMethodEntities as $shipmentMethodEntity) {
            $shipmentMethodCollectionTransfer->addShipmentMethod(
                $this->mapShipmentMethodEntityToShipmentMethodTransfer(
                    $shipmentMethodEntity,
                    new ShipmentMethodTransfer(),
                ),
            );
        }

        return $shipmentMethodCollectionTransfer;
    }

    /**
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethod $salesShipmentMethodEntity
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\MoneyValueTransfer>
     */
    protected function getPriceCollection(SpyShipmentMethod $salesShipmentMethodEntity): ArrayObject
    {
        $moneyValueCollection = new ArrayObject();
        foreach ($salesShipmentMethodEntity->getShipmentMethodPrices() as $shipmentMethodPriceEntity) {
            $moneyValueTransfer = $this->mapShipmentMethodPriceEntityToMoneyValueTransfer(
                $shipmentMethodPriceEntity,
                new MoneyValueTransfer(),
            );

            $moneyValueCollection->append($moneyValueTransfer);
        }

        return $moneyValueCollection;
    }
}
