<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business;

use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Shipment\Business\Calculator\CalculatorInterface;
use Spryker\Zed\Shipment\Business\Calculator\ShipmentTaxRateCalculator as ShipmentTaxRateCalculatorWithItemShipmentTaxRate;
use Spryker\Zed\Shipment\Business\Calculator\ShipmentTotalCalculator;
use Spryker\Zed\Shipment\Business\Calculator\ShipmentTotalCalculatorInterface;
use Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaver;
use Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface;
use Spryker\Zed\Shipment\Business\Checkout\ShipmentOrderSaver as CheckoutShipmentOrderSaver;
use Spryker\Zed\Shipment\Business\Collector\ShipmentSalesOrderItemCollector;
use Spryker\Zed\Shipment\Business\Collector\ShipmentSalesOrderItemCollectorInterface;
use Spryker\Zed\Shipment\Business\Event\ShipmentEventGrouper;
use Spryker\Zed\Shipment\Business\Event\ShipmentEventGrouperInterface;
use Spryker\Zed\Shipment\Business\Expander\OrderItemShipmentExpander;
use Spryker\Zed\Shipment\Business\Expander\OrderItemShipmentExpanderInterface;
use Spryker\Zed\Shipment\Business\Expander\QuoteShipmentExpander;
use Spryker\Zed\Shipment\Business\Expander\QuoteShipmentExpanderInterface;
use Spryker\Zed\Shipment\Business\Expander\ShipmentMethodExpander;
use Spryker\Zed\Shipment\Business\Expander\ShipmentMethodExpanderInterface;
use Spryker\Zed\Shipment\Business\Grouper\ItemGrouper;
use Spryker\Zed\Shipment\Business\Grouper\ItemGrouperInterface;
use Spryker\Zed\Shipment\Business\Grouper\ShipmentGrouper;
use Spryker\Zed\Shipment\Business\Grouper\ShipmentGrouperInterface;
use Spryker\Zed\Shipment\Business\Mail\ShipmentOrderMailExpander;
use Spryker\Zed\Shipment\Business\Mail\ShipmentOrderMailExpanderInterface;
use Spryker\Zed\Shipment\Business\Mapper\ShipmentMapper;
use Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface;
use Spryker\Zed\Shipment\Business\Model\Carrier;
use Spryker\Zed\Shipment\Business\Model\MethodPrice;
use Spryker\Zed\Shipment\Business\Model\ShipmentCarrierReader;
use Spryker\Zed\Shipment\Business\Model\ShipmentTaxRateCalculator;
use Spryker\Zed\Shipment\Business\Model\Transformer\ShipmentMethodTransformer;
use Spryker\Zed\Shipment\Business\OrderItem\ShipmentSalesOrderItemReader;
use Spryker\Zed\Shipment\Business\OrderItem\ShipmentSalesOrderItemReaderInterface;
use Spryker\Zed\Shipment\Business\Replacer\SalesOrderShipmentReplacer;
use Spryker\Zed\Shipment\Business\Replacer\SalesOrderShipmentReplacerInterface;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizer;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentOrderHydrate as MultipleShipmentOrderHydrate;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentOrderHydrateInterface;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentReader;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentReaderInterface;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentSaver;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentSaverInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\MultiShipmentExpenseFilter;
use Spryker\Zed\Shipment\Business\ShipmentExpense\MultiShipmentExpenseFilterInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCollectionRemover;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCollectionRemoverInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCreator;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCreatorInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseFilter;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseFilterInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentFetcher;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentFetcherInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentGroupCreator;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentGroupCreatorInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpander as ShipmentGroupShipmentMethodExpander;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface as ShipmentGroupShipmentMethodExpanderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodAvailabilityChecker;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodAvailabilityCheckerInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodDeliveryTimeReader;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodDeliveryTimeReaderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodPriceReader;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodPriceReaderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodReader;
use Spryker\Zed\Shipment\Business\ShipmentMethod\MethodReaderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodCreator;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodCreatorInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodDeleter;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodDeleterInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodPluginReader;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodPluginReaderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodReader;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodReaderInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodStoreRelationUpdater;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodStoreRelationUpdaterInterface;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodUpdater;
use Spryker\Zed\Shipment\Business\ShipmentMethod\ShipmentMethodUpdaterInterface;
use Spryker\Zed\Shipment\Business\StrategyResolver\OrderSaverStrategyResolver;
use Spryker\Zed\Shipment\Business\StrategyResolver\OrderSaverStrategyResolverInterface;
use Spryker\Zed\Shipment\Business\StrategyResolver\ShipmentExpenseFilterStrategyResolver;
use Spryker\Zed\Shipment\Business\StrategyResolver\ShipmentExpenseFilterStrategyResolverInterface;
use Spryker\Zed\Shipment\Business\StrategyResolver\TaxRateCalculatorStrategyResolver;
use Spryker\Zed\Shipment\Business\StrategyResolver\TaxRateCalculatorStrategyResolverInterface;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCalculationFacadeInterface;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToPriceFacadeInterface;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface;
use Spryker\Zed\Shipment\ShipmentDependencyProvider;

/**
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Shipment\ShipmentConfig getConfig()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface getRepository()
 */
class ShipmentBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\Shipment\Business\Model\CarrierInterface
     */
    public function createCarrier()
    {
        return new Carrier();
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\ShipmentCarrierReaderInterface
     */
    public function createShipmentCarrierReader()
    {
        return new ShipmentCarrierReader(
            $this->getQueryContainer(),
        );
    }

    public function createShipmentMethodCreator(): ShipmentMethodCreatorInterface
    {
        return new ShipmentMethodCreator(
            $this->getEntityManager(),
            $this->createMethodPrice(),
            $this->createShipmentMethodStoreRelationUpdater(),
        );
    }

    public function createShipmentMethodStoreRelationUpdater(): ShipmentMethodStoreRelationUpdaterInterface
    {
        return new ShipmentMethodStoreRelationUpdater(
            $this->getRepository(),
            $this->getEntityManager(),
        );
    }

    public function createShipmentMethodUpdater(): ShipmentMethodUpdaterInterface
    {
        return new ShipmentMethodUpdater(
            $this->getRepository(),
            $this->getEntityManager(),
            $this->createMethodPrice(),
            $this->createShipmentMethodStoreRelationUpdater(),
        );
    }

    public function createShipmentMethodDeleter(): ShipmentMethodDeleterInterface
    {
        return new ShipmentMethodDeleter(
            $this->getRepository(),
            $this->getEntityManager(),
        );
    }

    public function createShipmentMethodReader(): ShipmentMethodReaderInterface
    {
        return new ShipmentMethodReader(
            $this->getRepository(),
            $this->getCurrencyFacade(),
            $this->createShipmentMethodExpander(),
        );
    }

    public function createMethodReader(): MethodReaderInterface
    {
        return new MethodReader(
            $this->getShipmentService(),
            $this->getMethodFilterPlugins(),
            $this->getRepository(),
            $this->createShipmentMethodAvailabilityChecker(),
            $this->createShipmentMethodPriceReader(),
            $this->createShipmentMethodDeliveryTimeReader(),
            $this->getStoreFacade(),
            $this->createShipmentMethodExpander(),
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\Transformer\ShipmentMethodTransformerInterface
     */
    public function createShipmentMethodTransformer()
    {
        return new ShipmentMethodTransformer(
            $this->getCurrencyFacade(),
            $this->getQueryContainer(),
        );
    }

    public function createShipmentExpenseFilter(): ShipmentExpenseFilterInterface
    {
        return new ShipmentExpenseFilter();
    }

    public function createMultiShipmentExpenseFilter(): MultiShipmentExpenseFilterInterface
    {
        return new MultiShipmentExpenseFilter(
            $this->getShipmentService(),
            $this->createShipmentExpenseCollectionRemover(),
        );
    }

    public function createShipmentExpenseCollectionRemover(): ShipmentExpenseCollectionRemoverInterface
    {
        return new ShipmentExpenseCollectionRemover($this->getShipmentService());
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\MethodPriceInterface
     */
    protected function createMethodPrice()
    {
        return new MethodPrice(
            $this->getQueryContainer(),
        );
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodFilterPluginInterface>
     */
    protected function getMethodFilterPlugins()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SHIPMENT_METHOD_FILTER_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentGroupsSanitizerPluginInterface>
     */
    protected function getShipmentGroupsSanitizerPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SHIPMENT_GROUPS_SANITIZER_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentExpenseExpanderPluginInterface>
     */
    public function getShipmentExpenseExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS_SHIPMENT_EXPENSE_EXPANDER);
    }

    /**
     * @deprecated Use {@link createCheckoutMultiShipmentOrderSaver()} instead.
     *
     * @return \Spryker\Zed\Shipment\Business\Checkout\ShipmentOrderSaverInterface
     */
    public function createCheckoutShipmentOrderSaver()
    {
        return new CheckoutShipmentOrderSaver(
            $this->getEntityManager(),
            $this->createExpenseSanitizer(),
            $this->getRepository(),
        );
    }

    public function createCheckoutMultiShipmentOrderSaver(): MultiShipmentOrderSaverInterface
    {
        return new MultiShipmentOrderSaver(
            $this->getEntityManager(),
            $this->getSalesFacade(),
            $this->getShipmentService(),
            $this->createExpenseSanitizer(),
            $this->getShipmentExpenseExpanderPlugins(),
        );
    }

    /**
     * @deprecated Use {@link createShipmentTaxCalculatorWithItemShipmentTaxRate()} instead.
     *
     * @return \Spryker\Zed\Shipment\Business\Model\ShipmentTaxRateCalculator
     */
    public function createShipmentTaxCalculator()
    {
        return new ShipmentTaxRateCalculator(
            $this->getQueryContainer(),
            $this->getTaxFacade(),
            $this->getShipmentService(),
            $this->getStoreFacade(),
        );
    }

    public function createShipmentTaxCalculatorWithItemShipmentTaxRate(): CalculatorInterface
    {
        return new ShipmentTaxRateCalculatorWithItemShipmentTaxRate(
            $this->getRepository(),
            $this->getTaxFacade(),
            $this->getShipmentService(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\ShipmentToTaxInterface
     */
    public function getTaxFacade()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_TAX);
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToCurrencyInterface
     */
    protected function getCurrencyFacade()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_CURRENCY);
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToStoreInterface
     */
    protected function getStoreFacade()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_STORE);
    }

    protected function getSalesFacade(): ShipmentToSalesFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_SALES);
    }

    public function createMultipleShipmentOrderHydrate(): ShipmentOrderHydrateInterface
    {
        return new MultipleShipmentOrderHydrate($this->getRepository(), $this->getSalesFacade());
    }

    public function getShipmentService(): ShipmentServiceInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SERVICE_SHIPMENT);
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use $this->createShipmentTaxCalculatorWithItemShipmentTaxRate() instead.
     *
     * @return \Spryker\Zed\Shipment\Business\StrategyResolver\TaxRateCalculatorStrategyResolverInterface
     */
    public function createShipmentTaxCalculatorStrategyResolver(): TaxRateCalculatorStrategyResolverInterface
    {
        $strategyContainer = [];

        $strategyContainer[TaxRateCalculatorStrategyResolver::STRATEGY_KEY_WITHOUT_MULTI_SHIPMENT] = function () {
            return $this->createShipmentTaxCalculator();
        };

        $strategyContainer[TaxRateCalculatorStrategyResolver::STRATEGY_KEY_WITH_MULTI_SHIPMENT] = function () {
            return $this->createShipmentTaxCalculatorWithItemShipmentTaxRate();
        };

        return new TaxRateCalculatorStrategyResolver($strategyContainer);
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use $this->createCheckoutMultiShipmentOrderSaver() instead.
     *
     * @return \Spryker\Zed\Shipment\Business\StrategyResolver\OrderSaverStrategyResolverInterface
     */
    public function createCheckoutShipmentOrderSaverStrategyResolver(): OrderSaverStrategyResolverInterface
    {
        $strategyContainer = [];

        $strategyContainer[OrderSaverStrategyResolver::STRATEGY_KEY_WITHOUT_MULTI_SHIPMENT] = function () {
            return $this->createCheckoutShipmentOrderSaver();
        };

        $strategyContainer[OrderSaverStrategyResolver::STRATEGY_KEY_WITH_MULTI_SHIPMENT] = function () {
            return $this->createCheckoutMultiShipmentOrderSaver();
        };

        return new OrderSaverStrategyResolver($strategyContainer);
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use $this->createMultiShipmentExpenseFilter() instead.
     *
     * @return \Spryker\Zed\Shipment\Business\StrategyResolver\ShipmentExpenseFilterStrategyResolverInterface
     */
    public function createShipmentExpenseFilterStrategyResolver(): ShipmentExpenseFilterStrategyResolverInterface
    {
        $strategyContainer = [];

        $strategyContainer[OrderSaverStrategyResolver::STRATEGY_KEY_WITHOUT_MULTI_SHIPMENT] = function () {
            return $this->createShipmentExpenseFilter();
        };

        $strategyContainer[OrderSaverStrategyResolver::STRATEGY_KEY_WITH_MULTI_SHIPMENT] = function () {
            return $this->createMultiShipmentExpenseFilter();
        };

        return new ShipmentExpenseFilterStrategyResolver($strategyContainer);
    }

    public function createShipmentFetcher(): ShipmentFetcherInterface
    {
        return new ShipmentFetcher(
            $this->getQueryContainer(),
            $this->getCurrencyFacade(),
            $this->createShipmentMethodTransformer(),
        );
    }

    public function createShipmentGroupShipmentMethodExpander(): ShipmentGroupShipmentMethodExpanderInterface
    {
        return new ShipmentGroupShipmentMethodExpander(
            $this->createShipmentFetcher(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\Shipment\Business\Shipment\ShipmentReaderInterface
     */
    public function createShipmentReader(): ShipmentReaderInterface
    {
        return new ShipmentReader(
            $this->getSalesFacade(),
            $this->getRepository(),
        );
    }

    public function createShipmentSaver(): ShipmentSaverInterface
    {
        return new ShipmentSaver(
            $this->createCheckoutMultiShipmentOrderSaver(),
            $this->createShipmentGroupShipmentMethodExpander(),
            $this->getShipmentService(),
            $this->createShipmentExpenseCreator(),
        );
    }

    public function createShipmentExpenseCreator(): ShipmentExpenseCreatorInterface
    {
        return new ShipmentExpenseCreator(
            $this->createShipmentMapper(),
            $this->createExpenseSanitizer(),
        );
    }

    public function createExpenseSanitizer(): ExpenseSanitizerInterface
    {
        return new ExpenseSanitizer($this->getPriceFacade());
    }

    public function createShipmentMethodAvailabilityChecker(): MethodAvailabilityCheckerInterface
    {
        /** @var array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodAvailabilityPluginInterface> $shipmentMethodAvailabilityPlugins */
        $shipmentMethodAvailabilityPlugins = $this->getAvailabilityPlugins();

        return new MethodAvailabilityChecker($shipmentMethodAvailabilityPlugins);
    }

    public function createShipmentMethodPriceReader(): MethodPriceReaderInterface
    {
        /** @var array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodPricePluginInterface> $shipmentMethodPricePlugins */
        $shipmentMethodPricePlugins = $this->getPricePlugins();

        return new MethodPriceReader(
            $shipmentMethodPricePlugins,
            $this->getStoreFacade(),
            $this->getRepository(),
            $this->getCurrencyFacade(),
        );
    }

    public function createShipmentGroupCreator(): ShipmentGroupCreatorInterface
    {
        return new ShipmentGroupCreator($this->getRepository(), $this->getShipmentService(), $this->getSalesFacade());
    }

    public function createShipmentMapper(): ShipmentMapperInterface
    {
        return new ShipmentMapper();
    }

    public function createShipmentMethodDeliveryTimeReader(): MethodDeliveryTimeReaderInterface
    {
        /** @var array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodDeliveryTimePluginInterface> $shipmentMethodDeliveryTimePlugins */
        $shipmentMethodDeliveryTimePlugins = $this->getDeliveryTimePlugins();

        return new MethodDeliveryTimeReader($shipmentMethodDeliveryTimePlugins);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodAvailabilityPluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodAvailabilityPluginInterface>
     */
    public function getAvailabilityPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::AVAILABILITY_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodPricePluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodPricePluginInterface>
     */
    public function getPricePlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::PRICE_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodDeliveryTimePluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodDeliveryTimePluginInterface>
     */
    public function getDeliveryTimePlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::DELIVERY_TIME_PLUGINS);
    }

    /**
     * @return array<string, array<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodAvailabilityPluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodAvailabilityPluginInterface|\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodPricePluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodPricePluginInterface|\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodDeliveryTimePluginInterface|\Spryker\Zed\Shipment\Communication\Plugin\ShipmentMethodDeliveryTimePluginInterface>>
     */
    public function getShipmentMethodPlugins(): array
    {
        return [
            ShipmentDependencyProvider::AVAILABILITY_PLUGINS => $this->getAvailabilityPlugins(),
            ShipmentDependencyProvider::PRICE_PLUGINS => $this->getPricePlugins(),
            ShipmentDependencyProvider::DELIVERY_TIME_PLUGINS => $this->getDeliveryTimePlugins(),
        ];
    }

    public function createShipmentMethodPluginReader(): ShipmentMethodPluginReaderInterface
    {
        return new ShipmentMethodPluginReader($this->getShipmentMethodPlugins());
    }

    public function getPriceFacade(): ShipmentToPriceFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_PRICE);
    }

    public function getCalculationFacade(): ShipmentToCalculationFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_CALCULATION);
    }

    public function createQuoteShipmentExpander(): QuoteShipmentExpanderInterface
    {
        return new QuoteShipmentExpander(
            $this->getShipmentService(),
            $this->createMethodReader(),
            $this->createExpenseSanitizer(),
            $this->createShipmentMapper(),
            $this->getCalculationFacade(),
            $this->getConfig(),
            $this->getShipmentGroupsSanitizerPlugins(),
        );
    }

    public function createShipmentSalesOrderItemReader(): ShipmentSalesOrderItemReaderInterface
    {
        return new ShipmentSalesOrderItemReader($this->getRepository());
    }

    public function createShipmentOrderMailExpander(): ShipmentOrderMailExpanderInterface
    {
        return new ShipmentOrderMailExpander($this->getShipmentService());
    }

    public function createOrderItemShipmentExpander(): OrderItemShipmentExpanderInterface
    {
        return new OrderItemShipmentExpander(
            $this->createItemGrouper(),
            $this->createShipmentGrouper(),
            $this->getRepository(),
        );
    }

    public function createShipmentGrouper(): ShipmentGrouperInterface
    {
        return new ShipmentGrouper();
    }

    public function createItemGrouper(): ItemGrouperInterface
    {
        return new ItemGrouper();
    }

    public function createShipmentEventGrouper(): ShipmentEventGrouperInterface
    {
        return new ShipmentEventGrouper($this->getShipmentService());
    }

    public function createShipmentTotalCalculator(): ShipmentTotalCalculatorInterface
    {
        return new ShipmentTotalCalculator();
    }

    public function createShipmentMethodExpander(): ShipmentMethodExpanderInterface
    {
        return new ShipmentMethodExpander($this->getShipmentMethodCollectionExpanderPlugins());
    }

    public function createSalesOrderShipmentReplacer(): SalesOrderShipmentReplacerInterface
    {
        return new SalesOrderShipmentReplacer(
            $this->getEntityManager(),
            $this->getRepository(),
            $this->getSalesFacade(),
            $this->createCheckoutMultiShipmentOrderSaver(),
        );
    }

    public function createShipmentSalesOrderItemCollector(): ShipmentSalesOrderItemCollectorInterface
    {
        return new ShipmentSalesOrderItemCollector();
    }

    /**
     * @return list<\Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentMethodCollectionExpanderPluginInterface>
     */
    public function getShipmentMethodCollectionExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS_SHIPMENT_METHOD_COLLECTION_EXPANDER);
    }
}
