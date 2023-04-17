<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business;

use ArrayObject;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\MailTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SalesShipmentCollectionTransfer;
use Generated\Shared\Transfer\SalesShipmentCriteriaTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentCarrierRequestTransfer;
use Generated\Shared\Transfer\ShipmentCarrierTransfer;
use Generated\Shared\Transfer\ShipmentGroupResponseTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentMethodPluginCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethod;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Shipment\Business\ShipmentBusinessFactory getFactory()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface getRepository()
 */
class ShipmentFacade extends AbstractFacade implements ShipmentFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentCarrierTransfer $carrierTransfer
     *
     * @return int
     */
    public function createCarrier(ShipmentCarrierTransfer $carrierTransfer)
    {
        $carrierModel = $this->getFactory()->createCarrier();

        return $carrierModel->create($carrierTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Generated\Shared\Transfer\ShipmentCarrierTransfer>
     */
    public function getCarriers()
    {
        return $this->getFactory()
            ->createShipmentCarrierReader()
            ->getCarriers();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentCarrierRequestTransfer $shipmentCarrierRequestTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentCarrierTransfer|null
     */
    public function findShipmentCarrier(ShipmentCarrierRequestTransfer $shipmentCarrierRequestTransfer): ?ShipmentCarrierTransfer
    {
        return $this->getRepository()->findShipmentCarrier($shipmentCarrierRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Generated\Shared\Transfer\ShipmentMethodTransfer>
     */
    public function getMethods()
    {
        return $this->getRepository()->getActiveShipmentMethods();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $methodTransfer
     *
     * @return int|null
     */
    public function createMethod(ShipmentMethodTransfer $methodTransfer): ?int
    {
        return $this->getFactory()
            ->createShipmentMethodCreator()
            ->createShipmentMethod($methodTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idShipmentMethod
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findMethodById($idShipmentMethod)
    {
        return $this->getFactory()
            ->createShipmentMethodReader()
            ->findShipmentMethodById($idShipmentMethod);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer
     */
    public function getAvailableMethodsByShipment(QuoteTransfer $quoteTransfer): ShipmentMethodsCollectionTransfer
    {
        return $this
            ->getFactory()
            ->createMethodReader()
            ->getAvailableMethodsByShipment($quoteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idShipmentMethod
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findAvailableMethodById($idShipmentMethod, QuoteTransfer $quoteTransfer)
    {
        return $this->getFactory()
            ->createMethodReader()
            ->findAvailableMethodById($idShipmentMethod, $quoteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idMethod
     *
     * @return bool
     */
    public function hasMethod($idMethod)
    {
        return $this->getRepository()->hasShipmentMethodByIdShipmentMethod($idMethod);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idMethod
     *
     * @return bool
     */
    public function deleteMethod($idMethod)
    {
        return $this->getFactory()
            ->createShipmentMethodDeleter()
            ->deleteShipmentMethod($idMethod);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $methodTransfer
     *
     * @return int|bool
     */
    public function updateMethod(ShipmentMethodTransfer $methodTransfer)
    {
        return $this->getFactory()
            ->createShipmentMethodUpdater()
            ->updateShipmentMethod($methodTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function calculateShipmentTaxRate(QuoteTransfer $quoteTransfer)
    {
        $this->getFactory()
            ->createShipmentTaxCalculatorStrategyResolver()
            ->resolve($quoteTransfer->getItems())
            ->recalculate($quoteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Shipment\Business\ShipmentFacade::saveSalesOrderShipment()} instead.
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer)
    {
        $this->getFactory()
            ->createCheckoutShipmentOrderSaverStrategyResolver()
            ->resolve($quoteTransfer->getItems())
            ->saveOrderShipment($quoteTransfer, $saveOrderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveSalesOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->getFactory()
            ->createCheckoutMultiShipmentOrderSaver()
            ->saveSalesOrderShipment($quoteTransfer, $saveOrderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function hydrateOrderShipment(OrderTransfer $orderTransfer)
    {
        return $this->getFactory()->createMultipleShipmentOrderHydrate()->hydrateOrderWithShipment($orderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethod $shipmentMethodEntity
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer
     */
    public function transformShipmentMethodEntityToShipmentMethodTransfer(SpyShipmentMethod $shipmentMethodEntity)
    {
        return $this->getFactory()->createShipmentMethodTransformer()
            ->transformEntityToTransfer($shipmentMethodEntity);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idShipmentMethod
     *
     * @return bool
     */
    public function isShipmentMethodActive($idShipmentMethod)
    {
        return $this->getRepository()->hasActiveShipmentMethodByIdShipmentMethod($idShipmentMethod);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Shared\Shipment\ShipmentConfig::SHIPMENT_EXPENSE_TYPE} instead.
     *
     * @return string
     */
    public function getShipmentExpenseTypeIdentifier()
    {
        return $this->getFactory()->getConfig()->getShipmentExpenseType();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Shipment\Business\ShipmentFacade::getSalesShipmentCollection()} instead.
     *
     * @param int $idSalesShipment
     *
     * @return \Generated\Shared\Transfer\ShipmentTransfer|null
     */
    public function findShipmentById(int $idSalesShipment): ?ShipmentTransfer
    {
        return $this->getFactory()
            ->createShipmentReader()
            ->findShipmentById($idSalesShipment);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupResponseTransfer
     */
    public function saveShipment(ShipmentGroupTransfer $shipmentGroupTransfer, OrderTransfer $orderTransfer): ShipmentGroupResponseTransfer
    {
        return $this->getFactory()
            ->createShipmentSaver()
            ->saveShipment($shipmentGroupTransfer, $orderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param array<bool> $itemListUpdatedStatus
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    public function createShipmentGroupTransferWithListedItems(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        array $itemListUpdatedStatus
    ): ShipmentGroupTransfer {
        return $this->getFactory()
            ->createShipmentGroupCreator()
            ->createShipmentGroupTransferWithListedItems($shipmentGroupTransfer, $itemListUpdatedStatus);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function filterObsoleteShipmentExpenses(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $this->getFactory()
            ->createShipmentExpenseFilterStrategyResolver()
            ->resolve($calculableObjectTransfer->getItems())
            ->filterObsoleteShipmentExpenses($calculableObjectTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idSalesOrder
     * @param int $idSalesShipment
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer>
     */
    public function findSalesOrderItemsIdsBySalesShipmentId(int $idSalesOrder, int $idSalesShipment): ArrayObject
    {
        return $this->getFactory()
            ->createShipmentSalesOrderItemReader()
            ->findSalesOrderItemsBySalesShipmentId($idSalesOrder, $idSalesShipment);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuoteWithShipmentGroups(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createQuoteShipmentExpander()
            ->expandQuoteWithShipmentGroups($quoteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\MailTransfer
     */
    public function expandOrderMailTransfer(MailTransfer $mailTransfer, OrderTransfer $orderTransfer): MailTransfer
    {
        return $this->getFactory()
            ->createShipmentOrderMailExpander()
            ->expandOrderMailTransfer($mailTransfer, $orderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    public function expandOrderItemsWithShipment(array $itemTransfers): array
    {
        return $this->getFactory()
            ->createOrderItemShipmentExpander()
            ->expandOrderItemsWithShipment($itemTransfers);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $events
     * @param iterable<\Generated\Shared\Transfer\ItemTransfer> $orderItemTransfers
     *
     * @return array
     */
    public function groupEventsByShipment(array $events, iterable $orderItemTransfers): array
    {
        return $this->getFactory()
            ->createShipmentEventGrouper()
            ->groupEventsByShipment($events, $orderItemTransfers);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $shipmentMethodTransfer
     *
     * @return bool
     */
    public function isShipmentMethodUniqueForCarrier(ShipmentMethodTransfer $shipmentMethodTransfer): bool
    {
        return $this->getRepository()
            ->isShipmentMethodUniqueForCarrier($shipmentMethodTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $shipmentMethodName
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findShipmentMethodByName(string $shipmentMethodName): ?ShipmentMethodTransfer
    {
        return $this->getRepository()
            ->findShipmentMethodByName($shipmentMethodName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $shipmentMethodKey
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findShipmentMethodByKey(string $shipmentMethodKey): ?ShipmentMethodTransfer
    {
        return $this->getRepository()
            ->findShipmentMethodByKey($shipmentMethodKey);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodPluginCollectionTransfer
     */
    public function getShipmentMethodPlugins(): ShipmentMethodPluginCollectionTransfer
    {
        return $this->getFactory()->createShipmentMethodPluginReader()->getShipmentMethodPlugins();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Generated\Shared\Transfer\ShipmentCarrierTransfer>
     */
    public function getActiveShipmentCarriers(): array
    {
        return $this->getRepository()->getActiveShipmentCarriers();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function calculateShipmentTaxRateByCalculableObject(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        return $this->getFactory()
            ->createShipmentTaxCalculatorStrategyResolver()
            ->resolve($calculableObjectTransfer->getItems())
            ->recalculateByCalculableObject($calculableObjectTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function calculateShipmentTotal(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $this->getFactory()
            ->createShipmentTotalCalculator()
            ->calculateShipmentTotal($calculableObjectTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SalesShipmentCriteriaTransfer $salesShipmentCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\SalesShipmentCollectionTransfer
     */
    public function getSalesShipmentCollection(
        SalesShipmentCriteriaTransfer $salesShipmentCriteriaTransfer
    ): SalesShipmentCollectionTransfer {
        return $this->getRepository()->getSalesShipmentCollection($salesShipmentCriteriaTransfer);
    }
}
