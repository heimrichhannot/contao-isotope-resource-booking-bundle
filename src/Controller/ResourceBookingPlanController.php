<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Controller;

use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResourceBookingPlanController extends AbstractController
{
    protected ModelUtil $modelUtil;
    protected BookingAttribute $bookingAttribute;

    public function __construct(ModelUtil $modelUtil, BookingAttribute $bookingAttribute)
    {
        $this->modelUtil = $modelUtil;
        $this->bookingAttribute = $bookingAttribute;
    }

    /**
     * @Route("/huh_isotope_resource_booking/blocked_dates", name="huh_isotope_resource_booking_blocked_dates")
     */
    public function blockedDates(Request $request): Response
    {
        $product = $this->modelUtil->findModelInstanceByPk('tl_iso_product', $request->get('productId'));

        if (!$product) {
            return new Response('Product not found.', 404);
        }
        $blocked = $this->bookingAttribute->getBlockedDates($product, $request->get('quantity'));

        return new JsonResponse(['data' => ['blocked' => $blocked]]);
    }
}
