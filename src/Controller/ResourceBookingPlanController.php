<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\IsotopeResourceBookingBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\IsotopeResourceBookingBundle\Attribute\BookingAttribute;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Isotope\Model\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/huh_isotope_resource_booking", name="huh_isotope_resource_booking_")
 */
class ResourceBookingPlanController extends AbstractController
{
    protected ModelUtil $modelUtil;
    protected BookingAttribute $bookingAttribute;
    protected ContaoFramework $framework;
    protected TranslatorInterface $translator;

    public function __construct(ModelUtil $modelUtil, BookingAttribute $bookingAttribute, ContaoFramework $framework, TranslatorInterface $translator)
    {
        $this->modelUtil = $modelUtil;
        $this->bookingAttribute = $bookingAttribute;
        $this->framework = $framework;
        $this->translator = $translator;
    }

    /**
     * @Route("/blocked_dates", name="blocked_dates")
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

    /**
     * @Route("/bookinglist", name="bookinglist", defaults={"_scope" = "backend", "_token_check" = true})
     */
    public function bookingList(Request $request): Response
    {
        if (!$this->framework->isInitialized()) {
            $this->framework->initialize();
        }
        $id = $request->get('id');

        if (!is_numeric($id) | !$product = Product::findById($id)) {
            return $this->render(
                '@HeimrichHannotIsotopeResourceBooking/backend/bookinglist.html.twig',
                ['error' => $this->translator->trans('Invalid id')]
            );
        }
        $day = is_numeric($request->get('day')) ? (int) $request->get('day') : date('d');
        $month = is_numeric($request->get('month')) ? (int) $request->get('month') : date('n');
        $year = is_numeric($request->get('year')) ? (int) $request->get('year') : date('Y');
        $orders = $this->bookingAttribute->getOrdersWithBookingsByDay($product, $day, $month, $year);
        $date = mktime(0, 0, 0, $month, $day, $year);

        return $this->render('@HeimrichHannotIsotopeResourceBooking/backend/bookinglist.html.twig', [
            'product' => $product,
            'orders' => $orders,
            'tstamp' => $date,
        ]);
    }

    /**
     * @Route("/bookingoverview", name="bookingoverview", defaults={"_scope" = "backend", "_token_check" = true})
     */
    public function bookingOverviewAction(Request $request): Response
    {
        $id = $request->get('id');

        if (!is_numeric($id) | !$product = Product::findById($id)) {
            throw new \Exception($this->translator->trans('Invalid id'));
        }
        $bookings = $this->bookingAttribute->getBookingCountsByMonth($product, date('n'), date('Y'));
        $year = is_numeric($request->get('year')) ? (int) $request->get('year') : date('Y');
        $month = is_numeric($request->get('month')) ? (int) $request->get('month') : date('n');
        $date = mktime(0, 0, 0, $month, 1, $year);

        return $this->render('@HeimrichHannotIsotopeResourceBooking/attribute/bookingoverview.html.twig', [
            'bookings' => $bookings,
            'product' => $product,
            'time' => $date,
        ]);
    }
}
