<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Message;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;

/**
 * Isotope Hook("preCheckout").
 */
class PreCheckoutListener
{
    protected BookingAttribute $bookingAttribute;

    public function __construct(BookingAttribute $bookingAttribute)
    {
        $this->bookingAttribute = $bookingAttribute;
    }

    /**
     * @param Order|null $order
     * @param Checkout   $checkout
     */
    public function __invoke(?Order $order, Checkout $checkout): bool
    {
        foreach ($order->getItems() as $item) {
            if (false === $this->bookingAttribute->validateCart($item, $item->quantity)) {
                Message::addError($item->getErrors()[0]);

                return false;
            }
        }

        return true;
    }
}
