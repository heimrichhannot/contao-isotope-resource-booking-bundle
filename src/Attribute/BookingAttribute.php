<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Attribute;

use Contao\Model\Collection;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class BookingAttributes.
 */
class BookingAttribute
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ModelUtil $modelUtil, TranslatorInterface $translator)
    {
        $this->modelUtil = $modelUtil;
        $this->translator = $translator;
    }

    public function checkProductBookingDates(IsotopeProduct $product, int $startDate, int $endDate, int $quantity = 1): bool
    {
        if (!$collectionItems = ProductCollectionItem::findBy(['product_id=?'], [$product->id])) {
            return true;
        }

        return $this->isBlockedForItems($collectionItems, $product, $quantity, $startDate, $endDate);
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

        if (!$collectionItems = ProductCollectionItem::findBy(['product_id=?', 'id!=?'], [$product->id, $item->id])) {
            return true;
        }

        if ($this->isBlockedForItems($collectionItems, $product, $quantity, $item->bookingStart, $item->bookingStop)) {
            return true;
        }

        $item->addError($this->translator->trans('huh.isotope.collection.booking.error.overbooked', ['%product%' => $product->getName()]));

        return false;
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
        return ceil(($item->bookingStop - $item->bookingStart) / 86400) + 1;
    }

    /**
     * @return array
     */
    public function getBlockedDates(IsotopeProduct $product, int $quantity = 1, array $options = [])
    {
        $collectionItems = $this->modelUtil->findModelInstancesBy(
            ProductCollectionItem::getTable(),
            [ProductCollectionItem::getTable().'.product_id=?'],
            [$product->getId()]
        );

        if (!$collectionItems) {
            return $this->getBlockedDatesByProduct($product, $quantity, $options);
        }

        return $this->getBlockedDatesByItems($collectionItems, $product, $quantity, $options);
    }

    /**
     * Returns a list of orders for given products for requested day.
     *
     * @return Collection|array|ProductCollection[]|null
     */
    public function getOrdersWithBookingsByDay(Product $product, int $day, int $month, int $year)
    {
        $orders = [];
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = mktime(23, 59, 59, $month, $day, $year);
        $items = $this->getBookedItemsInTimeRange($product, $start, $end, true);

        if (!$items) {
            return $orders;
        }

        foreach ($items as $item) {
            $orders[$item->pid]['items'][] = $item;
            $orders[$item->pid]['order'] = ProductCollection::findOneBy(['id =?', 'type=?'], [$item->pid, 'order']);
        }

        return $orders;
    }

    /**
     * Return a list with number of bookings per day.
     *
     * Includes reservations an blocked days.
     *
     * @return array
     */
    public function getBookingCountsByMonth(Product $product, int $month, int $year, array $options = [])
    {
        $defaults = [
            'double_blocked_value' => false,
        ];
        $options = array_merge($defaults, $options);

        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $monthDays = date('t', mktime(0, 0, 0, $month, 1, $year));
        $lastDay = mktime(23, 59, 59, $month, $monthDays, $year);

        $bookingList = [];
        $bookingList['booked'] = $bookingList['blocked'] = $bookingList['reserved'] = array_fill(1, $monthDays, 0);
        $items = $this->getBookedItemsInTimeRange($product, $firstDay, $lastDay);

        if (!$items) {
            return $bookingList;
        }

        foreach ($items as $item) {
            $range = $this->getRange($item->bookingStart, $item->bookingStop, $product->bookingBlock ?: 0, $options);
            $startDay = date('j', $item->bookingStart);
            $endDay = date('j', $item->bookingStop);

            foreach ($range as $tstamp) {
                if ($year == date('Y', $tstamp) && ($month == date('n', $tstamp))) {
                    $selectedDay = date('j', $tstamp);

                    if ($selectedDay < $startDay || $selectedDay > $endDay) {
                        ++$bookingList['blocked'][$selectedDay];

                        continue;
                    }
                    ++$bookingList['booked'][$selectedDay];
                }
            }
        }
        $reservedDates = $this->getReservedDates($product);

        foreach ($reservedDates as $reserved) {
            foreach ($reserved as $tstamp) {
                if ($year == date('Y', $tstamp) && ($month == date('n', $tstamp))) {
                    ++$bookingList['reserved'][date('j', $tstamp)];
                }
            }
        }

        return $bookingList;
    }

    /**
     * @param Collection     $collectionItems
     * @param IsotopeProduct $product
     * @param $quantity
     *
     * @return array
     */
    public function getBlockedDatesByItems($collectionItems, $product, int $quantity, array $options = [])
    {
        $stock = $product->stock - $quantity;

        $bookings = $this->getBookings($product, $collectionItems, $options);
        $reservedDates = $this->getReservedDates($product, $options);

        if (!empty($reservedDates)) {
            $bookings = $this->mergeBookedWithReserved($bookings, $reservedDates);
        }

        return $this->getLockedDates($bookings, $stock, $quantity);
    }

    public function getBlockedDatesByProduct($product, $quantity, array $options = [])
    {
        $stock = $product->stock - $quantity;

        $reservedDates = $this->getReservedDates($product, $options);

        if (empty($reservedDates)) {
            return [];
        }

        return $this->getLockedDates($reservedDates, $stock, $quantity);
    }

    /**
     * calculate the bookingRange of a product
     * if the product has a bookingBlock it as to be added to the bookingStop and subtracted from the bookingStart
     * bookingBlock means that the product will be blocked for a certain amount of days after it's booking.
     *
     * @return array
     */
    public function getRange(int $start, int $stop, int $blocking = 0, array $options = [])
    {
        $defaults = [
            'double_blocked_value' => false,
        ];
        $options = array_merge($defaults, $options);

        if (true === $options['double_blocked_value']) {
            $blocking = $blocking * 2;
        }

        $bookingStart = $blocking > 0 ? $start - ($blocking * 86400) : $start;
        $bookingStop = $blocking > 0 ? $stop + ($blocking * 86400) : $stop;

        return range($bookingStart, $bookingStop, 86400);
    }

    /**
     * Split up booking date string to two seperate timestamps.
     *
     * @return array
     */
    public function splitUpBookingDates(string $booking)
    {
        $bookingDates = explode('bis', $booking);

        return [strtotime(trim($bookingDates[0])), strtotime(trim($bookingDates[1]))];
    }

    /**
     * @return Collection|ProductCollectionItemModel[]|null
     */
    protected function getBookedItemsInTimeRange(ProductModel $product, int $startDate, int $endDate, bool $ignoreBlocking = false)
    {
        $searchRange = 0;

        if ($product->bookingBlock && !$ignoreBlocking) {
            //search block range * 2 to get also overlapping block dates and add 1 day to get the booking date
            $searchRange = (86400 * $product->bookingBlock * 2) + 1;
        }
        $firstDayWithBlocking = $startDate - $searchRange;
        $lastDayWithBlocking = $endDate + $searchRange;

        return ProductCollectionItemModel::findBy([
            'product_id = ?',
            "((bookingStart <= $lastDayWithBlocking AND bookingStop >= $startDate) ".
            "OR (bookingStart <= $endDate AND bookingStop >= $firstDayWithBlocking) ".
            "OR (bookingStart <= $startDate AND bookingStop >= $endDate))",
        ], [
            (int) $product->id,
        ]);
    }

    /**
     * get reserved dates from product.
     *
     * @param $product
     *
     * @return array
     */
    protected function getReservedDates($product, array $options = [])
    {
        if (!$product->bookingReservedDates) {
            return [];
        }

        if (empty($reserved = StringUtil::deserialize($product->bookingReservedDates, true))) {
            return [];
        }

        $reservedDates = [];

        foreach ($reserved as $pk) {
            if (null === ($blockedDates = $this->modelUtil->findModelInstanceByPk('tl_fieldpalette', $pk))) {
                continue;
            }

            $range = $this->getRange($blockedDates->start, $blockedDates->stop, $product->bookingBlock ?: 0, $options);

            $count = $blockedDates->useCount ? $blockedDates->count : $product->stock;

            for ($i = 0; $i < $count; ++$i) {
                $reservedDates[] = $range;
            }
        }

        return $reservedDates;
    }

    /**
     * get the booking dates for a product from collectionItems.
     *
     * @param $product
     *
     * @return array
     */
    protected function getBookings($product, Collection $collectionItems, array $options = [])
    {
        $defaults = [
            'double_blocked_value' => false,
        ];
        $options = array_merge($defaults, $options);

        $bookings = [];

        foreach ($collectionItems as $booking) {
            if (!$booking->bookingStart || !$booking->bookingStop) {
                continue;
            }

            $range =
                $this->getRange($booking->bookingStart, $booking->bookingStop, $product->bookingBlock ?: 0, $options);
            $bookings[$booking->id] = $range;
        }

        return $bookings;
    }

    /**
     * merge reserved dates into booking array.
     *
     * @return array
     */
    protected function mergeBookedWithReserved(array $bookings, array $reservedDates)
    {
        foreach ($reservedDates as $range) {
            $bookings[] = $range;
        }

        return $bookings;
    }

    /**
     * get the final locked days for this product.
     *
     * @return array
     */
    protected function getLockedDates(array $bookings, int $stock, int $quantity)
    {
        $counts = [];

        foreach ($bookings as $dates) {
            foreach ($dates as $date) {
                $count = 0;

                foreach ($bookings as $compareDates) {
                    foreach ($compareDates as $compareDate) {
                        if ($compareDate != $date) {
                            continue;
                        }

                        ++$count;

                        $counts[$date] = $count;
                    }
                }
            }
        }

        $locked = [];

        foreach ($counts as $date => $bookingCount) {
            if ($date < strtotime('today midnight') || ($stock + $quantity) - $bookingCount > $quantity) {
                continue;
            }

            $locked[] = $date;
        }

        return $locked;
    }

    /**
     * @param $collectionItems
     */
    private function isBlockedForItems($collectionItems, IsotopeProduct $product, int $quantity, int $startDate, int $endDate, array $options = []): bool
    {
        $defaults = [
            'double_blocked_value' => false,
        ];
        $options = array_merge($defaults, $options);

        $blockedDates = $this->getBlockedDatesByItems($collectionItems, $product, $quantity);
        $productDates = $this->getRange($startDate, $endDate, $product->bookingBlock, $options);

        if (\count(array_diff($blockedDates, $productDates)) == \count($blockedDates)) {
            return true;
        }

        return false;
    }
}
