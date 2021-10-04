<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\EventListener\Isotope;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddProductToCollectionListener
{
    protected BookingAttribute $bookingAttribute;
    protected RequestStack $requestStack;
    protected TranslatorInterface $translator;

    public function __construct(BookingAttribute $bookingAttribute, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->bookingAttribute = $bookingAttribute;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function __invoke(IsotopeProduct $product, int $quantity, IsotopeProductCollection $collection, array $config): int
    {
        $bookingPlanData = $this->requestStack->getCurrentRequest()->get('edit_booking_plan');

        if (null === $bookingPlanData) {
            return $quantity;
        }

        $dates = explode(' bis ', $bookingPlanData);

        if (\count($dates) > 2 || \count($dates) < 1) {
            return 0;
        }
        $startDate = strtotime($dates[0]);
        $endDate = strtotime($dates[1] ?? $dates[0]);

        if (false === $startDate || false === $endDate) {
            return 0;
        }

        if (!$this->bookingAttribute->checkProductBookingDates($product, $startDate, $endDate)) {
            Message::addError($this->translator->trans('huh.isotope.collection.booking.error.overbooked', ['%product%' => $product->getName()]));

            return 0;
        }

        return $quantity;
    }
}
