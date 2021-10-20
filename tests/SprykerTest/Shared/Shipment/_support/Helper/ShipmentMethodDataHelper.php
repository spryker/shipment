<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Shipment\Helper;

use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\MoneyValueBuilder;
use Generated\Shared\DataBuilder\ShipmentMethodBuilder;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Shipment\Persistence\SpyShipmentCarrierQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPriceQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodStoreQuery;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\Shipment\Business\ShipmentFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;

class ShipmentMethodDataHelper extends Module
{
    use LocatorHelperTrait;

    /**
     * @var string
     */
    public const NAMESPACE_ROOT = '\\';

    /**
     * First level key represents store name.
     * Second level key represents currency ISO code.
     * Second level value represents the optional corresponding MoneyValue transfer object override values.
     *
     * @var array
     */
    public const DEFAULT_PRICE_LIST = [
        'DE' => [
            'EUR' => [],
        ],
    ];

    /**
     * @var array<int>|null Keys are store names, values are store ids.
     */
    protected static $idStoreCache = null;

    /**
     * @var array<int> Keys are currency ISO codes, values are currency ids.
     */
    protected static $idCurrencyCache = [];

    /**
     * @param array $overrideShipmentMethod
     * @param array $overrideCarrier
     * @param array $priceList
     * @param array $idStoreList
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer
     */
    public function haveShipmentMethod(
        array $overrideShipmentMethod = [],
        array $overrideCarrier = [],
        array $priceList = self::DEFAULT_PRICE_LIST,
        array $idStoreList = []
    ): ShipmentMethodTransfer {
        $shipmentMethodTransfer = (new ShipmentMethodBuilder($overrideShipmentMethod))->build();
        $shipmentMethodTransfer = $this->assertCarrier($shipmentMethodTransfer, $overrideCarrier);

        $moneyValueTransferCollection = new ArrayObject();
        foreach ($priceList as $storeName => $currencies) {
            foreach ($currencies as $currencyIsoCode => $moneyValueOverride) {
                $moneyValueTransferCollection->append(
                    (new MoneyValueBuilder($moneyValueOverride))
                        ->build()
                        ->setFkCurrency($this->getIdCurrencyByIsoCode($currencyIsoCode))
                        ->setFkStore($this->getIdStoreByName($storeName)),
                );
            }
        }
        $shipmentMethodTransfer->setPrices($moneyValueTransferCollection);
        $storeRelationTransfer = (new StoreRelationTransfer())->setIdStores($idStoreList);
        $shipmentMethodTransfer->setStoreRelation($storeRelationTransfer);

        $idShipmentMethod = $this->getShipmentFacade()->createMethod($shipmentMethodTransfer);
        $shipmentMethodTransfer->setIdShipmentMethod($idShipmentMethod);

        return $shipmentMethodTransfer;
    }

    /**
     * @return void
     */
    public function ensureShipmentMethodTableIsEmpty(): void
    {
        SpyShipmentMethodPriceQuery::create()->deleteAll();
        SpyShipmentMethodStoreQuery::create()->deleteAll();
        SpyShipmentMethodQuery::create()->deleteAll();
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $shipmentMethodTransfer
     * @param array $overrideCarrier
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer
     */
    protected function assertCarrier(ShipmentMethodTransfer $shipmentMethodTransfer, array $overrideCarrier): ShipmentMethodTransfer
    {
        if ($shipmentMethodTransfer->getFkShipmentCarrier()) {
            $shipmentCarrierEntity = SpyShipmentCarrierQuery::create()->findOneByIdShipmentCarrier($shipmentMethodTransfer->getFkShipmentCarrier());

            if ($shipmentCarrierEntity) {
                return $shipmentMethodTransfer->setCarrierName($shipmentCarrierEntity->getName());
            }
        }

        $shipmentCarrierTransfer = $this->getShipmentCarrierDataHelper()->haveShipmentCarrier($overrideCarrier);
        $shipmentMethodTransfer->setFkShipmentCarrier($shipmentCarrierTransfer->getIdShipmentCarrier());
        $shipmentMethodTransfer->setCarrierName($shipmentCarrierTransfer->getName());

        return $shipmentMethodTransfer;
    }

    /**
     * @return \SprykerTest\Shared\Shipment\Helper\ShipmentCarrierDataHelper|\Codeception\Module
     */
    protected function getShipmentCarrierDataHelper()
    {
        return $this->getModule(static::NAMESPACE_ROOT . ShipmentCarrierDataHelper::class);
    }

    /**
     * @param string $currencyIsoCode
     *
     * @return int
     */
    protected function getIdCurrencyByIsoCode(string $currencyIsoCode): int
    {
        if (!isset(static::$idCurrencyCache[$currencyIsoCode])) {
            static::$idCurrencyCache[$currencyIsoCode] = $this->getCurrencyFacade()
                ->fromIsoCode($currencyIsoCode)
                ->getIdCurrency();
        }

        return static::$idCurrencyCache[$currencyIsoCode];
    }

    /**
     * @param string $storeName
     *
     * @return int
     */
    protected function getIdStoreByName(string $storeName): int
    {
        if (static::$idStoreCache === null) {
            $this->loadStoreCache();
        }

        return static::$idStoreCache[$storeName];
    }

    /**
     * @return void
     */
    protected function loadStoreCache(): void
    {
        static::$idStoreCache = [];
        foreach ($this->getStoreFacade()->getAllStores() as $storeTransfer) {
            static::$idStoreCache[$storeTransfer->getName()] = $storeTransfer->getIdStore();
        }
    }

    /**
     * @return \Spryker\Zed\Currency\Business\CurrencyFacadeInterface
     */
    protected function getCurrencyFacade(): CurrencyFacadeInterface
    {
        return $this->getLocator()->currency()->facade();
    }

    /**
     * @return \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected function getStoreFacade(): StoreFacadeInterface
    {
        return $this->getLocator()->store()->facade();
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentFacadeInterface
     */
    protected function getShipmentFacade(): ShipmentFacadeInterface
    {
        return $this->getLocator()->shipment()->facade();
    }
}
