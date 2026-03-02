<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Shipment;

use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface;
use Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface;

/**
 * @deprecated Will be removed without replacement.
 */
class ShipmentReader implements ShipmentReaderInterface
{
    /**
     * @var \Spryker\Zed\Shipment\Dependency\Facade\ShipmentToSalesFacadeInterface
     */
    protected $salesFacade;

    /**
     * @var \Spryker\Zed\Shipment\Persistence\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    public function __construct(
        ShipmentToSalesFacadeInterface $salesFacade,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        $this->salesFacade = $salesFacade;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function findShipmentById(int $idSalesShipment): ?ShipmentTransfer
    {
        $shipmentTransfer = $this->shipmentRepository->findShipmentById($idSalesShipment);
        if ($shipmentTransfer === null) {
            return null;
        }

        $shipmentMethodTransfer = $shipmentTransfer->getMethod();
        if ($shipmentMethodTransfer === null) {
            return $shipmentTransfer;
        }

        $shipmentMethodTransfer = $this->getShipmentMethodTransferByName($shipmentMethodTransfer->getName());
        $shipmentTransfer->setMethod($shipmentMethodTransfer);

        return $shipmentTransfer;
    }

    protected function getShipmentMethodTransferByName(string $shipmentMethodName): ?ShipmentMethodTransfer
    {
        if ($shipmentMethodName === '') {
            return null;
        }

        return $this->shipmentRepository->findShipmentMethodByName($shipmentMethodName);
    }
}
