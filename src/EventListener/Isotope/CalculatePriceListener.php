<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Isotope;

class CalculatePriceListener
{
    protected BookingAttribute $bookingAttribute;

    public function __construct(BookingAttribute $bookingAttribute)
    {
        $this->bookingAttribute = $bookingAttribute;
    }

    public function __invoke($fltPrice, $objSource, $strField, $intTaxClass, $arrOptions): float
    {
        $product = $objSource->getRelated('pid');
        $item = Isotope::getCart()->getItemForProduct($product);

        if (!$this->bookingAttribute->itemHasBooking($item)) {
            return $fltPrice;
        }

        return $fltPrice * $this->bookingAttribute->itemBookingRange($item);
    }
}
