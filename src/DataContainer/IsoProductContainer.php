<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class IsoProductContainer
{
    protected BookingAttribute $bookingAttribute;
    protected Environment      $twig;
    private RequestStack       $requestStack;

    public function __construct(BookingAttribute $bookingAttribute, Environment $twig, RequestStack $requestStack)
    {
        $this->bookingAttribute = $bookingAttribute;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * Generates an overview of bookings for product.
     *
     * @return string An Html-Code
     */
    public function onBookingOverviewTextCallback(array $attributes)
    {
        $product = Product::findById($attributes['dataContainer']->id);
        $request = $this->requestStack->getCurrentRequest();

        $year = is_numeric($request->get('year')) ? (int) $request->get('year') : date('Y');
        $month = is_numeric($request->get('month')) ? (int) $request->get('month') : date('n');

        if ($request->request->has('huh_isotope_resource_booking_'.$product->id)) {
            $value = $request->request->get('huh_isotope_resource_booking_'.$product->id);
            [$month, $year] = explode('_', $value);
        }

        $date = mktime(0, 0, 0, $month, 1, $year);

        $bookings = $this->bookingAttribute->getBookingCountsByMonth($product, $month, $year);

        return $this->twig->render('@HeimrichHannotIsotopeResourceBooking/attribute/bookingoverview.html.twig', [
            'time' => $date,
            'bookings' => $bookings,
            'product' => $product,
        ]);
    }
}
