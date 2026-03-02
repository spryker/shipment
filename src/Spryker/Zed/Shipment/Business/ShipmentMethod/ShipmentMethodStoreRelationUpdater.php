<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\ShipmentMethod;

use Generated\Shared\Transfer\StoreRelationTransfer;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface;
use Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface;

class ShipmentMethodStoreRelationUpdater implements ShipmentMethodStoreRelationUpdaterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Shipment\Persistence\ShipmentEntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentEntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function update(StoreRelationTransfer $storeRelationTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($storeRelationTransfer) {
            $this->executeUpdateStoreRelationTransaction($storeRelationTransfer);
        });
    }

    protected function executeUpdateStoreRelationTransaction(StoreRelationTransfer $storeRelationTransfer): void
    {
        $storeRelationTransfer->requireIdEntity();

        $currentIdStores = $this->getIdStoresByIdShipmentMethod($storeRelationTransfer->getIdEntity());
        $requestedIdStores = $storeRelationTransfer->getIdStores();

        $saveIdStores = array_diff($requestedIdStores, $currentIdStores);
        $deleteIdStores = array_diff($currentIdStores, $requestedIdStores);

        $this->entityManager->addShipmentMethodStoreRelationsForStores($saveIdStores, $storeRelationTransfer->getIdEntity());
        $this->entityManager->removeShipmentMethodStoreRelationsForStores($deleteIdStores, $storeRelationTransfer->getIdEntity());
    }

    /**
     * @param int $idShipmentMethod
     *
     * @return array<int>
     */
    protected function getIdStoresByIdShipmentMethod(int $idShipmentMethod): array
    {
        $storeRelation = $this->shipmentRepository->getStoreRelationByIdShipmentMethod($idShipmentMethod);

        return $storeRelation->getIdStores();
    }
}
