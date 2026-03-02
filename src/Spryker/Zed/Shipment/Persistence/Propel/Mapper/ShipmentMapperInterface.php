<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\ShipmentCarrierTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Orm\Zed\Sales\Persistence\SpySalesShipment;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethod;

interface ShipmentMapperInterface
{
    public function mapShipmentTransferToShipmentEntity(
        ShipmentTransfer $shipmentTransfer,
        SpySalesShipment $salesShipmentEntity
    ): SpySalesShipment;

    public function mapShipmentEntityToShipmentTransfer(
        SpySalesShipment $salesShipmentEntity,
        ShipmentTransfer $shipmentTransfer
    ): ShipmentTransfer;

    public function mapShipmentEntityToShipmentMethodTransfer(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        SpySalesShipment $salesShipment
    ): ShipmentMethodTransfer;

    public function mapShipmentEntityToShipmentCarrierTransfer(
        ShipmentCarrierTransfer $shipmentCarrierTransfer,
        SpySalesShipment $salesShipment
    ): ShipmentCarrierTransfer;

    public function mapShipmentEntityToShippingAddressTransfer(
        AddressTransfer $addressTransfer,
        SpySalesShipment $salesShipment
    ): AddressTransfer;

    public function mapShipmentEntityToShipmentTransferWithDetails(
        SpySalesShipment $salesShipmentEntity,
        ShipmentTransfer $shipmentTransfer
    ): ShipmentTransfer;

    public function mapShipmentMethodEntityToShipmentMethodTransfer(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        SpyShipmentMethod $salesShipmentMethod
    ): ShipmentMethodTransfer;

    public function mapShipmentTransferToShipmentMethodTransfer(
        ShipmentMethodTransfer $shipmentMethodTransfer,
        ShipmentTransfer $shipmentTransfer
    ): ShipmentMethodTransfer;

    public function mapCountryEntityToCountryTransfer(
        SpyCountry $countryEntity,
        CountryTransfer $countryTransfer
    ): CountryTransfer;

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Sales\Persistence\SpySalesShipment>|iterable $salesOrderShipments
     * @param array<\Generated\Shared\Transfer\ShipmentTransfer> $shipmentTransfers
     *
     * @return array<\Generated\Shared\Transfer\ShipmentTransfer>
     */
    public function mapShipmentEntitiesToShipmentTransfers(
        iterable $salesOrderShipments,
        array $shipmentTransfers
    ): array;
}
