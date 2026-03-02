<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence;

use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Orm\Zed\Sales\Persistence\SpySalesOrderQuery;
use Orm\Zed\Sales\Persistence\SpySalesShipmentQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentCarrierQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPriceQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodStoreQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\PaginationMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentCarrierMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentExpenseMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentExpenseMapperInterface;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentMethodMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentMethodMapperInterface;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentOrderMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentOrderMapperInterface;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentSalesOrderItemMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentSalesOrderItemMapperInterface;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentTaxSetMapper;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\ShipmentTaxSetMapperInterface;
use Spryker\Zed\Shipment\Persistence\Propel\Mapper\StoreRelationMapper;

/**
 * @method \Spryker\Zed\Shipment\ShipmentConfig getConfig()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface getRepository()
 */
class ShipmentPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Shipment\Persistence\SpyShipmentCarrierQuery
     */
    public function createShipmentCarrierQuery()
    {
        return SpyShipmentCarrierQuery::create();
    }

    /**
     * @return \Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery
     */
    public function createShipmentMethodQuery()
    {
        return SpyShipmentMethodQuery::create();
    }

    public function createShipmentMethodStoreQuery(): SpyShipmentMethodStoreQuery
    {
        return SpyShipmentMethodStoreQuery::create();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesShipmentQuery
     */
    public function createSalesShipmentQuery()
    {
        return SpySalesShipmentQuery::create();
    }

    public function createSalesOrderItemQuery(): SpySalesOrderItemQuery
    {
        return SpySalesOrderItemQuery::create();
    }

    /**
     * @return \Orm\Zed\Shipment\Persistence\SpyShipmentMethodPriceQuery
     */
    public function createShipmentMethodPriceQuery()
    {
        return SpyShipmentMethodPriceQuery::create();
    }

    public function createSalesOrderQuery(): SpySalesOrderQuery
    {
        return SpySalesOrderQuery::create();
    }

    public function createShipmentMapper(): ShipmentMapper
    {
        return new ShipmentMapper();
    }

    public function createShipmentMethodMapper(): ShipmentMethodMapperInterface
    {
        return new ShipmentMethodMapper($this->createStoreRelationMapper());
    }

    public function createStoreRelationMapper(): StoreRelationMapper
    {
        return new StoreRelationMapper();
    }

    public function createTaxSetMapper(): ShipmentTaxSetMapperInterface
    {
        return new ShipmentTaxSetMapper();
    }

    public function createShipmentExpenseMapper(): ShipmentExpenseMapperInterface
    {
        return new ShipmentExpenseMapper();
    }

    public function createShipmentOrderMapper(): ShipmentOrderMapperInterface
    {
        return new ShipmentOrderMapper();
    }

    public function createShipmentSalesOrderItemMapper(): ShipmentSalesOrderItemMapperInterface
    {
        return new ShipmentSalesOrderItemMapper();
    }

    public function createShipmentCarrierMapper(): ShipmentCarrierMapper
    {
        return new ShipmentCarrierMapper();
    }

    public function createPaginationMapper(): PaginationMapper
    {
        return new PaginationMapper();
    }
}
