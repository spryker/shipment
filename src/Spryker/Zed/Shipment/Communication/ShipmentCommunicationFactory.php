<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Shipment\Communication\Form\CarrierForm;
use Spryker\Zed\Shipment\Communication\Form\DataProvider\MethodFormDataProvider;
use Spryker\Zed\Shipment\Communication\Form\MethodForm;
use Spryker\Zed\Shipment\Communication\Table\MethodTable;
use Spryker\Zed\Shipment\ShipmentDependencyProvider;

/**
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentQueryContainer getQueryContainer()
 * @method \Spryker\Zed\Shipment\ShipmentConfig getConfig()
 */
class ShipmentCommunicationFactory extends AbstractCommunicationFactory
{

    /**
     * @return \Spryker\Zed\Shipment\Communication\Table\MethodTable
     */
    public function createMethodTable()
    {
        $methodQuery = $this->getQueryContainer()->queryMethods();

        return new MethodTable($methodQuery);
    }

    /**
     * @param array $formData
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCarrierForm(array $formData = [], array $options = [])
    {
        $formType = new CarrierForm();

        return $this->getFormFactory()->create($formType, $formData, $options);
    }

    /**
     * @param array $formData
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createMethodForm(array $formData = [], array $options = [])
    {
        $form = new MethodForm();

        return $this->getFormFactory()->create($form, $formData, $options);
    }

    /**
     * @return \Spryker\Zed\Shipment\Communication\Form\DataProvider\MethodFormDataProvider
     */
    public function createMethodFormDataProvider()
    {
        return new MethodFormDataProvider(
            $this->getQueryContainer(),
            $this->getTaxQueryContainer(),
            $this->getProvidedDependency(ShipmentDependencyProvider::PLUGINS)
        );
    }

    /**
     * @return \Spryker\Zed\Tax\Persistence\TaxQueryContainerInterface
     */
    protected function getTaxQueryContainer()
    {
        return $this->getProvidedDependency(ShipmentDependencyProvider::QUERY_CONTAINER_TAX);
    }

}
