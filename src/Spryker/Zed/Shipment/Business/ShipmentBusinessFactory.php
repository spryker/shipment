<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business;

use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaver;
use Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface;
use Spryker\Zed\Shipment\Business\Checkout\ShipmentOrderSaver as CheckoutShipmentOrderSaver;
use Spryker\Zed\Shipment\Business\Mapper\ShipmentMapper;
use Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface;
use Spryker\Zed\Shipment\Business\Model\Carrier;
use Spryker\Zed\Shipment\Business\Model\Method;
use Spryker\Zed\Shipment\Business\Model\MethodPrice;
use Spryker\Zed\Shipment\Business\Model\ShipmentCarrierReader;
use Spryker\Zed\Shipment\Business\Model\ShipmentOrderHydrate;
use Spryker\Zed\Shipment\Business\Model\ShipmentOrderSaver;
use Spryker\Zed\Shipment\Business\Model\ShipmentTaxRateCalculator;
use Spryker\Zed\Shipment\Business\Model\Transformer\ShipmentMethodTransformer;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizer;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentSaver;
use Spryker\Zed\Shipment\Business\Shipment\ShipmentSaverInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCreator;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCreatorInterface;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseFilter;
use Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseFilterInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentFetcher;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentFetcherInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentGroupCreator;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentGroupCreatorInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpander;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface;
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
            $this->getQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\MethodInterface
     */
    public function createMethod()
    {
        return new Method(
            $this->getQueryContainer(),
            $this->createMethodPrice(),
            $this->createShipmentMethodTransformer(),
            $this->getCurrencyFacade(),
            $this->getStoreFacade(),
            $this->getPlugins(),
            $this->getMethodFilterPlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Shipment\ShipmentSaverInterface
     */
    public function createShipmentSaver(): ShipmentSaverInterface
    {
        return new ShipmentSaver(
            $this->createCheckoutMultiShipmentOrderSaver(),
            $this->createShipmentMethodExpander(),
            $this->getShipmentService(),
            $this->createShipmentExpenseCreator()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseCreatorInterface
     */
    public function createShipmentExpenseCreator(): ShipmentExpenseCreatorInterface
    {
        return new ShipmentExpenseCreator(
            $this->createShipmentMapper(),
            $this->createExpenseSanitizer()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface
     */
    public function createExpenseSanitizer(): ExpenseSanitizerInterface
    {
        return new ExpenseSanitizer($this->getPriceFacade());
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface
     */
    public function createShipmentMapper(): ShipmentMapperInterface
    {
        return new ShipmentMapper();
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface
     */
    public function createCheckoutMultiShipmentOrderSaver(): MultiShipmentOrderSaverInterface
    {
        return new MultiShipmentOrderSaver(
            $this->getEntityManager(),
            $this->getSalesFacade(),
            $this->getShipmentService(),
            $this->createExpenseSanitizer(),
            $this->getShipmentExpenseExpanderPlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface
     */
    public function createShipmentMethodExpander(): ShipmentMethodExpanderInterface
    {
        return new ShipmentMethodExpander(
            $this->createShipmentFetcher(),
            $this->getStoreFacade()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\Transformer\ShipmentMethodTransformerInterface
     */
    public function createShipmentMethodTransformer()
    {
        return new ShipmentMethodTransformer(
            $this->getCurrencyFacade(),
            $this->getQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentExpense\ShipmentExpenseFilterInterface
     */
    public function createShipmentExpenseFilter(): ShipmentExpenseFilterInterface
    {
        return new ShipmentExpenseFilter();
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\MethodPriceInterface
     */
    protected function createMethodPrice()
    {
        return new MethodPrice(
            $this->getQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentFetcherInterface
     */
    public function createShipmentFetcher(): ShipmentFetcherInterface
    {
        return new ShipmentFetcher(
            $this->getQueryContainer(),
            $this->getCurrencyFacade(),
            $this->createShipmentMethodTransformer()
        );
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentGroupCreatorInterface
     */
    public function createShipmentGroupCreator(): ShipmentGroupCreatorInterface
    {
        return new ShipmentGroupCreator($this->getRepository(), $this->getShipmentService(), $this->getSalesFacade());
    }

    /**
     * @return array
     */
    protected function getPlugins()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS);
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\Plugin\ShipmentMethodFilterPluginInterface[]
     */
    protected function getMethodFilterPlugins()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SHIPMENT_METHOD_FILTER_PLUGINS);
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\ShipmentOrderSaverInterface
     */
    public function createShipmentOrderSaver()
    {
        return new ShipmentOrderSaver($this->getSalesQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Checkout\ShipmentOrderSaverInterface
     */
    public function createCheckoutShipmentOrderSaver()
    {
        return new CheckoutShipmentOrderSaver($this->getSalesQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\ShipmentTaxRateCalculator
     */
    public function createShipmentTaxCalculator()
    {
        return new ShipmentTaxRateCalculator($this->getQueryContainer(), $this->getTaxFacade());
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

    /**
     * @return \Spryker\Zed\Shipment\Business\Model\ShipmentOrderHydrateInterface
     */
    public function createShipmentOrderHydrate()
    {
        return new ShipmentOrderHydrate($this->getQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Sales\Persistence\SalesQueryContainerInterface
     */
    protected function getSalesQueryContainer()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::QUERY_CONTAINER_SALES);
    }

    /**
     * @return \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    public function getShipmentService(): ShipmentServiceInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::SERVICE_SHIPMENT);
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToPriceFacadeInterface
     */
    public function getPriceFacade(): ShipmentToPriceFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_PRICE);
    }

    /**
     * @return \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface
     */
    protected function getSalesFacade(): ShipmentToSalesFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::FACADE_SALES);
    }

    /**
     * @return \Spryker\Zed\ShipmentExtension\Dependency\Plugin\ShipmentExpenseExpanderPluginInterface[]
     */
    public function getShipmentExpenseExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS_SHIPMENT_EXPENSE_EXPANDER);
    }
}
