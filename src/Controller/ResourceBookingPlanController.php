<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use Isotope\Model\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @Route("/huh_isotope_resource_booking", name="huh_isotope_resource_booking_")
 */
class ResourceBookingPlanController extends AbstractController
{
    protected BookingAttribute $bookingAttribute;
    protected ContaoFramework $framework;
    protected TranslatorInterface $translator;

    public function __construct(BookingAttribute $bookingAttribute, ContaoFramework $framework, TranslatorInterface $translator)
    {
        $this->bookingAttribute = $bookingAttribute;
        $this->framework = $framework;
        $this->translator = $translator;
    }

    /**
     * @Route("/blocked_dates", name="blocked_dates")
     */
    public function blockedDates(Request $request): Response
    {
        /** @var Product|null $product */
        $product = Product::findByPk($request->get('productId'));

        if (!$product) {
            return new Response('Product not found.', 404);
        }

        $blocked = $this->bookingAttribute->getBookedDatesForProduct($product, $request->get('quantity'));
        $reserved = $this->bookingAttribute->getCartDatesForProduct($product);

        return new JsonResponse(['data' => [
            'blocked' => $blocked,
            'reserved' => $reserved,
        ]]);
    }
}
