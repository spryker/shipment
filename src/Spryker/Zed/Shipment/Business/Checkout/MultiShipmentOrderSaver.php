<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Checkout;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Shared\Shipment\ShipmentConstants;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCustomerInterface;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface;
use Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface;

class MultiShipmentOrderSaver implements MultiShipmentOrderSaverInterface
{
    use DatabaseTransactionHandlerTrait;

    /**
     * @var \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface
     */
    protected $salesFacade;

    /**
     * @var \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCustomerInterface
     */
    protected $customerFacade;

    /**
     * @var \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    protected $shipmentService;

    /**
     * @param \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface $entityManager
     * @param \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface $salesFacade
     * @param \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCustomerInterface $customerFacade
     * @param \Spryker\Service\Shipment\ShipmentServiceInterface $shipmentService
     */
    public function __construct(
        ShipmentEntityManagerInterface $entityManager,
        ShipmentToSalesFacadeInterface $salesFacade,
        ShipmentToCustomerInterface $customerFacade,
        ShipmentServiceInterface $shipmentService
    ) {
        $this->entityManager = $entityManager;
        $this->salesFacade = $salesFacade;
        $this->customerFacade = $customerFacade;
        $this->shipmentService = $shipmentService;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer)
    {
        $this->assertShipmentRequirements($quoteTransfer->getItems());

        $this->handleDatabaseTransaction(function () use ($quoteTransfer, $saveOrderTransfer) {
            $this->saveOrderShipmentTransaction($quoteTransfer, $saveOrderTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    public function saveOrderShipmentByShipmentGroup(OrderTransfer $orderTransfer, ShipmentGroupTransfer $shipmentGroupTransfer, SaveOrderTransfer $saveOrderTransfer): ShipmentGroupTransfer
    {
        $this->assertShipmentRequirements($orderTransfer->getItems());

        $shipmentGroupTransfer = $this->handleDatabaseTransaction(function () use ($orderTransfer, $shipmentGroupTransfer, $saveOrderTransfer) {
            return $this
                ->saveOrderShipmentTransactionByShipmentGroup(
                    $orderTransfer,
                    $shipmentGroupTransfer,
                    $saveOrderTransfer
                );
        });

        return $shipmentGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function saveOrderShipmentTransaction(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $orderTransfer = $this->salesFacade->getOrderByIdSalesOrder($saveOrderTransfer->getIdSalesOrder());
        $shipmentGroups = $this->shipmentService->groupItemsByShipment($quoteTransfer->getItems());
        $orderTransfer = $this->addShipmentExpensesFromQuoteToOrder($quoteTransfer, $orderTransfer);

        foreach ($shipmentGroups as $shipmentGroupTransfer) {
            $shipmentGroupTransfer = $this
                ->saveOrderShipmentTransactionByShipmentGroup(
                    $orderTransfer,
                    $shipmentGroupTransfer,
                    $saveOrderTransfer
                );
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    protected function saveOrderShipmentTransactionByShipmentGroup(
        OrderTransfer $orderTransfer,
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): ShipmentGroupTransfer {

        $shipmentTransfer = $shipmentGroupTransfer->getShipment();

        $shipmentTransfer = $this->saveSalesOrderAddress($shipmentTransfer);
        $expenseTransfer = $this->addShipmentExpenseToOrder($shipmentTransfer, $orderTransfer, $saveOrderTransfer);

        $shipmentTransfer = $this->entityManager->createOrderShipment(
            $shipmentTransfer,
            $orderTransfer,
            $expenseTransfer
        );

        $saveOrderTransfer = $this->updateFkShipmentForOrderItems($saveOrderTransfer, $shipmentTransfer);

        return $shipmentGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentTransfer
     */
    protected function saveSalesOrderAddress(ShipmentTransfer $shipmentTransfer): ShipmentTransfer
    {
        $shippingAddressTransfer = $shipmentTransfer->getShippingAddress();
        $customerAddressTransfer = $this->customerFacade->findCustomerAddressByAddressData($shippingAddressTransfer);
        if ($customerAddressTransfer !== null) {
            $shippingAddressTransfer = $customerAddressTransfer;
        }

        $shippingAddressTransfer = $this->salesFacade->createOrderAddress($shippingAddressTransfer);

        $shipmentTransfer->setShippingAddress($shippingAddressTransfer);

        return $shipmentTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function addShipmentExpensesFromQuoteToOrder(QuoteTransfer $quoteTransfer, OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($quoteTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getType() === ShipmentConstants::SHIPMENT_EXPENSE_TYPE) {
                $orderTransfer->addExpense($expenseTransfer);
            }
        }

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer|null
     */
    protected function addShipmentExpenseToOrder(
        ShipmentTransfer $shipmentTransfer,
        OrderTransfer $orderTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): ?ExpenseTransfer {
        $expenseTransfer = $this->findShipmentExpense($orderTransfer, $shipmentTransfer);
        if ($expenseTransfer === null) {
            return null;
        }

        $expenseTransfer = $this->sanitizeExpenseSumPrices($expenseTransfer);
        $expenseTransfer->setFkSalesOrder($orderTransfer->getIdSalesOrder());

        $expenseTransfer = $this->createExpense($expenseTransfer);

        $orderTransfer->addExpense($expenseTransfer);
        $saveOrderTransfer->addOrderExpense($expenseTransfer);

        return $expenseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function createExpense(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        if ($expenseTransfer->getIdSalesExpense()) {
            return $expenseTransfer;
        }

        return $this->salesFacade->createSalesExpense($expenseTransfer);
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
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function updateFkShipmentForOrderItems(
        SaveOrderTransfer $saveOrderTransfer,
        ShipmentTransfer $shipmentTransfer
    ): ShipmentGroupTransfer {

        foreach ($saveOrderTransfer->getItems() as $itemTransfer) {
            $this->entityManager->updateOrderItemFkShipment($itemTransfer, $shipmentTransfer);
        }

        return $saveOrderTransfer;
    }

    /**
     * @param iterable|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return void
     */
    protected function assertShipmentRequirements(iterable $itemTransfers): void
    {
        foreach ($itemTransfers as $itemTransfer) {
            $itemTransfer->requireShipment();
            $itemTransfer->getShipment()->requireMethod();
            $itemTransfer->getShipment()->requireShippingAddress();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $salesOrderTransfer
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer|null
     */
    protected function findShipmentExpense(OrderTransfer $salesOrderTransfer, ShipmentTransfer $shipmentTransfer): ?ExpenseTransfer
    {
        foreach ($salesOrderTransfer->getExpenses() as $expenseTransfer) {
            $expenseShipmentTransfer = $expenseTransfer->getShipment();
            /**
             * @todo Fix shipment comparing.
             */
            if ($expenseShipmentTransfer === $shipmentTransfer) {
                return $expenseTransfer;
            }
        }

        return null;
    }
}