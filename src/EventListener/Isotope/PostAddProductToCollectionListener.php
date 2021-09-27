<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Isotope Hook("postAddProductToCollection").
 */
class PostAddProductToCollectionListener
{
    protected RequestStack $requestStack;
    protected BookingAttribute $bookingAttribute;

    public function __construct(RequestStack $requestStack, BookingAttribute $bookingAttribute)
    {
        $this->requestStack = $requestStack;
        $this->bookingAttribute = $bookingAttribute;
    }

    public function __invoke(ProductCollectionItem &$item, int $quantity, ProductCollection $collection)
    {
        $changes = $this->requestStack->getCurrentRequest()->get('edit_booking_plan');

        if (!$changes) {
            return;
        }

        [$bookingStart, $bookingStop] = $this->bookingAttribute->splitUpBookingDates($changes);

        if ($bookingStart && $bookingStop) {
            $item->bookingStart = $bookingStart;
            $item->bookingStop = $bookingStop;
            $item->tstamp = time();
            $item->save();
        }
    }
}
