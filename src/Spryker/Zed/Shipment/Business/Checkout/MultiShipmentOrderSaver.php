<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Checkout;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderFilterTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Shared\Shipment\ShipmentConfig;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface;
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
     * @var \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    protected $shipmentService;

    /**
     * @var \Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface
     */
    protected $expenseSanitizer;

    /**
     * @var array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentExpenseExpanderPluginInterface>
     */
    protected $shipmentExpenseExpanderPlugins;

    /**
     * @param \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface $entityManager
     * @param \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface $salesFacade
     * @param \Spryker\Service\Shipment\ShipmentServiceInterface $shipmentService
     * @param \Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface $expenseSanitizer
     * @param array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentExpenseExpanderPluginInterface> $shipmentExpenseExpanderPlugins
     */
    public function __construct(
        ShipmentEntityManagerInterface $entityManager,
        ShipmentToSalesFacadeInterface $salesFacade,
        ShipmentServiceInterface $shipmentService,
        ExpenseSanitizerInterface $expenseSanitizer,
        array $shipmentExpenseExpanderPlugins
    ) {
        $this->entityManager = $entityManager;
        $this->salesFacade = $salesFacade;
        $this->shipmentService = $shipmentService;
        $this->expenseSanitizer = $expenseSanitizer;
        $this->shipmentExpenseExpanderPlugins = $shipmentExpenseExpanderPlugins;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaver::saveSalesOrderShipment()} instead.
     *
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
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveSalesOrderShipment(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $this->assertShipmentRequirements($quoteTransfer->getItems());

        $this->handleDatabaseTransaction(function () use ($quoteTransfer, $saveOrderTransfer) {
            $this->saveSalesOrderShipmentTransaction($quoteTransfer, $saveOrderTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    public function saveOrderShipmentByShipmentGroup(
        OrderTransfer $orderTransfer,
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): ShipmentGroupTransfer {
        $this->assertShipmentRequirements($orderTransfer->getItems());

        $shipmentGroupTransfer = $this->handleDatabaseTransaction(function () use (
            $orderTransfer,
            $shipmentGroupTransfer,
            $saveOrderTransfer
        ) {
            return $this->saveOrderShipmentTransactionByShipmentGroup(
                $orderTransfer,
                $shipmentGroupTransfer,
                $saveOrderTransfer,
            );
        });

        return $shipmentGroupTransfer;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaver::saveSalesOrderShipmentTransaction()} instead.
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function saveOrderShipmentTransaction(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $orderTransfer = $this->salesFacade->getOrderByIdSalesOrder($saveOrderTransfer->getIdSalesOrder());
        $shipmentGroups = $this->shipmentService->groupItemsByShipment($saveOrderTransfer->getOrderItems());
        $orderTransfer = $this->addShipmentExpensesFromQuoteToOrder($quoteTransfer, $orderTransfer);

        foreach ($shipmentGroups as $shipmentGroupTransfer) {
            $this->saveOrderShipmentTransactionByShipmentGroup(
                $orderTransfer,
                $shipmentGroupTransfer,
                $saveOrderTransfer,
            );
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    protected function saveSalesOrderShipmentTransaction(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer): void
    {
        $orderFilterTransfer = (new OrderFilterTransfer())->setSalesOrderId($saveOrderTransfer->getIdSalesOrder());
        $orderTransfer = $this->salesFacade->getOrder($orderFilterTransfer);
        $shipmentGroups = $this->shipmentService->groupItemsByShipment($saveOrderTransfer->getOrderItems());
        $orderTransfer = $this->addShipmentExpensesFromQuoteToOrder($quoteTransfer, $orderTransfer);

        foreach ($shipmentGroups as $shipmentGroupTransfer) {
            $shipmentGroupTransfer = $this->saveSalesShipment(
                $orderTransfer,
                $shipmentGroupTransfer,
                $saveOrderTransfer,
            );

            $this->addShipmentToQuoteItems($shipmentGroupTransfer, $quoteTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    protected function saveSalesShipment(
        OrderTransfer $orderTransfer,
        ShipmentGroupTransfer $shipmentGroupTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): ShipmentGroupTransfer {
        $shipmentTransfer = $shipmentGroupTransfer->requireShipment()->getShipment();

        $expenseTransfer = $this->findShipmentExpense($orderTransfer, $shipmentTransfer);
        if ($expenseTransfer !== null) {
            $expenseTransfer = $this->saveShipmentExpense($expenseTransfer, $saveOrderTransfer, $shipmentGroupTransfer);
            $orderTransfer->addExpense($expenseTransfer);
            $saveOrderTransfer->addOrderExpense($expenseTransfer);
        }

        $this->hydrateShippingAddressToShipmentTransfer($shipmentTransfer, $orderTransfer);

        $shipmentTransfer = $this->saveSalesOrderAddress($shipmentTransfer);
        $shipmentTransfer = $this->entityManager->saveSalesShipment($shipmentTransfer, $orderTransfer, $expenseTransfer);

        $shipmentGroupTransfer->setShipment($shipmentTransfer);

        return $shipmentGroupTransfer;
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only.
     *
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function hydrateShippingAddressToShipmentTransfer(ShipmentTransfer $shipmentTransfer, OrderTransfer $orderTransfer): void
    {
        if ($shipmentTransfer->getShippingAddress() === null) {
            $shipmentTransfer->setShippingAddress($orderTransfer->getShippingAddress());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function addShipmentToQuoteItems(ShipmentGroupTransfer $shipmentGroupTransfer, QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($this->shipmentService->getShipmentHashKey($itemTransfer->getShipment()) !== $shipmentGroupTransfer->getHash()) {
                continue;
            }

            if (!$shipmentGroupTransfer->getShipment()->getIdSalesShipment()) {
                continue;
            }

            $itemTransfer->setShipment($shipmentGroupTransfer->getShipment());
        }

        return $quoteTransfer;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaver::saveOrderShipmentByShipmentGroup()} instead.
     *
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
        $shipmentTransfer = $shipmentGroupTransfer->requireShipment()->getShipment();

        $expenseTransfer = $this->findShipmentExpense($orderTransfer, $shipmentTransfer);
        if ($expenseTransfer !== null) {
            $expenseTransfer = $this->saveShipmentExpense($expenseTransfer, $saveOrderTransfer, $shipmentGroupTransfer);
            $orderTransfer->addExpense($expenseTransfer);
            $saveOrderTransfer->addOrderExpense($expenseTransfer);
        }

        $this->hydrateShippingAddressToShipmentTransfer($shipmentTransfer, $orderTransfer);

        $shipmentTransfer = $this->saveSalesOrderAddress($shipmentTransfer);

        $shipmentTransfer = $this->entityManager->saveSalesShipment(
            $shipmentTransfer,
            $orderTransfer,
            $expenseTransfer,
        );

        $itemTransfers = $shipmentGroupTransfer->getItems();

        $this->entityManager->updateFkShipmentForOrderItems($itemTransfers, $shipmentTransfer);

        $shipmentGroupTransfer->setItems($itemTransfers);

        return $shipmentGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentTransfer
     */
    protected function saveSalesOrderAddress(ShipmentTransfer $shipmentTransfer): ShipmentTransfer
    {
        $shippingAddressTransfer = $shipmentTransfer->requireShippingAddress()->getShippingAddress();
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
    protected function addShipmentExpensesFromQuoteToOrder(
        QuoteTransfer $quoteTransfer,
        OrderTransfer $orderTransfer
    ): OrderTransfer {
        foreach ($quoteTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getType() === ShipmentConfig::SHIPMENT_EXPENSE_TYPE) {
                $orderTransfer->addExpense($expenseTransfer);
            }
        }

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function saveShipmentExpense(
        ExpenseTransfer $expenseTransfer,
        SaveOrderTransfer $saveOrderTransfer,
        ShipmentGroupTransfer $shipmentGroupTransfer
    ): ExpenseTransfer {
        $expenseTransfer = $this->expenseSanitizer->sanitizeExpenseSumValues($expenseTransfer);
        $expenseTransfer = $this->executeShipmentExpenseExpanderPlugins($expenseTransfer, $shipmentGroupTransfer);

        if ($expenseTransfer->getIdSalesExpense()) {
            return $this->salesFacade->updateSalesExpense($expenseTransfer);
        }

        $expenseTransfer->setFkSalesOrder($saveOrderTransfer->getIdSalesOrder());

        return $this->salesFacade->createSalesExpense($expenseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function executeShipmentExpenseExpanderPlugins(ExpenseTransfer $expenseTransfer, ShipmentGroupTransfer $shipmentGroupTransfer): ExpenseTransfer
    {
        foreach ($this->shipmentExpenseExpanderPlugins as $shipmentExpenseExpanderPlugin) {
            $expenseTransfer = $shipmentExpenseExpanderPlugin->expand($expenseTransfer, $shipmentGroupTransfer);
        }

        return $expenseTransfer;
    }

    /**
     * @param iterable<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
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
    protected function findShipmentExpense(
        OrderTransfer $salesOrderTransfer,
        ShipmentTransfer $shipmentTransfer
    ): ?ExpenseTransfer {
        $itemShipmentKey = $this->shipmentService->getShipmentHashKey($shipmentTransfer);
        foreach ($salesOrderTransfer->getExpenses() as $expenseTransfer) {
            $expenseShipmentTransfer = $expenseTransfer->getShipment();
            if ($expenseShipmentTransfer === null || $expenseTransfer->getType() !== ShipmentConfig::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            $expenseShipmentKey = $this->shipmentService->getShipmentHashKey($expenseShipmentTransfer);
            if ($expenseShipmentKey === $itemShipmentKey) {
                return $expenseTransfer;
            }
        }

        return null;
    }
}
