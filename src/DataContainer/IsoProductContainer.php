<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Model\Product;
use Twig\Environment;

class IsoProductContainer
{
    protected BookingAttribute $bookingAttribute;
    protected Environment $twig;

    public function __construct(BookingAttribute $bookingAttribute, Environment $twig)
    {
        $this->bookingAttribute = $bookingAttribute;
        $this->twig = $twig;
    }

    /**
     * Generates an overview of bookings for product.
     *
     * @return string An Html-Code
     */
    public function onBookingOverviewTextCallback(array $attributes)
    {
        $product = Product::findById($attributes['dataContainer']->id);
        $bookings = $this->bookingAttribute->getBookingCountsByMonth($product, date('n'), date('Y'));

        return $this->twig->render('@HeimrichHannotIsotopeResourceBooking/attribute/bookingoverview.html.twig', [
            'time' => time(),
            'bookings' => $bookings,
            'product' => $product,
        ]);
    }
}
