<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Sanitizer;

use Generated\Shared\Transfer\ExpenseTransfer;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToPriceFacadeInterface;

class ExpenseSanitizer implements ExpenseSanitizerInterface
{
    /**
     * @var \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToPriceFacadeInterface
     */
    protected $priceFacade;

    public function __construct(ShipmentToPriceFacadeInterface $priceFacade)
    {
        $this->priceFacade = $priceFacade;
    }

    /**
     * @deprecated @deprecated For BC reasons the missing sum prices are mirrored from unit prices.
     *
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    public function sanitizeExpenseSumValues(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        $expenseTransfer->setSumGrossPrice($expenseTransfer->getSumGrossPrice() ?? $expenseTransfer->getUnitGrossPrice());
        $expenseTransfer->setSumNetPrice($expenseTransfer->getSumNetPrice() ?? $expenseTransfer->getUnitNetPrice());
        $expenseTransfer->setSumPrice($expenseTransfer->getSumPrice() ?? $expenseTransfer->getUnitPrice());
        $expenseTransfer->setSumTaxAmount($expenseTransfer->getSumTaxAmount() ?? $expenseTransfer->getUnitTaxAmount());
        $expenseTransfer->setSumDiscountAmountAggregation(
            $expenseTransfer->getSumDiscountAmountAggregation()
            ?? $expenseTransfer->getUnitDiscountAmountAggregation(),
        );
        $expenseTransfer->setSumPriceToPayAggregation(
            $expenseTransfer->getSumPriceToPayAggregation()
            ?? $expenseTransfer->getUnitPriceToPayAggregation(),
        );

        return $expenseTransfer;
    }

    public function sanitizeShipmentExpensePricesByPriceMode(ExpenseTransfer $shipmentExpenseTransfer, int $price, string $priceMode): ExpenseTransfer
    {
        if ($priceMode === $this->priceFacade->getNetPriceModeIdentifier()) {
            return $this->sanitizeShipmentExpensePricesForNetPriceMode($shipmentExpenseTransfer, $price);
        }

        return $this->sanitizeShipmentExpensePricesForGrossPriceMode($shipmentExpenseTransfer, $price);
    }

    protected function sanitizeShipmentExpensePricesForNetPriceMode(ExpenseTransfer $shipmentExpenseTransfer, int $price): ExpenseTransfer
    {
        return $shipmentExpenseTransfer->setUnitNetPrice($price)
            ->setUnitGrossPrice(0)
            ->setSumGrossPrice(0);
    }

    protected function sanitizeShipmentExpensePricesForGrossPriceMode(ExpenseTransfer $shipmentExpenseTransfer, int $price): ExpenseTransfer
    {
        return $shipmentExpenseTransfer->setUnitGrossPrice($price)
            ->setUnitNetPrice(0)
            ->setSumNetPrice(0);
    }
}
