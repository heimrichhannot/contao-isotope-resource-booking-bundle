<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Attribute;

use Contao\Model\Collection;
use HeimrichHannot\IsotopeResourceBookingBundle\Model\ProductBookingModel;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionItem;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BookingAttributes.
 */
class BookingAttribute
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function isActive(IsotopeProduct $product): bool
    {
        return (bool) $product->getType()->addResourceBooking;
    }

    public function isBlockingTimeActive(IsotopeProduct $product): bool
    {
        return ($this->isActive($product) && (bool)$product->getType()->allowBlockingTime);
    }

    /**
     * @param IsotopeProduct|int $product
     * @param array{
     *     evaluateBlockedTime: bool,
     *     minDate: int,
     *     maxDate: int,
     *     skipBookingIds: array<int>,
     * } $options
     * @return array
     */
    public function getBookedDatesForProduct($product, int $quantity = 1, array $options = []): array
    {
        $options = array_merge([
            'evaluateBlockedTime' => true,
            'minDate' => 0,
            'maxDate' => 0,
            'skipBookingIds' => null,
        ], $options);

        if (is_int($product)) {
            $product = Product::findByPk($product);
        }

        if (!$product instanceof IsotopeProduct) {
            return [];
        }

        $blockTimeframe = 0;
        if ($options['evaluateBlockedTime']) {
            $blockTimeframe = $this->getProductBlockedDays($product);
        }

        $columns = ['pid=?'];
        $values = [$product->id];

        if ($options['minDate'] > 0) {
            $searchStartDate = (new \DateTime())->setTimestamp($options['minDate']);
            $searchStartDate->modify('-'.($blockTimeframe + 1).' days');
            $columns[] = 'start>?';
            $values[] = $searchStartDate->getTimestamp();
        }

        if ($options['maxDate'] > 0) {
            $searchEndDate = (new \DateTime())->setTimestamp($options['maxDate']);
            $searchEndDate->modify('+'.$blockTimeframe.' days');
            $columns[] = 'stop<?';
            $values[] = $searchEndDate->getTimestamp();
        }

        if (!empty($options['skipBookingIds'])) {
            $columns[] = 'id NOT IN ('.implode(',', $options['skipBookingIds']).')';
        }

        $bookings = ProductBookingModel::findBy($columns, $values);

        $blockedDates = [];

        foreach ($bookings as $booking) {
            $dateStart = (new \DateTime())->setTimestamp($booking->start);
            $dateEnd = (new \DateTime())->setTimestamp($booking->stop);

            if ($dateStart > $dateEnd) {
                continue;
            }

            if ($blockTimeframe > 0) {
                $dateStart->modify('-'.$blockTimeframe.' days');
                $dateEnd->modify('+'.$blockTimeframe.' days');
            }

            $dateCurrent = clone $dateStart;
            while ($dateCurrent <= $dateEnd) {
                $blockedDates[$dateCurrent->format('Y-m-d')] = 1;
                $dateCurrent->modify('+1 day');
            }
        }

        return $blockedDates;
    }

    /**
     * @param IsotopeProduct|int $product
     * @param int|null $collectionItemId
     * @return array
     */
    public function getCartDatesForProduct($product, ?int $collectionItemId = null): array
    {
        if (is_int($product)) {
            $product = Product::findByPk($product);
        }

        if (!$product instanceof IsotopeProduct) {
            return [];
        }

        $columns = ['product_id=?', 'bookingStart!=?', 'bookingStop!=?'];
        $values = [$product->id, '', ''];

        if ($collectionItemId) {
            $columns[] = 'id!=?';
            $values[] = $collectionItemId;
        }

//        $collection = ProductCollection::findByPk($item->pid);
//        if ('order' === $collection->type) {
//            $columns[] = 'pid!=?';
//            $values[] = $collection->source_collection_id;
//        }

        /** @var ProductCollectionItem[]|Collection $collectionItems */
        $collectionItems = ProductCollectionItem::findBy($columns, $values);
        if (!$collectionItems) {
            return [];
        }

        $cartDates = [];

        foreach ($collectionItems as $collectionItem) {
            $cartDates = array_merge(
                $cartDates,
                $this->createDateRange(
                    $collectionItem->bookingStart,
                    $collectionItem->bookingStop,
                    $this->getProductBlockedDays($collectionItem->getProduct())
                )
            );
        }

        return $cartDates;
    }

    /**
     * Check if a product is bookable to given dates. Checks bookings and current cart items of all users.
     */
    public function isAvailable($product, int $start, int $stop, int $quantity = 1, ?int $collectionItemId = null, array $options = []): bool
    {
        $options = array_merge([
            'skipBookingIds' => null,
        ], $options);

        $dateStart = date('Y-m-d', $start);
        $dateStop = date('Y-m-d', $stop);

        $bookings = array_keys($this->getBookedDatesForProduct($product, $quantity, [
            'minDate' => $start,
            'maxDate' => $stop,
            'skipBookingIds' => $options['skipBookingIds'],
        ]));

        if (in_array($dateStart, $bookings) || in_array($dateStop, $bookings)) {
            return false;
        }

        $cartDatesForProduct = $this->getCartDatesForProduct($product, $collectionItemId);
        if (in_array($dateStart, $cartDatesForProduct) || in_array($dateStop, $cartDatesForProduct)) {
            return false;
        }

        return true;
    }

    /**
     * Validated cart for items with booking option.
     *
     * Will add an error to the item, if booking for selected dates is not possible. Will return true otherwise.
     */
    public function validateCart(ProductCollectionItem &$item, int $quantity): bool
    {
        $product = $item->getProduct();

        if (!$this->itemHasBooking($item)) {
            return true;
        }

        if (!$this->isAvailable($product, $item->bookingStart, $item->bookingStop, $quantity, $item->id)) {
            $item->addError($this->translator->trans('huh.isotope.collection.booking.error.overbooked', ['%product%' => $product->getName()]));
            return false;
        }

        return true;
    }

    private function createDateRange(int $start, int $stop, int $blockTime = 0): array
    {
        $blockedDates = [];

        $dateStart = (new \DateTime())->setTimestamp($start);
        $dateEnd = (new \DateTime())->setTimestamp($stop);

        if ($dateStart > $dateEnd) {
            return $blockedDates;
        }

        if ($blockTime > 0) {
            $dateStart->modify('-'.$blockTime.' days');
            $dateEnd->modify('+'.$blockTime.' days');
        }

        $dateCurrent = clone $dateStart;
        while ($dateCurrent <= $dateEnd) {
            $blockedDates[$dateCurrent->format('Y-m-d')] = 1;
            $dateCurrent->modify('+1 day');
        }

        return $blockedDates;
    }

    public function itemHasBooking(ProductCollectionItem $item): bool
    {
        if (!$item->bookingStart && !$item->bookingStop) {
            return false;
        }

        return true;
    }

    public function itemBookingRange(ProductCollectionItem $item): int
    {
        $dateStart = (new \DateTime())->setTimestamp($item->bookingStart);
        $dateEnd = (new \DateTime())->setTimestamp($item->bookingStop);
        $interval = $dateStart->diff($dateEnd);
        return $interval->days + 1;
    }

    private function getProductBlockedDays (IsotopeProduct $product): int
    {
        if ($this->isBlockingTimeActive($product)) {
            return $product->bookingBlock ?? 0;
        }

        return 0;
    }
}
