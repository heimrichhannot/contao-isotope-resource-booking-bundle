<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\DataContainer;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use HeimrichHannot\IsotopeResourceBookingBundle\Controller\ResourceBookingPlanController;
use Isotope\Model\Product;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class IsoProductContainer
{
    private RequestStack       $requestStack;
    private ResourceBookingPlanController $resourceBookingPlanController;

    public function __construct(RequestStack $requestStack, ResourceBookingPlanController $resourceBookingPlanController)
    {
        $this->requestStack = $requestStack;
        $this->resourceBookingPlanController = $resourceBookingPlanController;
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

        return $this->resourceBookingPlanController->renderBookingOverview($product, $month, $year);
    }
}
