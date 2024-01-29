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
    private Environment $twig;

    public function __construct(BookingAttribute $bookingAttribute, ContaoFramework $framework, TranslatorInterface $translator, Environment $twig)
    {
        $this->bookingAttribute = $bookingAttribute;
        $this->framework = $framework;
        $this->translator = $translator;
        $this->twig = $twig;
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

        $year = is_numeric($request->get('year')) ? (int) $request->get('year') : date('Y');
        $month = is_numeric($request->get('month')) ? (int) $request->get('month') : date('n');

        return new Response($this->renderBookingOverview($product, $month, $year));
    }

    public function renderBookingOverview(Product $product, int $month, int $year): string
    {
        $date = mktime(0, 0, 0, $month, 1, $year);

        $bookings = $this->bookingAttribute->getBookingCountsByMonth($product, $month, $year);


        $lastMonth = strtotime('-1 month', $date);
        $urlLastMonth = $this->generateUrl('huh_isotope_resource_booking_bookingoverview', [
            'id' => $product->id,
            'month' => date('n', $lastMonth),
            'year' => date('Y', $lastMonth),
        ]);

        $nextMonth = strtotime('+1 month', $date);
        $urlNextMonth = $this->generateUrl('huh_isotope_resource_booking_bookingoverview', [
            'id' => $product->id,
            'month' => date('n', $nextMonth),
            'year' => date('Y', $nextMonth),
        ]);

        return $this->twig->render('@HeimrichHannotIsotopeResourceBooking/attribute/bookingoverview.html.twig', [
            'time' => $date,
            'bookings' => $bookings,
            'product' => $product,
            'month' => $month-1,
            'urlLastMonth' => $urlLastMonth,
            'urlNextMonth' => $urlNextMonth,
        ]);
    }
}
