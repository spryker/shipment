<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Checkout;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Orm\Zed\Sales\Persistence\SpySalesExpense;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use Orm\Zed\Sales\Persistence\SpySalesShipment;
use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;
use Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCountryInterface;

class ShipmentOrderSaver implements ShipmentOrderSaverInterface
{
    use DatabaseTransactionHandlerTrait;

    /**
     * @var \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @var \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    protected $shipmentService;

    /**
     * @var \Spryker\Zed\Sales\Dependency\Facade\SalesToCountryInterface
     */
    protected $countryFacade;

    /**
     * @param \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface $queryContainer
     * @param \Spryker\Service\Shipment\ShipmentServiceInterface $shipmentService
     * @param \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCountryInterface $countryFacade
     */
    public function __construct(
        SalesQueryContainerInterface $queryContainer,
        ShipmentServiceInterface $shipmentService,
        ShipmentToCountryInterface $countryFacade
    ) {
        $this->queryContainer = $queryContainer;
        $this->shipmentService = $shipmentService;
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer)
    {
        $this->assertShipmentRequirements($quoteTransfer);

        $this->handleDatabaseTransaction(function () use ($quoteTransfer, $saveOrderTransfer) {
            $this->saveOrderShipmentTransaction($quoteTransfer, $saveOrderTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function saveOrderShipmentTransaction(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $salesOrderEntity = $this->getSalesOrderByIdSalesOrder($saveOrderTransfer->getIdSalesOrder());

        $quoteTransfer->setShipmentGroups(
            $this->shipmentService->groupItemsByShipment($quoteTransfer->getItems())
        );

        foreach ($quoteTransfer->getShipmentGroups() as $shipmentGroupTransfer) {
            $this->saveShipmentAddressTransfer($shipmentGroupTransfer);
            $this->addExpensesToOrder($shipmentGroupTransfer, $salesOrderEntity, $saveOrderTransfer);
            $idSalesShipment = $this->createSalesShipment($shipmentGroupTransfer, $salesOrderEntity);
            $this->updateItemsShipment($shipmentGroupTransfer, $salesOrderEntity, $idSalesShipment);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param int $idSalesShipment
     *
     * @return void
     */
    protected function updateItemsShipment(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SpySalesOrder $salesOrderEntity,
        int $idSalesShipment
    ): void {
        foreach ($shipmentGroupTransfer->getItems() as $itemTransfer) {
            foreach ($salesOrderEntity->getItems() as $itemEntity) {
                if ($itemTransfer->getIdOrderItem() !== $itemEntity->getIdSalesOrderItem()) {
                    continue;
                }

                $itemEntity->setFkSalesShipment($idSalesShipment);
                $itemEntity->save();
                break;
            }
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     *
     * @return void
     */
    protected function saveShipmentAddressTransfer(ShipmentGroupTransfer $shipmentGroupTransfer): void
    {
        $shippingAddressTransfer = $shipmentGroupTransfer->getShipment()->getShippingAddress();

        $this->saveSalesOrderAddress($shippingAddressTransfer);

        $shipmentGroupTransfer
            ->getShipment()
            ->getShippingAddress()
            ->setIdSalesOrderAddress($shippingAddressTransfer->getIdSalesOrderAddress());
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesExpense $salesOrderExpenseEntity
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return void
     */
    protected function hydrateOrderExpenseEntity(
        SpySalesExpense $salesOrderExpenseEntity,
        ExpenseTransfer $expenseTransfer
    ) {
        $sanitizedExpenseTransfer = $this->sanitizeExpenseSumPrices(clone $expenseTransfer);

        $salesOrderExpenseEntity->fromArray($expenseTransfer->toArray());
        $salesOrderExpenseEntity->setGrossPrice($sanitizedExpenseTransfer->getSumGrossPrice());
        $salesOrderExpenseEntity->setNetPrice($sanitizedExpenseTransfer->getSumNetPrice());
        $salesOrderExpenseEntity->setPrice($sanitizedExpenseTransfer->getSumPrice());
        $salesOrderExpenseEntity->setTaxAmount($sanitizedExpenseTransfer->getSumTaxAmount());
        $salesOrderExpenseEntity->setDiscountAmountAggregation($sanitizedExpenseTransfer->getSumDiscountAmountAggregation());
        $salesOrderExpenseEntity->setPriceToPayAggregation($sanitizedExpenseTransfer->getSumPriceToPayAggregation());
    }

    /**
     * @deprecated For BC reasons the missing sum prices are mirrored from unit prices
     *
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function sanitizeExpenseSumPrices(ExpenseTransfer $expenseTransfer)
    {
        $expenseTransfer->setSumGrossPrice($expenseTransfer->getSumGrossPrice() ?? $expenseTransfer->getUnitGrossPrice());
        $expenseTransfer->setSumNetPrice($expenseTransfer->getSumNetPrice() ?? $expenseTransfer->getUnitNetPrice());
        $expenseTransfer->setSumPrice($expenseTransfer->getSumPrice() ?? $expenseTransfer->getUnitPrice());
        $expenseTransfer->setSumTaxAmount($expenseTransfer->getSumTaxAmount() ?? $expenseTransfer->getUnitTaxAmount());
        $expenseTransfer->setSumDiscountAmountAggregation($expenseTransfer->getSumDiscountAmountAggregation() ?? $expenseTransfer->getUnitDiscountAmountAggregation());
        $expenseTransfer->setSumPriceToPayAggregation($expenseTransfer->getSumPriceToPayAggregation() ?? $expenseTransfer->getUnitPriceToPayAggregation());

        return $expenseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    protected function assertShipmentRequirements(QuoteTransfer $quoteTransfer): void
    {
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $itemTransfer->requireShipment();
            $itemTransfer->getShipment()->requireMethod();
            $itemTransfer->getShipment()->requireShippingAddress();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function addExpensesToOrder(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SpySalesOrder $salesOrderEntity,
        SaveOrderTransfer $saveOrderTransfer
    ) {
        $salesOrderExpenseEntity = $this->createSpySalesExpense($shipmentGroupTransfer->getShipment()->getExpense(), $salesOrderEntity->getIdSalesOrder());

        $shipmentGroupTransfer
            ->getShipment()
            ->getExpense()
            ->setIdSalesExpense($salesOrderExpenseEntity->getIdSalesExpense());

        $this->setCheckoutResponseExpenses($saveOrderTransfer, $shipmentGroupTransfer->getShipment()->getExpense(), $salesOrderExpenseEntity);

        $salesOrderEntity->addExpense($salesOrderExpenseEntity);
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesExpense
     */
    protected function createSpySalesExpense(ExpenseTransfer $expenseTransfer, int $idSalesOrder): SpySalesExpense
    {
        $salesOrderExpenseEntity = new SpySalesExpense();
        $this->hydrateOrderExpenseEntity($salesOrderExpenseEntity, $expenseTransfer);
        $salesOrderExpenseEntity->setFkSalesOrder($idSalesOrder);
        $salesOrderExpenseEntity->save();

        return $salesOrderExpenseEntity;
    }

    /**
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder|null
     */
    protected function getSalesOrderByIdSalesOrder($idSalesOrder)
    {
        return $this->queryContainer->querySalesOrderById($idSalesOrder)->findOne();
    }

    /**
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesExpense $salesOrderExpenseEntity
     *
     * @return void
     */
    protected function setCheckoutResponseExpenses(
        SaveOrderTransfer $saveOrderTransfer,
        ExpenseTransfer $expenseTransfer,
        SpySalesExpense $salesOrderExpenseEntity
    ) {
        $orderExpense = clone $expenseTransfer;
        $orderExpense->setIdSalesExpense($salesOrderExpenseEntity->getIdSalesExpense());
        $saveOrderTransfer->addOrderExpense($orderExpense);
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     *
     * @return int
     */
    protected function createSalesShipment(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SpySalesOrder $salesOrderEntity
    ): int {
        $salesShipmentEntity = $this->mapSalesShipmentEntity(
            $salesOrderEntity,
            $shipmentGroupTransfer->getShipment()
        );

        $salesShipmentEntity->save();

        return $salesShipmentEntity->getIdSalesShipment();
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrderEntity
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesShipment
     */
    protected function mapSalesShipmentEntity(
        SpySalesOrder $salesOrderEntity,
        ShipmentTransfer $shipmentTransfer
    ): SpySalesShipment {
        $salesShipmentEntity = new SpySalesShipment();
        $salesShipmentEntity->fromArray($shipmentTransfer->getMethod()->toArray());
        $salesShipmentEntity->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesShipmentEntity->setFkSalesExpense($shipmentTransfer->getExpense()->getIdSalesExpense());
        $salesShipmentEntity->setFkSalesOrderAddress($shipmentTransfer->getShippingAddress()->getIdSalesOrderAddress());

        return $salesShipmentEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return void
     */
    protected function saveSalesOrderAddress(AddressTransfer $addressTransfer): void
    {
        $salesOrderAddressEntity = $this->createSalesOrderAddressEntity();
        $this->hydrateSalesOrderAddress($addressTransfer, $salesOrderAddressEntity);
        $salesOrderAddressEntity->save();

        $addressTransfer->setIdSalesOrderAddress($salesOrderAddressEntity->getIdSalesOrderAddress());
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderAddress $salesOrderAddressEntity
     *
     * @return void
     */
    protected function hydrateSalesOrderAddress(
        AddressTransfer $addressTransfer,
        SpySalesOrderAddress $salesOrderAddressEntity
    ): void {
        $salesOrderAddressEntity->fromArray($addressTransfer->toArray());
        $salesOrderAddressEntity->setFkCountry(
            $this->countryFacade->getIdCountryByIso2Code($addressTransfer->getIso2Code())
        );
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderAddress
     */
    protected function createSalesOrderAddressEntity(): SpySalesOrderAddress
    {
        return new SpySalesOrderAddress();
    }

}
